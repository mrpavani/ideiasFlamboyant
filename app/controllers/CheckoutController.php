<?php

declare(strict_types=1);

namespace Controllers;

use Core\Controller;
use Core\Csrf;
use Core\Flash;
use Models\Cart;
use Models\Order;
use Services\PaymentManager;

final class CheckoutController extends Controller
{
    public function index(): void
    {
        if (Cart::isEmpty()) {
            Flash::error('Seu carrinho está vazio.');
            redirect('produtos');
        }

        $this->view('shop/checkout', [
            'title'    => 'Finalizar compra — ' . setting('store_name', 'Idéias Flamboyant'),
            'totals'   => Cart::totals(),
            'gateways' => PaymentManager::enabled(),
            'whatsappFallback' => setting('payment_whatsapp_fallback', '1') === '1',
        ]);
    }

    public function process(): void
    {
        Csrf::check();

        if (Cart::isEmpty()) {
            Flash::error('Seu carrinho está vazio.');
            redirect('produtos');
        }

        $data = [
            'name'       => $this->input('name', ''),
            'email'      => $this->input('email', ''),
            'phone'      => $this->input('phone', ''),
            'document'   => $this->input('document', ''),
            'zip'        => $this->input('zip', ''),
            'street'     => $this->input('street', ''),
            'number'     => $this->input('number', ''),
            'complement' => $this->input('complement', ''),
            'district'   => $this->input('district', ''),
            'city'       => $this->input('city', ''),
            'state'      => strtoupper((string) $this->input('state', '')),
            'notes'      => $this->input('notes', ''),
            'payment_method' => $this->input('payment_method', 'whatsapp'),
        ];

        [$valid, $errors] = $this->validate($data, [
            'name'   => 'required|min:3|max:160',
            'phone'  => 'required|min:8|max:40',
            'email'  => 'email',
            'street' => 'required|max:200',
            'number' => 'required|max:20',
            'city'   => 'required|max:120',
            'state'  => 'required|min:2|max:2',
        ]);

        if (!$valid) {
            Flash::error(implode(' ', $errors));
            Flash::withInput($_POST);
            redirect('checkout');
        }

        $totals = Cart::totals();
        $order = Order::createFromCart($totals['items'], $data, $totals);

        activity('pedido.criado', 'order', (int) $order['id'], ['total' => $order['total']]);

        $method = $data['payment_method'];

        // Pagamento online
        if ($method !== 'whatsapp') {
            $gateway = PaymentManager::get($method);
            if ($gateway && $gateway->isEnabled()) {
                try {
                    $checkout = $gateway->createCheckout($order);
                    \Core\Database::update('orders', [
                        'payment_method'     => $method,
                        'payment_gateway_id' => $checkout['gateway_id'],
                        'payment_link'       => $checkout['checkout_url'],
                    ], 'id = :id', ['id' => $order['id']]);

                    Order::recordEvent((int) $order['id'], $method, 'checkout.created', 'pendente', $checkout['raw']);
                    Cart::clear();
                    redirect($checkout['checkout_url']);
                } catch (\Throwable $e) {
                    Order::recordEvent((int) $order['id'], $method, 'checkout.error', 'erro', ['message' => $e->getMessage()]);
                    Flash::error('Não foi possível iniciar o pagamento online. Finalize pelo WhatsApp.');
                }
            }
        }

        // Fallback: WhatsApp
        \Core\Database::update('orders', ['payment_method' => 'whatsapp'], 'id = :id', ['id' => $order['id']]);
        Cart::clear();
        redirect('pedido/' . $order['reference']);
    }

    public function confirmation(string $reference): void
    {
        $order = Order::findByReference($reference);
        if (!$order) {
            http_response_code(404);
            $this->view('errors/404', ['title' => 'Pedido não encontrado']);
            return;
        }
        $order['items'] = Order::items((int) $order['id']);

        $lines = ["*Pedido {$order['reference']}* — " . setting('store_name', 'Idéias Flamboyant')];
        foreach ($order['items'] as $item) {
            $lines[] = "• {$item['quantity']}x {$item['product_name']} — " . money($item['line_total']);
        }
        $lines[] = 'Total: ' . money($order['total']);
        $lines[] = "Cliente: {$order['customer_name']}";
        $whatsMessage = implode("\n", $lines);

        $this->view('shop/confirmation', [
            'title'        => "Pedido {$order['reference']} — " . setting('store_name', 'Idéias Flamboyant'),
            'order'        => $order,
            'whatsappUrl'  => whatsapp_link($whatsMessage),
            'paymentState' => $this->input('pg', ''),
        ]);
    }
}
