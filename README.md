# Idéias Flamboyant — E-commerce

Loja virtual da **Idéias Flamboyant**, empresa especializada em fabricação e venda de
produtos impressos em impressora 3D.

Stack: **PHP 8.1+ / MySQL 8 / JavaScript (vanilla)** — sem framework, sem dependências externas.

---

## Recursos

- Vitrine moderna com identidade visual do logo (paleta flamboyant).
- Catálogo com categorias, busca, página de produto e galeria de imagens.
- Carrinho de compras (sessão) e checkout.
- **Contato e acompanhamento de pedido via WhatsApp** (número configurável no admin).
- **Pagamentos**: Mercado Pago e InfinitePay — chaves e ativação configuráveis pelo painel admin.
- **Controle de estoque** com histórico de movimentações (entrada/saída/ajuste) e baixa automática na aprovação do pagamento.
- **Painel administrativo**: produtos (CRUD + upload de imagens), categorias, pedidos, estoque, configurações e usuários.
- **API REST v1** (`/api/v1/...`) com autenticação por token — preparada para integração futura com sistema de vendas/ERP.

---

## Estrutura

```
ecomerce-ideia/
├── index.php               # front controller — raiz web (aponte o virtualhost / DocumentRoot aqui)
├── .htaccess
├── assets/                 # css, js, imagens estáticas
├── uploads/                # imagens de produtos (gravável)
├── app/
│   ├── config/             # configuração (lê .env)
│   ├── core/               # Database, Router, Controller, Auth, helpers...
│   ├── models/             # camada de dados (PDO)
│   ├── controllers/        # controladores da loja e do admin
│   ├── services/           # gateways de pagamento
│   ├── views/              # templates PHP
│   └── routes.php          # tabela de rotas
│   └── .htaccess           # bloqueia acesso direto (Require all denied)
├── database/
│   ├── schema.sql          # estrutura das tabelas
│   ├── seed.sql            # dados iniciais (admin, categorias, exemplos)
│   └── .htaccess           # bloqueia acesso direto
├── bin/.htaccess           # bloqueia acesso direto
├── .env.example
└── README.md
```

> **Por quê a raiz web é a raiz do projeto, e não uma subpasta `public/`?**
> Assim o projeto funciona em hospedagens que não deixam trocar a Raiz do
> documento (ex.: Git deploy do Hostinger, que instala tudo direto em
> `public_html`). `app/`, `database/`, `bin/` e `logo/` ficam protegidos por
> `.htaccess` próprios (`Require all denied`) em vez de depender de uma
> pasta fora do alcance do navegador. Se seu servidor permitir apontar a
> Raiz do documento para uma subpasta isolada, isso é ainda mais seguro —
> mas não é obrigatório com essa estrutura.

---

## Instalação (local — XAMPP / Laragon / WAMP)

1. **Banco de dados**

   ```sql
   CREATE DATABASE ideias_flamboyant CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

   Importe a estrutura e os dados iniciais:

   ```bash
   mysql -u root -p ideias_flamboyant < database/schema.sql
   mysql -u root -p ideias_flamboyant < database/seed.sql
   ```

2. **Configuração**

   ```bash
   copy .env.example .env
   ```

   Edite `.env` com os dados do seu MySQL e a URL base.

3. **Servidor web**

   Aponte o `DocumentRoot` (ou a pasta pública do Laragon) para a **raiz do projeto**.
   Alternativa rápida com o servidor embutido do PHP (rode a partir da raiz do projeto):

   ```bash
   php -S localhost:8000 server.php
   ```

   (`server.php` é só um roteador que replica localmente as mesmas regras
   de bloqueio que o `.htaccess` aplica em produção — o servidor embutido
   do PHP não lê `.htaccess`.)

4. **Permissão de upload**

   Garanta que `uploads/` tenha permissão de escrita.

5. **Acesse**

   - Loja: `http://localhost:8000/`
   - Admin: `http://localhost:8000/admin` — login inicial:
     - **E-mail:** `marcospavani@gmail.com`
     - **Senha:** a definida ao gerar o hash em `database/seed.sql` *(não fica em texto puro em nenhum arquivo do repositório — troque por uma nova quando quiser em Admin → Usuários)*

---

## Pagamentos

Configuração em **Admin → Configurações → Pagamentos**:

| Gateway | Campos |
|---|---|
| Mercado Pago | Access Token, Public Key, ativar/desativar, modo sandbox |
| InfinitePay | Handle (usuário), Token/API Key, ativar/desativar |

### Webhooks

Cadastre as URLs de notificação em cada gateway:

- Mercado Pago: `https://SEU-DOMINIO/webhook/mercadopago`
- InfinitePay: `https://SEU-DOMINIO/webhook/infinitepay`

Ao receber a confirmação de pagamento aprovado, o pedido passa para `pago` e o
estoque é baixado automaticamente (com registro em `stock_movements`).

---

## API REST v1 (integração futura com sistema de vendas)

Base: `/api/v1`. Autenticação por header `Authorization: Bearer <token>`
(tokens gerenciados em **Admin → Configurações → API**).

| Método | Rota | Descrição |
|---|---|---|
| GET | `/api/v1/products` | Lista produtos + estoque |
| GET | `/api/v1/products/{id}` | Detalhe do produto |
| PATCH | `/api/v1/products/{id}/stock` | Ajusta estoque (`{"quantity": 10, "reason": "..."}`) |
| GET | `/api/v1/orders` | Lista pedidos (filtros `status`, `since`) |
| GET | `/api/v1/orders/{id}` | Detalhe do pedido |
| POST | `/api/v1/orders/{id}/status` | Atualiza status do pedido |
| GET | `/api/v1/stock/movements` | Histórico de movimentações |

Respostas em JSON. Projetado para que um ERP externo sincronize catálogo,
estoque e pedidos sem acoplamento ao front da loja.

---

## Segurança

- Senhas com `password_hash` (bcrypt).
- Proteção CSRF em todos os formulários.
- Prepared statements (PDO) em toda a camada de dados.
- Sessão de admin isolada, com regeneração de ID no login.
- Uploads validados por tipo MIME e extensão.

---

## Próximos passos sugeridos

- Cálculo de frete (Correios / transportadora).
- E-mails transacionais (confirmação de pedido).
- Cupons de desconto.
- Sincronização bidirecional com o ERP via fila.
