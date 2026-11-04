-- voluntarios.sql

USE volunteerconnect;

CREATE VIEW enderecos_por_voluntario AS
SELECT
    v.id AS id_voluntario,
    v.nome AS nome_voluntario,
    v.email,
    v.endereco
FROM voluntarios v
ORDER BY v.nome;

CREATE VIEW campanhas_por_instituicao AS
SELECT
    i.id AS id_instituicao,
    i.nome AS nome_instituicao,
    i.area,
    c.id AS id_campanha,
    c.nome AS nome_campanha,
    c.descricao,
    c.status,
    c.vagas
FROM instituicoes i
INNER JOIN campanhas c
    ON i.id = c.id_instituicao
ORDER BY i.nome, c.nome;

CREATE VIEW inscricoes_por_voluntario AS
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
    ON ins.id_campanha = c.id
ORDER BY v.nome, c.nome;


CREATE VIEW voluntarios_por_campanha AS
SELECT
    c.id AS id_campanha,
    c.nome AS nome_campanha,
    c.status AS status_campanha,
    i.nome AS nome_instituicao,
    v.id AS id_voluntario,
    v.nome AS nome_voluntario,
    v.email,
    ins.id AS id_inscricao,
    ins.status AS status_inscricao,
    ins.horas
FROM campanhas c
INNER JOIN instituicoes i
    ON c.id_instituicao = i.id
INNER JOIN inscricoes ins
    ON c.id = ins.id_campanha
INNER JOIN voluntarios v
    ON ins.id_voluntario = v.id
ORDER BY c.nome, v.nome;