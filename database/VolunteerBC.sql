CREATE DATABASE IF NOT EXISTS volunteerconnect;
USE volunteerconnect;


CREATE TABLE instituicoes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
	area ENUM(
        'meio ambiente',
        'educacao',
        'assistencia social',
        'saude',
        'protecao animal',
        'cultura e esportes'
    ) NOT NULL,
	endereco VARCHAR(200) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    disponibilidade ENUM(
        'manha',
        'tarde',
        'noite',
        'fim de semana',
        'qualquer horario'
    ) NOT NULL,
	senha VARCHAR(255) NULL
);


CREATE TABLE voluntarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    endereco VARCHAR(200) NOT NULL,
    cpf VARCHAR(100),
    email VARCHAR(150) NOT NULL UNIQUE,
	area_atuacao ENUM(
        'meio ambiente',
        'educacao',
        'assistencia social',
        'saude',
        'protecao animal',
        'cultura e esportes'
    ) NOT NULL,
	disponibilidade ENUM(
        'manha',
        'tarde',
        'noite',
        'fim de semana',
        'qualquer horario'
    ) NOT NULL,
	senha VARCHAR(255) NULL
);


CREATE TABLE campanhas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_instituicao INT NOT NULL,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
	endereco VARCHAR(200) NOT NULL,
    horario TIME NOT NULL,
	disponibilidade ENUM(
        'manha',
        'tarde',
        'noite',
        'fim de semana',
        'qualquer horario'
    ) NOT NULL,
	status ENUM(
        'planejada',
        'ativa',
        'encerrada',
        'cancelada'
    ) NOT NULL,
	vagas INT NOT NULL,
    vagasTotais INT NOT NULL,

    FOREIGN KEY (id_instituicao)
    REFERENCES instituicoes(id)
    ON DELETE CASCADE
);


CREATE TABLE inscricoes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_campanha INT NOT NULL,
    id_voluntario INT NOT NULL,
	status ENUM(
        'aguardando',
        'andamento',
        'finalizado',
        'cancelado'
    ) NOT NULL,
	horas INT DEFAULT 0,

    FOREIGN KEY (id_campanha)
    REFERENCES campanhas(id)
    ON DELETE CASCADE,

    FOREIGN KEY (id_voluntario)
    REFERENCES voluntarios(id)
    ON DELETE CASCADE,

    UNIQUE (id_campanha, id_voluntario)
);


INSERT INTO instituicoes
(id, nome, area, endereco, email, disponibilidade, senha)
VALUES
(1, 'Instituto Saber Comunitário', 'educacao',
'Rua das Flores, 123 - Centro, São Paulo - SP',
'contato@sabercomunitario.org', 'manha', NULL),

(2, 'TechSocial Brasil', 'cultura e esportes',
'Av. Paulista, 1000, Cj 502 - Bela Vista, São Paulo - SP',
'suporte@techsocial.org.br', 'tarde', NULL),

(3, 'Saúde & Vida Voluntários', 'saude',
'Rua Doutor Arnaldo, 450 - Pacaembu, São Paulo - SP',
'atendimento@saudeevida.org', 'manha', NULL),

(4, 'Escola Viva Periferia', 'educacao',
'Rua das Oliveiras, 88 - Heliópolis, São Paulo - SP',
'contato@escolaviva.org', 'fim de semana', NULL),

(5, 'Inclusão Digital para Todos', 'educacao',
'Rua Vergueiro, 2030 - Vila Mariana, São Paulo - SP',
'projetos@inclusaodigital.org', 'tarde', NULL);


INSERT INTO voluntarios
(id, nome, endereco, email, cpf, area_atuacao, disponibilidade, senha)
VALUES
(1, 'Ana Clara Silva',
'Rua Augusta, 500 - Consolação, São Paulo - SP',
'ana.silva@email.com', '548.652.525-58',
'educacao', 'manha', NULL),

(2, 'Carlos Eduardo Souza',
'Av. Brasil, 1200 - Jardins, São Paulo - SP',
'carlos.souza@email.com', '456.123.854-36',
'cultura e esportes', 'tarde', NULL),

(3, 'Mariana Oliveira Santos',
'Rua Domingos de Morais, 300 - Vila Mariana, São Paulo - SP',
'mariana.santos@email.com', '565.125.963-98',
'saude', 'noite', NULL),

(4, 'Lucas Gabriel Ferreira',
'Rua Clélia, 850 - Lapa, São Paulo - SP',
'lucas.ferreira@email.com', '321.654.987-00',
'protecao animal', 'fim de semana', NULL),

(5, 'Beatriz Lima Rocha',
'Av. Jabaquara, 1500 - Saúde, São Paulo - SP',
'beatriz.rocha@email.com', '528.654.697-01',
'educacao', 'manha', NULL),

(6, 'Felipe Alves Costa',
'Rua Teodoro Sampaio, 900 - Pinheiros, São Paulo - SP',
'felipe.costa@email.com', '852.654.385-91',
'meio ambiente', 'qualquer horario', NULL),

