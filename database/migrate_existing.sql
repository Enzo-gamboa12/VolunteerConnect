-- Faça BACKUP antes. Selecione explicitamente o banco existente no cliente MySQL.
-- Não troca senhas, não exclui registros, não recria tabelas e não importa exemplos.
-- Compatível com o esquema deste projeto. DDL MySQL não possui rollback transacional.
SET NAMES utf8mb4;
DELIMITER $$
DROP PROCEDURE IF EXISTS vc_migrate_20261127$$
CREATE PROCEDURE vc_migrate_20261127()
BEGIN
    IF DATABASE() IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Selecione o banco existente antes de migrar.';
    END IF;
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='campanhas' AND column_name='data_inicio') THEN
        ALTER TABLE campanhas ADD COLUMN data_inicio DATE NULL AFTER endereco;
    END IF;
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='campanhas' AND column_name='data_termino') THEN
        ALTER TABLE campanhas ADD COLUMN data_termino DATE NULL AFTER data_inicio;
    END IF;
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='campanhas' AND column_name='tipo') THEN
        ALTER TABLE campanhas ADD COLUMN tipo ENUM('Saúde','Tecnologia','Educação') NULL;
    END IF;
END$$
CALL vc_migrate_20261127()$$
DROP PROCEDURE vc_migrate_20261127$$
DELIMITER ;

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
    c.data_inicio,
    c.data_termino,
    c.descricao,
    c.status,
    c.vagas,
    c.vagasTotais,
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
    c.data_inicio,
    c.data_termino,
    c.tipo AS categoria,
    c.horario,
    c.endereco,
    i.id AS id_instituicao,
    i.nome AS nome_instituicao,
    ins.id AS id_inscricao,
    ins.status,
    ins.horas
FROM voluntarios v
INNER JOIN inscricoes ins
ON v.id = ins.id_voluntario
INNER JOIN campanhas c
ON ins.id_campanha = c.id
INNER JOIN instituicoes i
ON c.id_instituicao = i.id;

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

-- Tipos ausentes devem ser classificados pela instituição, não por inferência automática.
SELECT id, nome, tipo FROM campanhas WHERE tipo IS NULL OR tipo = '';
