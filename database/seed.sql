-- =====================================================================
--  Idéias Flamboyant — dados iniciais
--  Login admin:  marcospavani@gmail.com  /  (senha definida na criação do hash abaixo)
-- =====================================================================
SET NAMES utf8mb4;

-- ---- Usuário administrador -------------------------------------------
INSERT INTO users (name, email, password_hash, role) VALUES
('Marcos Pavani', 'marcospavani@gmail.com',
 '$2y$12$nSa3CAgC4s3j1sit0KwU.Oxm8Hjlx76Gcq9ZlogAMOSY4JNfb6sLq', 'admin');

-- ---- Configurações ------------------------------------------------------
INSERT INTO settings (`key`, `value`, `group`, is_secret) VALUES
('store_name',        'Idéias Flamboyant',                         'geral',   0),
('store_tagline',     'Materiais terapêuticos, brinquedos e decoração em impressão 3D', 'geral', 0),
('store_email',       'contato@ideiasflamboyant.com.br',           'geral',   0),
('store_city',        'Goiânia - GO',                              'geral',   0),
('currency',          'BRL',                                       'geral',   0),

('whatsapp_number',   '5562999999999',                             'contato', 0),
('whatsapp_message',  'Olá! Vim pela loja da Idéias Flamboyant e gostaria de mais informações.', 'contato', 0),
('instagram_url',     'https://instagram.com/ideiasflamboyant',    'contato', 0),

('shipping_flat_rate','0.00',                                      'entrega', 0),
('shipping_free_above','0.00',                                     'entrega', 0),
('shipping_note',     'Enviamos para todo o Brasil. O frete é combinado pelo WhatsApp.', 'entrega', 0),

-- Pagamentos (configurar chaves reais pelo painel admin)
('mp_enabled',        '0',                                         'pagamento', 0),
('mp_sandbox',        '1',                                         'pagamento', 0),
('mp_access_token',   '',                                          'pagamento', 1),
('mp_public_key',     '',                                          'pagamento', 1),

('infinitepay_enabled','0',                                        'pagamento', 0),
('infinitepay_handle', '',                                         'pagamento', 0),
('infinitepay_token',  '',                                         'pagamento', 1),

('payment_whatsapp_fallback','1',                                  'pagamento', 0);

-- ---- Categorias -------------------------------------------------------
INSERT INTO categories (name, slug, description, position) VALUES
('Terapêuticos',   'terapeuticos',   'Recursos para estímulo sensorial, foco, coordenação e comunicação', 1),
('Brinquedos',     'brinquedos',     'Peças lúdicas que unem diversão e desenvolvimento',                 2),
('Decoração',      'decoracao',      'Objetos autorais para deixar a casa com a sua cara',                3),
('Personalizados', 'personalizados', 'Projetos sob medida, modelados e impressos para você',              4);

-- ---- Produtos de exemplo (todos em PLA) --------------------------
INSERT INTO products
(category_id, name, slug, sku, short_description, description, price, compare_at_price,
 material, color, weight_grams, dimensions, is_made_to_order,
 stock_quantity, low_stock_alert, is_active, is_featured) VALUES
(1, 'Fidget Infinito Sensorial', 'fidget-infinito-sensorial', 'IF-TER-001',
 'Peça articulada para autorregulação, foco e alívio de ansiedade.',
 'Fidget de dobras infinitas impresso em PLA, com movimento silencioso e encaixe firme. Ajuda na concentração e na regulação sensorial. Leve, cabe no bolso e é fácil de higienizar.',
 39.90, 54.90, 'PLA', 'À escolha', 60, '7 x 7 x 2 cm', 0, 15, 4, 1, 1),

(1, 'Prancha de Comunicação Alternativa', 'prancha-comunicacao-alternativa', 'IF-TER-002',
 'Base com cartões de CAA para apoio à comunicação.',
 'Prancha em PLA com suporte para cartões de comunicação alternativa e ampliada (CAA). Estrutura resistente, cantos arredondados e cartões substituíveis. Ótima para uso por famílias e profissionais.',
 129.90, NULL, 'PLA', 'À escolha', 320, '25 x 18 x 2 cm', 0, 6, 3, 1, 1),

(2, 'Quebra-cabeça 3D de Encaixe', 'quebra-cabeca-3d-encaixe', 'IF-BRK-003',
 'Blocos de encaixe que estimulam lógica e coordenação motora.',
 'Conjunto de peças de encaixe impressas em PLA, com formas geométricas coloridas. Trabalha coordenação motora fina, noção espacial e raciocínio. Indicado a partir de 3 anos.',
 59.90, NULL, 'PLA', 'Sortido', 180, '15 x 15 x 6 cm', 0, 9, 3, 1, 1),

(2, 'Jogo da Velha de Viagem', 'jogo-da-velha-de-viagem', 'IF-BRK-004',
 'Tabuleiro compacto com peças que encaixam — leva para qualquer lugar.',
 'Jogo da velha em PLA com tabuleiro e 10 peças que encaixam para não perder nada. Diversão rápida em casa ou na viagem.',
 34.90, NULL, 'PLA', 'À escolha', 90, '9 x 9 x 2 cm', 0, 12, 4, 1, 0),

(3, 'Vaso Geométrico Flamboyant', 'vaso-geometrico-flamboyant', 'IF-DEC-005',
 'Vaso decorativo com facetas geométricas, perfeito para suculentas.',
 'Vaso impresso em PLA com acabamento fosco e design geométrico exclusivo da Idéias Flamboyant. Ideal para suculentas e cactos. Acompanha furo de drenagem.',
 89.90, 119.90, 'PLA', 'Laranja Flamboyant', 180, '12 x 12 x 14 cm', 0, 10, 3, 1, 1),

(4, 'Projeto Personalizado 3D', 'projeto-personalizado-3d', 'IF-PER-006',
 'Transforme sua ideia em um produto real impresso em 3D.',
 'Envie seu modelo ou uma descrição do que precisa. Fazemos a modelagem, a prototipagem e a impressão em PLA. Orçamento pelo WhatsApp.',
 0.00, NULL, 'PLA', 'A definir', NULL, 'Sob medida', 1, 0, 0, 1, 0);

-- ---- Movimentações de estoque iniciais ----------------------------
INSERT INTO stock_movements (product_id, type, quantity, balance_after, reason, reference) VALUES
(1, 'entrada', 15, 15, 'Estoque inicial', 'seed'),
(2, 'entrada', 6,  6,  'Estoque inicial', 'seed'),
(3, 'entrada', 9,  9,  'Estoque inicial', 'seed'),
(4, 'entrada', 12, 12, 'Estoque inicial', 'seed'),
(5, 'entrada', 10, 10, 'Estoque inicial', 'seed');
