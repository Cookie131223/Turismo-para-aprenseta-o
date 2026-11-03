-- Versão Postgres do schema.sql (usada no Supabase quando o site roda na Vercel).
-- O banco do MySQL continua em schema.sql.

CREATE TABLE IF NOT EXISTS clientes (
  id SERIAL PRIMARY KEY,
  nome VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  senha VARCHAR(255) NOT NULL,
  role VARCHAR(50) NOT NULL DEFAULT 'user',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS destinos (
  id SERIAL PRIMARY KEY,
  nome VARCHAR(255) NOT NULL,
  pais VARCHAR(255) NOT NULL,
  descricao TEXT NOT NULL,
  preco DECIMAL(10,2) NOT NULL,
  imagem VARCHAR(500) NOT NULL,
  lat DECIMAL(9,6) NULL,
  lon DECIMAL(9,6) NULL
);

CREATE TABLE IF NOT EXISTS reservas (
  id SERIAL PRIMARY KEY,
  cliente_id INT NOT NULL REFERENCES clientes(id),
  destino_id INT NOT NULL REFERENCES destinos(id),
  destino_nome VARCHAR(255) NOT NULL,
  valor DECIMAL(10,2) NOT NULL,
  data_reserva TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  status VARCHAR(50) DEFAULT 'aguardando_pagamento',
  forma_pagamento VARCHAR(50) NULL,
  data_pagamento TIMESTAMP NULL,
  data_ida DATE NULL,
  data_volta DATE NULL,
  viajantes INT NOT NULL DEFAULT 1,
  cupom VARCHAR(40) NULL,
  desconto DECIMAL(10,2) NOT NULL DEFAULT 0,
  codigo VARCHAR(12) NULL
);

CREATE TABLE IF NOT EXISTS favoritos (
  cliente_id INT NOT NULL,
  destino_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (cliente_id, destino_id)
);

CREATE TABLE IF NOT EXISTS avaliacoes (
  id SERIAL PRIMARY KEY,
  cliente_id INT NOT NULL,
  destino_id INT NOT NULL,
  nota INT NOT NULL,
  comentario TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE (cliente_id, destino_id)
);

CREATE TABLE IF NOT EXISTS cupons (
  codigo VARCHAR(40) PRIMARY KEY,
  percentual INT NOT NULL,
  descricao VARCHAR(160) NOT NULL,
  ativo INT NOT NULL DEFAULT 1
);

CREATE TABLE IF NOT EXISTS tripora_meta (
  chave VARCHAR(40) PRIMARY KEY,
  valor VARCHAR(255) NOT NULL
);

-- Sessões de login guardadas no banco (a Vercel não mantém arquivos entre acessos)
CREATE TABLE IF NOT EXISTS sessoes (
  id VARCHAR(128) PRIMARY KEY,
  dados TEXT NOT NULL DEFAULT '',
  atualizado INT NOT NULL
);

INSERT INTO cupons (codigo, percentual, descricao, ativo) VALUES
('TRIPORA10', 10, '10% de desconto em qualquer destino', 1),
('BEMVINDO15', 15, '15% de desconto de boas-vindas', 1),
('APRESENTACAO20', 20, '20% de desconto para a apresentação', 1)
ON CONFLICT (codigo) DO NOTHING;

INSERT INTO tripora_meta (chave, valor) VALUES ('schema', '2') ON CONFLICT (chave) DO NOTHING;

INSERT INTO destinos (nome,pais,descricao,preco,imagem)
SELECT v.* FROM (VALUES
('Dubai','Emirados Árabes Unidos','Arquitetura futurista, deserto e experiências premium.',5800.00,'https://images.unsplash.com/photo-1512453979798-5ea266f8880c?auto=format&fit=crop&w=900&q=80'),
('Paris','França','Arte, gastronomia e os cartões-postais mais famosos da Europa.',6900.00,'https://images.unsplash.com/photo-1502602898657-3e91760cbb34?auto=format&fit=crop&w=900&q=80'),
('Rio de Janeiro','Brasil','Praias, natureza e uma das cidades mais icônicas do país.',3200.00,'https://images.unsplash.com/photo-1483729558449-99ef09a8c325?auto=format&fit=crop&w=900&q=80'),
('Tóquio','Japão','Tecnologia, tradição, gastronomia e bairros vibrantes em uma das cidades mais fascinantes do mundo.',8200.00,'https://images.unsplash.com/photo-1540959733332-eab4deabeeaf?auto=format&fit=crop&w=900&q=80'),
('Nova York','Estados Unidos','Arranha-céus, Broadway, Central Park e a energia única da cidade que nunca dorme.',7600.00,'https://images.unsplash.com/photo-1485871981521-5b1fd3805eee?auto=format&fit=crop&w=900&q=80'),
('Roma','Itália','História, arquitetura, gastronomia italiana e alguns dos monumentos mais famosos do mundo.',6400.00,'https://images.unsplash.com/photo-1552832230-c0197dd311b5?auto=format&fit=crop&w=900&q=80'),
('Lisboa','Portugal','Mirantes, ruas históricas, gastronomia e o charme de uma das capitais mais acolhedoras da Europa.',5200.00,'https://images.unsplash.com/photo-1525207934214-58e69a8f8a93?auto=format&fit=crop&w=900&q=80'),
('Buenos Aires','Argentina','Cultura, tango, gastronomia e arquitetura clássica em uma viagem cheia de personalidade.',2900.00,'https://images.unsplash.com/photo-1589909202802-8f4aadce1849?auto=format&fit=crop&w=900&q=80'),
('Cancún','México','Mar azul-turquesa, resorts, praias paradisíacas e experiências inesquecíveis no Caribe.',6100.00,'https://images.unsplash.com/photo-1552074284-5e88ef1aef18?auto=format&fit=crop&w=900&q=80')
) AS v(nome,pais,descricao,preco,imagem)
WHERE NOT EXISTS (SELECT 1 FROM destinos);
