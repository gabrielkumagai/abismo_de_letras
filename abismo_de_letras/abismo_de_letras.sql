-- Criação do Banco de Dados
CREATE DATABASE IF NOT EXISTS abismo_de_letras CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE abismo_de_letras;

-- 1. Tabela de Usuários (AGORA COM FOTO DE PERFIL)
CREATE TABLE usuarios (
    id_usuario INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    tipo_usuario ENUM('escritor', 'leitor', 'estudante') NOT NULL, 
    data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP,
    foto_perfil VARCHAR(255) DEFAULT 'default.png'
);

-- 2. Tabela de Gêneros (NOVA TABELA PARA BUSCA)
CREATE TABLE generos (
    id_genero INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) UNIQUE NOT NULL
);

-- 3. Tabela de Histórias/Publicações
CREATE TABLE historias (
    id_historia INT PRIMARY KEY AUTO_INCREMENT,
    id_autor INT,
    titulo VARCHAR(255) NOT NULL,
    conteudo LONGTEXT NOT NULL,
    acesso ENUM('publico', 'restrito') DEFAULT 'publico',
    data_publicacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    id_historia_original INT NULL, 
    capa_imagem VARCHAR(255) DEFAULT 'default_capa.png',
    id_genero INT NULL,
    status_historia ENUM('ativo', 'deletado') DEFAULT 'ativo',
    motivo_exclusao TEXT NULL,
    
    FOREIGN KEY (id_autor) REFERENCES usuarios(id_usuario),
    FOREIGN KEY (id_historia_original) REFERENCES historias(id_historia),
    FOREIGN KEY (id_genero) REFERENCES generos(id_genero)
);

-- 4. Tabela para Comentários/Interações
CREATE TABLE interacoes (
    id_interacao INT PRIMARY KEY AUTO_INCREMENT,
    id_historia INT,
    id_usuario INT,
    texto TEXT,
    data_interacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_historia) REFERENCES historias(id_historia),
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
);

-- 5. Tabela de Badges (Gamificação)
CREATE TABLE badges (
    id_badge INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    descricao VARCHAR(255) NOT NULL,
    icone VARCHAR(50) 
);

-- 6. Tabela para rastrear quais Badges o usuário possui
CREATE TABLE usuario_badge (
    id_usuario_badge INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT,
    id_badge INT,
    data_conquista DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario),
    FOREIGN KEY (id_badge) REFERENCES badges(id_badge),
    UNIQUE KEY uq_usuario_badge (id_usuario, id_badge)
);

-- 7. Tabela de Seguidores
CREATE TABLE seguidores (
    id_seguidor INT,
    id_seguido INT,
    data_seguimento DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_seguidor, id_seguido),
    FOREIGN KEY (id_seguidor) REFERENCES usuarios(id_usuario),
    FOREIGN KEY (id_seguido) REFERENCES usuarios(id_usuario)
);

-- 8. Tabela de Redações ENEM (Colaboração)
CREATE TABLE redacoes_enem (
    id_redacao INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT,
    titulo VARCHAR(255) NOT NULL, 
    tema VARCHAR(255) NOT NULL,
    texto LONGTEXT NOT NULL,
    data_salva DATETIME DEFAULT CURRENT_TIMESTAMP,
    id_redacao_original INT NULL, 
    tipo_contribuicao ENUM('rascunho', 'continuação', 'correcao_peer') DEFAULT 'rascunho',
    
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario),
    FOREIGN KEY (id_redacao_original) REFERENCES redacoes_enem(id_redacao)
);

-- 9. Tabela de Modelos de Redação de Alta Pontuação (Nota +900)
CREATE TABLE modelos_nota_mil (
    id_modelo INT PRIMARY KEY AUTO_INCREMENT,
    titulo VARCHAR(255) NOT NULL,
    tema VARCHAR(255) NOT NULL,
    texto_modelo LONGTEXT NOT NULL,
    nota INT DEFAULT 960
);

-- 10. TABELA DE CONSULTA DE LIVROS ENEM (NOVA!)
CREATE TABLE livros_enem (
    id_livro INT PRIMARY KEY AUTO_INCREMENT,
    titulo VARCHAR(255) NOT NULL,
    autor VARCHAR(100) NOT NULL,
    escola_literaria VARCHAR(100) NOT NULL,
    sinopse TEXT NOT NULL,
    relevancia_enem TEXT
);

-- Popula Tabela de Livros ENEM (Exemplos Comuns)
INSERT INTO livros_enem (titulo, autor, escola_literaria, sinopse, relevancia_enem) VALUES
('Memórias Póstumas de Brás Cubas', 'Machado de Assis', 'Realismo', 'Narrado por um defunto autor, o livro é uma crítica mordaz à sociedade burguesa do Segundo Império, explorando temas como a vaidade e o pessimismo.', 'Essencial para o Realismo. Foca na ironia e crítica social, abordando o ceticismo do narrador.'),
('O Cortiço', 'Aluísio Azevedo', 'Naturalismo', 'Retrato da vida de uma habitação coletiva no Rio de Janeiro, o livro explora o determinismo e o zoomorfismo, mostrando a influência do meio no indivíduo.', 'Fundamental para o Naturalismo. Explora as teorias raciais e sociais da época.'),
('Vidas Secas', 'Graciliano Ramos', 'Modernismo (Segunda Geração)', 'A saga da família de Fabiano, Sinhá Vitória e os filhos em sua luta pela sobrevivência no sertão nordestino, marcada pela seca e pela miséria.', 'Importante para a prosa regionalista do Modernismo. Foca na linguagem seca e na crítica social.'),
('Capitães da Areia', 'Jorge Amado', 'Modernismo (Segunda Geração)', 'A história de um grupo de meninos de rua, conhecidos como "Capitães da Areia", que vivem de pequenos furtos e sobrevivem em Salvador.', 'Aborda temas sociais, desigualdade e a vida no subúrbio brasileiro.'),
('Iracema', 'José de Alencar', 'Romantismo', 'Um poema em prosa que narra o amor trágico entre a índia Iracema, "a virgem dos lábios de mel", e o colonizador português Martim, sendo uma obra fundadora do indianismo romântico.', 'Representa o Romantismo nacionalista e idealista.'),
('Primeiros Cantos', 'Gonçalves Dias', 'Romantismo', 'Coletânea de poesias que consolidou o indianismo no Brasil, com destaque para a "Canção do Exílio" e poemas de exaltação à natureza pátria.', 'Marca a primeira fase do Romantismo no Brasil.');

-- Popula a tabela de Gêneros (Dados de Exemplo)
INSERT INTO generos (nome) VALUES
('Fantasia'), ('Ficção Científica'), ('Romance'), ('Mistério'), ('Terror'), ('Poesia'), ('Drama');

-- Popula a tabela de Badges com as conquistas
INSERT INTO badges (nome, descricao, icone) VALUES
('Iniciador de Abismos', 'Publicou a primeira história original.', '✨'),
('O Colaborador', 'Publicou sua primeira versão alternativa.', '🔗'),
('Primeiro Feedback', 'Fez o primeiro comentário construtivo em uma história.', '💬'),
('Autor Produtivo', 'Publicou 5 ou mais histórias/versões.', '🖋️'),
('Crítico Engajado', 'Fez 10 ou mais comentários em diferentes histórias.', '🧐');