(7, 'Juliana Mendes Pereira',
'Rua Voluntários da Pátria, 2100 - Santana, São Paulo - SP',
'juliana.mendes@email.com', '128.927.682-98',
'assistencia social', 'fim de semana', NULL);


INSERT INTO campanhas
(id, id_instituicao, nome, descricao, endereco,
horario, disponibilidade, status, vagas, vagasTotais)
VALUES

(1, 1, 'Reforço Escolar de Matemática',
'Aulas de apoio para crianças do 5º ao 9º ano da rede pública.',
'Rua das Flores, 123 - Centro, São Paulo - SP',
'08:00:00', 'manha', 'ativa', 10, 20),

(2, 2, 'Lógica de Programação para Jovens',
'Oficina introdutória de lógica e algoritmos para adolescentes.',
'Av. Paulista, 1000, Cj 502 - Bela Vista, São Paulo - SP',
'14:00:00', 'tarde', 'ativa', 5, 10),

(3, 3, 'Mutirão da Saúde Comunitária',
'Atendimento preventivo, aferição de pressão e exames básicos de glicemia.',
'Rua Doutor Arnaldo, 450 - Pacaembu, São Paulo - SP',
'09:00:00', 'manha', 'ativa', 8, 10),

(4, 4, 'Biblioteca Comunitária e Leitura',
'Organização do acervo de livros e mediação de leitura infantil.',
'Rua das Oliveiras, 88 - Heliópolis, São Paulo - SP',
'10:00:00', 'fim de semana', 'planejada', 6, 8),

(5, 5, 'Informática Básica para Idosos',
'Curso prático para ensinar idosos a utilizarem smartphones e computadores.',
'Rua Vergueiro, 2030 - Vila Mariana, São Paulo - SP',
'15:00:00', 'tarde', 'encerrada', 4, 6),

(6, 3, 'Campanha de Doação de Sangue',
'Apoio logístico e triagem na campanha de conscientização sobre doação.',
'Rua Doutor Arnaldo, 450 - Pacaembu, São Paulo - SP',
'08:00:00', 'manha', 'cancelada', 12, 20);


INSERT INTO inscricoes
(id, id_campanha, id_voluntario, status, horas)
VALUES
(1, 1, 1, 'andamento', 12),
(2, 1, 5, 'andamento', 8),
(3, 2, 2, 'andamento', 15),
(4, 2, 7, 'aguardando', 0),
(5, 3, 3, 'andamento', 20),
(6, 3, 6, 'andamento', 10),
(7, 4, 1, 'aguardando', 0),
(8, 5, 4, 'finalizado', 30),
(9, 5, 2, 'finalizado', 25),
(10, 6, 3, 'cancelado', 0);


CREATE OR REPLACE VIEW enderecos_por_voluntario AS
SELECT
    v.id AS id_voluntario,
    v.nome AS nome_voluntario,
    v.email,
    v.endereco
FROM voluntarios v;

CREATE OR REPLACE VIEW campanhas_por_instituicao AS
SELECT
    i.id AS id_instituicao,
    i.nome AS nome_instituicao,
    i.area,
    c.id AS id_campanha,
    c.nome AS nome_campanha,
    c.descricao,
    c.status,
    c.vagas,
    c.endereco,
    c.horario,
    c.disponibilidade
FROM instituicoes i
INNER JOIN campanhas c
ON i.id = c.id_instituicao;

CREATE OR REPLACE VIEW inscricoes_por_voluntario AS
SELECT
    v.id AS id_voluntario,
    v.nome AS nome_voluntario,
    v.email,
    c.id AS id_campanha,
    c.nome AS nome_campanha,
    ins.id AS id_inscricao,
    ins.status,
    ins.horas
FROM voluntarios v
INNER JOIN inscricoes ins
ON v.id = ins.id_voluntario
INNER JOIN campanhas c
ON ins.id_campanha = c.id;

CREATE OR REPLACE VIEW voluntarios_por_campanha AS
SELECT
    c.id AS id_campanha,
    c.nome AS nome_campanha,
    c.status AS status_campanha,
    i.nome AS nome_instituicao,
    v.id AS id_voluntario,
    v.nome AS nome_voluntario,
    v.email,
    v.area_atuacao,
    v.disponibilidade,
    ins.id AS id_inscricao,
    ins.status AS status_inscricao,
    ins.horas
FROM campanhas c
INNER JOIN instituicoes i
ON c.id_instituicao = i.id
INNER JOIN inscricoes ins
ON c.id = ins.id_campanha
INNER JOIN voluntarios v
ON ins.id_voluntario = v.id;


SELECT * FROM instituicoes;
SELECT * FROM voluntarios;
SELECT * FROM campanhas;
SELECT * FROM inscricoes;

SELECT * FROM enderecos_por_voluntario;
SELECT * FROM campanhas_por_instituicao;
SELECT * FROM inscricoes_por_voluntario;
SELECT * FROM voluntarios_por_campanha;
