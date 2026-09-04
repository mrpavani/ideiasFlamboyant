<?php

declare(strict_types=1);

namespace Controllers\Admin;

use Core\Csrf;
use Core\Flash;
use Models\ApiToken;
use Models\Setting;

final class SettingController extends AdminController
{
    /** Chaves sensíveis: em branco no formulário = manter o valor atual. */
    private const SECRET_KEYS = ['mp_access_token', 'mp_public_key', 'infinitepay_token'];

    /** Todas as chaves editáveis pelo formulário. */
    private const KEYS = [
        // geral
        'store_name', 'store_tagline', 'store_email', 'store_city',
        // contato
        'whatsapp_number', 'whatsapp_message', 'instagram_url',
        // entrega
        'shipping_flat_rate', 'shipping_free_above', 'shipping_note',
        // pagamento
        'mp_enabled', 'mp_sandbox', 'mp_access_token', 'mp_public_key',
        'infinitepay_enabled', 'infinitepay_handle', 'infinitepay_token',
        'payment_whatsapp_fallback',
    ];

    public function index(): void
    {
        $this->adminView('settings/index', [
            'title'     => 'Configurações — Painel',
            'settings'  => Setting::map(),
            'apiTokens' => ApiToken::all(),
            'newToken'  => $_SESSION['_new_api_token'] ?? null,
        ]);
        unset($_SESSION['_new_api_token']);
    }

    public function update(): void
    {
        Csrf::check();

        $values = [];
        foreach (self::KEYS as $key) {
            if (in_array($key, ['mp_enabled', 'mp_sandbox', 'infinitepay_enabled', 'payment_whatsapp_fallback'], true)) {
                $values[$key] = !empty($_POST[$key]) ? '1' : '0';
            } else {
                $values[$key] = trim((string) ($_POST[$key] ?? ''));
            }
        }

        // Normaliza WhatsApp para apenas dígitos
        $values['whatsapp_number'] = preg_replace('/\D+/', '', $values['whatsapp_number']);
        // Normaliza valores monetários
        foreach (['shipping_flat_rate', 'shipping_free_above'] as $k) {
            $values[$k] = number_format((float) str_replace(',', '.', $values[$k]), 2, '.', '');
        }

        Setting::setMany($values, self::SECRET_KEYS);
        activity('config.atualizada', 'settings');
        Flash::success('Configurações salvas.');
        redirect('admin/configuracoes#' . ($this->input('tab', '') ?: 'geral'));
    }

    public function createApiToken(): void
    {
        Csrf::check();
        $name = (string) $this->input('name', 'Integração');
        $scopes = $this->boolInput('can_write') ? 'read,write' : 'read';
        $token = ApiToken::generate($name ?: 'Integração', $scopes);
        $_SESSION['_new_api_token'] = $token['token'];
        activity('api_token.criado', 'api_token', $token['id']);
        Flash::success('Token gerado. Copie agora — ele não será exibido novamente.');
        redirect('admin/configuracoes#api');
    }

    public function revokeApiToken(string $id): void
    {
        Csrf::check();
        ApiToken::revoke((int) $id);
        Flash::success('Token revogado.');
        redirect('admin/configuracoes#api');
    }
}
