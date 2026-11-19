
CREATE DATABASE IF NOT EXISTS gerenciador_logistico
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE gerenciador_logistico;


-- TABELA DE PRODUTOS
CREATE TABLE IF NOT EXISTS produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(30) NOT NULL UNIQUE,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    unidade VARCHAR(20),
    quantidade INT NOT NULL DEFAULT 0,
    peso DECIMAL(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB;


-- TABELA DE ENDEREÇOS
CREATE TABLE IF NOT EXISTS enderecos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(30) NOT NULL UNIQUE,
    identificacao VARCHAR(100) NOT NULL,
    rua VARCHAR(150),
    numero VARCHAR(20),
    bairro VARCHAR(100),
    cidade VARCHAR(100),
    estado VARCHAR(2),
    cep VARCHAR(10)
) ENGINE=InnoDB;


-- TABELA DE TRANSPORTADORES
CREATE TABLE IF NOT EXISTS transportadores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    telefone VARCHAR(20),
    email VARCHAR(100)
) ENGINE=InnoDB;


-- TABELA DE FRETES
CREATE TABLE IF NOT EXISTS fretes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    produto_id INT,
    transportador_id INT,
    origem_id INT,
    destino_id INT,
    data_saida DATE,
    data_entrega DATE,
    valor DECIMAL(10,2),
    situacao VARCHAR(30),

    FOREIGN KEY (produto_id) REFERENCES produtos(id),
    FOREIGN KEY (transportador_id) REFERENCES transportadores(id),
    FOREIGN KEY (origem_id) REFERENCES enderecos(id),
    FOREIGN KEY (destino_id) REFERENCES enderecos(id)
) ENGINE=InnoDB;
