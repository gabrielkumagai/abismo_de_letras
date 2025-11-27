-- Criação do Banco de Dados
CREATE DATABASE IF NOT EXISTS abismo_de_letras CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE abismo_de_letras;

-- 1. Tabela de Usuários (Escritores, Leitores, Estudantes)
CREATE TABLE usuarios (
    id_usuario INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    tipo_usuario ENUM('escritor', 'leitor', 'estudante') NOT NULL, 
    data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 2. Tabela de Histórias/Publicações (Suporte à Colaboração)
CREATE TABLE historias (
    id_historia INT PRIMARY KEY AUTO_INCREMENT,
    id_autor INT,
    titulo VARCHAR(255) NOT NULL,
    conteudo LONGTEXT NOT NULL,
    acesso ENUM('publico', 'restrito') DEFAULT 'publico',
    data_publicacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    id_historia_original INT NULL, 
    
    FOREIGN KEY (id_autor) REFERENCES usuarios(id_usuario),
    FOREIGN KEY (id_historia_original) REFERENCES historias(id_historia)
);

-- 3. Tabela para Comentários/Interações
CREATE TABLE interacoes (
    id_interacao INT PRIMARY KEY AUTO_INCREMENT,
    id_historia INT,
    id_usuario INT,
    texto TEXT,
    data_interacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_historia) REFERENCES historias(id_historia),
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
);

-- 4. Tabela de Badges (Gamificação)
CREATE TABLE badges (
    id_badge INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    descricao VARCHAR(255) NOT NULL,
    icone VARCHAR(50) 
);

-- 5. Tabela para rastrear quais Badges o usuário possui
CREATE TABLE usuario_badge (
    id_usuario_badge INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT,
    id_badge INT,
    data_conquista DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario),
    FOREIGN KEY (id_badge) REFERENCES badges(id_badge),
    UNIQUE KEY uq_usuario_badge (id_usuario, id_badge)
);

-- NOVAS TABELAS PARA FUNCIONALIDADES AVANÇADAS

-- 6. Tabela de Seguidores
CREATE TABLE seguidores (
    id_seguidor INT,
    id_seguido INT,
    data_seguimento DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_seguidor, id_seguido),
    FOREIGN KEY (id_seguidor) REFERENCES usuarios(id_usuario),
    FOREIGN KEY (id_seguido) REFERENCES usuarios(id_usuario)
);

-- 7. Tabela de Redações ENEM (Simulação)
CREATE TABLE redacoes_enem (
    id_redacao INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT,
    tema VARCHAR(255) NOT NULL,
    texto LONGTEXT NOT NULL,
    status ENUM('rascunho', 'submetido') DEFAULT 'rascunho',
    data_salva DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
);

-- Popula a tabela de Badges com as conquistas
INSERT INTO badges (nome, descricao, icone) VALUES
('Iniciador de Abismos', 'Publicou a primeira história original.', '✨'),
('O Colaborador', 'Publicou sua primeira versão alternativa.', '🔗'),
('Primeiro Feedback', 'Fez o primeiro comentário construtivo em uma história.', '💬'),
('Autor Produtivo', 'Publicou 5 ou mais histórias/versões.', '🖋️'),
('Crítico Engajado', 'Fez 10 ou mais comentários em diferentes histórias.', '🧐');