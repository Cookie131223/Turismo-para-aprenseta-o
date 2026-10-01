CREATE DATABASE IF NOT EXISTS turismo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE turismo;

CREATE TABLE IF NOT EXISTS clientes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  senha VARCHAR(255) NOT NULL,
  role VARCHAR(50) NOT NULL DEFAULT 'user',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS destinos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(255) NOT NULL,
  pais VARCHAR(255) NOT NULL,
  descricao TEXT NOT NULL,
  preco DECIMAL(10,2) NOT NULL,
  imagem VARCHAR(500) NOT NULL
);

CREATE TABLE IF NOT EXISTS reservas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  cliente_id INT NOT NULL,
  destino_id INT NOT NULL,
  destino_nome VARCHAR(255) NOT NULL,
  valor DECIMAL(10,2) NOT NULL,
  data_reserva DATETIME DEFAULT CURRENT_TIMESTAMP,
  status VARCHAR(50) DEFAULT 'aguardando_pagamento',
  forma_pagamento VARCHAR(50) NULL,
  data_pagamento DATETIME NULL,
  CONSTRAINT fk_reserva_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id),
  CONSTRAINT fk_reserva_destino FOREIGN KEY (destino_id) REFERENCES destinos(id)
);

INSERT INTO destinos (nome,pais,descricao,preco,imagem) VALUES
('Dubai','Emirados Árabes Unidos','Arquitetura futurista, deserto e experiências premium.',5800.00,'https://images.unsplash.com/photo-1512453979798-5ea266f8880c?auto=format&fit=crop&w=900&q=80'),
('Paris','França','Arte, gastronomia e os cartões-postais mais famosos da Europa.',6900.00,'https://images.unsplash.com/photo-1502602898657-3e91760cbb34?auto=format&fit=crop&w=900&q=80'),
('Rio de Janeiro','Brasil','Praias, natureza e uma das cidades mais icônicas do país.',3200.00,'https://images.unsplash.com/photo-1483729558449-99ef09a8c325?auto=format&fit=crop&w=900&q=80'),
('Tóquio','Japão','Tecnologia, tradição, gastronomia e bairros vibrantes em uma das cidades mais fascinantes do mundo.',8200.00,'https://images.unsplash.com/photo-1540959733332-eab4deabeeaf?auto=format&fit=crop&w=900&q=80'),
('Nova York','Estados Unidos','Arranha-céus, Broadway, Central Park e a energia única da cidade que nunca dorme.',7600.00,'https://images.unsplash.com/photo-1485871981521-5b1fd3805eee?auto=format&fit=crop&w=900&q=80'),
('Roma','Itália','História, arquitetura, gastronomia italiana e alguns dos monumentos mais famosos do mundo.',6400.00,'https://images.unsplash.com/photo-1552832230-c0197dd311b5?auto=format&fit=crop&w=900&q=80'),
('Lisboa','Portugal','Mirantes, ruas históricas, gastronomia e o charme de uma das capitais mais acolhedoras da Europa.',5200.00,'https://images.unsplash.com/photo-1525207934214-58e69a8f8a93?auto=format&fit=crop&w=900&q=80'),
('Buenos Aires','Argentina','Cultura, tango, gastronomia e arquitetura clássica em uma viagem cheia de personalidade.',2900.00,'https://images.unsplash.com/photo-1589909202802-8f4aadce1849?auto=format&fit=crop&w=900&q=80'),
('Cancún','México','Mar azul-turquesa, resorts, praias paradisíacas e experiências inesquecíveis no Caribe.',6100.00,'https://images.unsplash.com/photo-1552074284-5e88ef1aef18?auto=format&fit=crop&w=900&q=80');
