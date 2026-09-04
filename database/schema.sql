-- =====================================================================
--  Idéias Flamboyant — E-commerce  |  Estrutura do banco (MySQL 8 / utf8mb4)
-- =====================================================================
SET NAMES utf8mb4;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;

-- ---------------------------------------------------------------------
--  Usuários do painel administrativo
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS users;
CREATE TABLE users (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name          VARCHAR(120)  NOT NULL,
  email         VARCHAR(160)  NOT NULL,
  password_hash VARCHAR(255)  NOT NULL,
  role          ENUM('admin','manager') NOT NULL DEFAULT 'admin',
  is_active     TINYINT(1)    NOT NULL DEFAULT 1,
  last_login_at DATETIME      NULL,
  created_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  Configurações gerais (chave/valor) — loja, WhatsApp, pagamentos
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS settings;
CREATE TABLE settings (
  `key`       VARCHAR(80)  NOT NULL,
  `value`     TEXT         NULL,
  `group`     VARCHAR(40)  NOT NULL DEFAULT 'geral',
  is_secret   TINYINT(1)   NOT NULL DEFAULT 0,
  updated_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  Categorias
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS categories;
CREATE TABLE categories (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name        VARCHAR(120) NOT NULL,
  slug        VARCHAR(140) NOT NULL,
  description VARCHAR(400) NULL,
  parent_id   INT UNSIGNED NULL,
  position    INT          NOT NULL DEFAULT 0,
  is_active   TINYINT(1)   NOT NULL DEFAULT 1,
  created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_categories_slug (slug),
  KEY idx_categories_parent (parent_id),
  CONSTRAINT fk_categories_parent FOREIGN KEY (parent_id) REFERENCES categories (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  Produtos
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS products;
CREATE TABLE products (
  id               INT UNSIGNED NOT NULL AUTO_INCREMENT,
  category_id      INT UNSIGNED NULL,
  name             VARCHAR(180)  NOT NULL,
  slug             VARCHAR(200)  NOT NULL,
  sku              VARCHAR(60)   NULL,
  short_description VARCHAR(400)  NULL,
  description      TEXT          NULL,
  price            DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  compare_at_price DECIMAL(10,2) NULL,
  cost_price       DECIMAL(10,2) NULL,
  -- ficha técnica da impressão 3D
  material         VARCHAR(80)   NULL,       -- PLA, PETG, ABS, Resina...
  color            VARCHAR(80)   NULL,
  weight_grams     INT           NULL,
  dimensions       VARCHAR(120)  NULL,       -- "10 x 8 x 5 cm"
  print_time_hours DECIMAL(5,2)  NULL,
  is_made_to_order TINYINT(1)    NOT NULL DEFAULT 0,  -- sob encomenda (não usa estoque)
  -- estoque
  stock_quantity   INT           NOT NULL DEFAULT 0,
  low_stock_alert  INT           NOT NULL DEFAULT 3,
  track_stock      TINYINT(1)    NOT NULL DEFAULT 1,
  -- status
  is_active        TINYINT(1)    NOT NULL DEFAULT 1,
  is_featured      TINYINT(1)    NOT NULL DEFAULT 0,
  views            INT UNSIGNED  NOT NULL DEFAULT 0,
  created_at       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_products_slug (slug),
  KEY idx_products_category (category_id),
  KEY idx_products_active (is_active),
  KEY idx_products_featured (is_featured),
  CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  Imagens de produto
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS product_images;
CREATE TABLE product_images (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  product_id INT UNSIGNED NOT NULL,
  path       VARCHAR(255) NOT NULL,
  alt        VARCHAR(180) NULL,
  position   INT          NOT NULL DEFAULT 0,
  is_primary TINYINT(1)   NOT NULL DEFAULT 0,
  created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_product_images_product (product_id),
  CONSTRAINT fk_product_images_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  Pedidos
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS orders;
CREATE TABLE orders (
  id                INT UNSIGNED NOT NULL AUTO_INCREMENT,
  reference         VARCHAR(20)  NOT NULL,                 -- código curto exibido ao cliente
  status            ENUM('pendente','pago','em_producao','enviado','concluido','cancelado')
                    NOT NULL DEFAULT 'pendente',
  payment_status    ENUM('pendente','aprovado','recusado','estornado','cancelado')
                    NOT NULL DEFAULT 'pendente',
  payment_method    VARCHAR(30)  NULL,                     -- mercadopago | infinitepay | whatsapp
  payment_gateway_id VARCHAR(120) NULL,                    -- id da preferência/cobrança no gateway
  payment_link      VARCHAR(500) NULL,
  -- cliente
  customer_name     VARCHAR(160) NOT NULL,
  customer_email    VARCHAR(180) NULL,
  customer_phone    VARCHAR(40)  NOT NULL,
  customer_document VARCHAR(30)  NULL,                     -- CPF/CNPJ
  -- entrega
  shipping_zip      VARCHAR(12)  NULL,
  shipping_street   VARCHAR(200) NULL,
  shipping_number   VARCHAR(20)  NULL,
  shipping_complement VARCHAR(120) NULL,
  shipping_district VARCHAR(120) NULL,
  shipping_city     VARCHAR(120) NULL,
  shipping_state    VARCHAR(2)   NULL,
  shipping_method   VARCHAR(60)  NULL,
  -- valores
  subtotal          DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  shipping_cost     DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  discount          DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  total             DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  notes             TEXT          NULL,
  stock_committed   TINYINT(1)    NOT NULL DEFAULT 0,      -- estoque já baixado?
  external_ref      VARCHAR(80)   NULL,                    -- id no sistema de vendas/ERP
  paid_at           DATETIME      NULL,
  created_at        DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at        DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_orders_reference (reference),
  KEY idx_orders_status (status),
  KEY idx_orders_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  Itens do pedido
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS order_items;
CREATE TABLE order_items (
  id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  order_id     INT UNSIGNED NOT NULL,
  product_id   INT UNSIGNED NULL,
  product_name VARCHAR(180) NOT NULL,      -- snapshot
  sku          VARCHAR(60)  NULL,
  unit_price   DECIMAL(10,2) NOT NULL,
  quantity     INT           NOT NULL DEFAULT 1,
  line_total   DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (id),
  KEY idx_order_items_order (order_id),
  KEY idx_order_items_product (product_id),
  CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE,
  CONSTRAINT fk_order_items_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  Histórico de pagamentos / eventos de gateway
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS payment_events;
CREATE TABLE payment_events (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  order_id   INT UNSIGNED NULL,
  gateway    VARCHAR(30)  NOT NULL,
  event_type VARCHAR(60)  NULL,
  status     VARCHAR(40)  NULL,
  payload    JSON         NULL,
  created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_payment_events_order (order_id),
  CONSTRAINT fk_payment_events_order FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  Movimentações de estoque
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS stock_movements;
CREATE TABLE stock_movements (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  product_id  INT UNSIGNED NOT NULL,
  type        ENUM('entrada','saida','ajuste') NOT NULL,
  quantity    INT          NOT NULL,          -- valor absoluto movimentado
  balance_after INT        NOT NULL,          -- saldo resultante
  reason      VARCHAR(200) NULL,
  reference   VARCHAR(80)  NULL,              -- ex.: "pedido #IF-000123" ou "API"
  user_id     INT UNSIGNED NULL,
  created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_stock_movements_product (product_id),
  CONSTRAINT fk_stock_movements_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE,
  CONSTRAINT fk_stock_movements_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  Tokens de API (integração com sistema de vendas/ERP)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS api_tokens;
CREATE TABLE api_tokens (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name        VARCHAR(120) NOT NULL,
  token_hash  CHAR(64)     NOT NULL,          -- sha256 do token
  token_preview VARCHAR(16) NOT NULL,         -- primeiros caracteres p/ exibição
  scopes      VARCHAR(255) NOT NULL DEFAULT 'read',  -- csv: read,write
  is_active   TINYINT(1)   NOT NULL DEFAULT 1,
  last_used_at DATETIME    NULL,
  created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_api_tokens_hash (token_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  Log de auditoria simples
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS activity_log;
CREATE TABLE activity_log (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id    INT UNSIGNED NULL,
  action     VARCHAR(120) NOT NULL,
  entity     VARCHAR(60)  NULL,
  entity_id  INT UNSIGNED NULL,
  meta       JSON         NULL,
  ip         VARCHAR(45)  NULL,
  created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_activity_log_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET foreign_key_checks = 1;
