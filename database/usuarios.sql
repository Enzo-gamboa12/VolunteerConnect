create view Fotovoltaicos_por_usuario as SELECT
    u.ID_USUARIO,
    u.nome AS nome_usuario,
    u.email,
    f.ID_fotovoltaicos,
    f.modelo_painel,
    f.data_intalacao,
    f.status_sistema
FROM usuarios u
INNER JOIN fotovoltaicos f
    ON u.ID_USUARIO = f.ID_USUARIO
ORDER BY u.nome;

create view enderecos_por_usuario as SELECT
    u.ID_USUARIO,
    u.nome AS nome_usuario,
    u.email,
    e.ID_ENDERECO,
    e.CEP,
    e.estado,
    e.cidade,
    e.logradouro,
    e.numero
FROM usuarios u
INNER JOIN enderecos e
    ON u.id_endereco = e.ID_ENDERECO
ORDER BY u.nome;

create view registros_por_usuario as SELECT
 u.ID_USUARIO,
    u.nome AS nome_usuario,
    f.ID_fotovoltaicos,
    f.modelo_painel,
    r.ID_REGISTRO,
    r.data_registro,
    r.energia_gerada,
    r.energia_consumida
FROM usuarios u
INNER JOIN fotovoltaicos f
    ON u.ID_USUARIO = f.ID_USUARIO
INNER JOIN registros r
    ON f.ID_fotovoltaicos = r.ID_fotovoltaicos
ORDER BY u.nome, r.data_registro;


create view manutencao_e_instalacao_por_usuario as SELECT
u.ID_USUARIO,
    u.nome AS nome_usuario,
    f.ID_fotovoltaicos,
    f.modelo_painel,
    m.ID_MANUTENCAO,
    m.data_agendada,
    m.tempo_estimado,
    m.qualidade_sistema,
    m.previsao_da_proxima_manutencao
FROM usuarios u
INNER JOIN fotovoltaicos f
    ON u.ID_USUARIO = f.ID_USUARIO
INNER JOIN manutencao_e_instalacao m
    ON f.ID_fotovoltaicos = m.ID_fotovoltaicos
ORDER BY u.nome, m.data_agendada;