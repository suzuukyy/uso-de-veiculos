-- Script para criar as tabelas do sistema
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    admin BOOLEAN DEFAULT 0
);

CREATE TABLE motoristas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    cnh VARCHAR(20) NOT NULL UNIQUE
);

CREATE TABLE veiculos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    placa VARCHAR(10) NOT NULL UNIQUE,
    modelo VARCHAR(100) NOT NULL
);

CREATE TABLE uso_veiculos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    veiculo_id INT NOT NULL,
    motorista_id INT NOT NULL,
    usuario_id INT NOT NULL,
    data_saida DATETIME NOT NULL,
    data_retorno DATETIME,
    observacao VARCHAR(255),
    FOREIGN KEY (veiculo_id) REFERENCES veiculos(id),
    FOREIGN KEY (motorista_id) REFERENCES motoristas(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- Usuário admin padrão (senha: admin123)
INSERT INTO usuarios (nome, usuario, senha, admin) VALUES ('Administrador', 'admin', '$2y$10$Z9w4QYw5v0r8zq6l2QyG6uZ2vQe3m6sQyQe5uYw6uQyQe5uYw6uQe', 1);
