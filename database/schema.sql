CREATE DATABASE volunteerconnect;

USE volunteerconnect;


CREATE TABLE instituicoes (
    id INT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    area ENUM('saude', 'tecnologia', 'educacao') NOT NULL,
    endereco VARCHAR(200) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE
);


CREATE TABLE voluntarios (
    id INT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    endereco VARCHAR(200) NOT NULL,
    especialidade VARCHAR(100),
    email VARCHAR(150) NOT NULL UNIQUE,
    disponibilidade VARCHAR(100)
);


CREATE TABLE campanhas (
    id INT PRIMARY KEY,
    id_instituicao INT NOT NULL,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    status ENUM('planejada', 'ativa', 'encerrada', 'cancelada') NOT NULL,
    vagas INT NOT NULL,

    FOREIGN KEY (id_instituicao)
    REFERENCES instituicoes(id)
);


CREATE TABLE inscricoes (
    id INT PRIMARY KEY,
    id_campanha INT NOT NULL,
    id_voluntario INT NOT NULL,
    status ENUM('aguardando', 'andamento', 'finalizado', 'cancelado') NOT NULL,
    horas INT DEFAULT 0,

    FOREIGN KEY (id_campanha)
    REFERENCES campanhas(id),

    FOREIGN KEY (id_voluntario)
    REFERENCES voluntarios(id),

    UNIQUE (id_campanha, id_voluntario)
);
