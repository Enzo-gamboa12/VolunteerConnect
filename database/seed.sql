-- seed.sql

USE volunteerconnect;

INSERT INTO instituicoes (id, nome, area, endereco, email) VALUES
(1, 'Instituto Saber Comunitário', 'educacao', 'Rua das Flores, 123 - Centro, São Paulo - SP', 'contato@sabercomunitario.org'),
(2, 'TechSocial Brasil', 'tecnologia', 'Av. Paulista, 1000, Cj 502 - Bela Vista, São Paulo - SP', 'suporte@techsocial.org.br'),
(3, 'Saúde & Vida Voluntários', 'saude', 'Rua Doutor Arnaldo, 450 - Pacaembu, São Paulo - SP', 'atendimento@saudeevida.org'),
(4, 'Escola Viva Periferia', 'educacao', 'Rua das Oliveiras, 88 - Heliópolis, São Paulo - SP', 'contato@escolaviva.org'),
(5, 'Inclusão Digital para Todos', 'tecnologia', 'Rua Vergueiro, 2030 - Vila Mariana, São Paulo - SP', 'projetos@inclusaodigital.org');

INSERT INTO voluntarios (id, nome, endereco, especialidade, email, disponibilidade) VALUES
(1, 'Ana Clara Silva', 'Rua Augusta, 500 - Consolação, São Paulo - SP', 'Pedagogia e Alfabetização', 'ana.silva@email.com', 'Finais de semana (Manhã)'),
(2, 'Carlos Eduardo Souza', 'Av. Brasil, 1200 - Jardins, São Paulo - SP', 'Desenvolvimento Web / HTML e CSS', 'carlos.souza@email.com', 'Terças e Quintas (Noite)'),
(3, 'Mariana Oliveira Santos', 'Rua Domingos de Morais, 300 - Vila Mariana, São Paulo - SP', 'Enfermagem e Primeiros Socorros', 'mariana.santos@email.com', 'Sábados (Integral)'),
(4, 'Lucas Gabriel Ferreira', 'Rua Clélia, 850 - Lapa, São Paulo - SP', 'Manutenção de Computadores e Redes', 'lucas.ferreira@email.com', 'Flexível'),
(5, 'Beatriz Lima Rocha', 'Av. Jabaquara, 1500 - Saúde, São Paulo - SP', 'Língua Portuguesa e Redação', 'beatriz.rocha@email.com', 'Segundas e Quartas (Tarde)'),
(6, 'Felipe Alves Costa', 'Rua Teodoro Sampaio, 900 - Pinheiros, São Paulo - SP', 'Medicina Geral / Triagem', 'felipe.costa@email.com', 'Domingos (Manhã)'),
(7, 'Juliana Mendes Pereira', 'Rua Voluntários da Pátria, 2100 - Santana, São Paulo - SP', 'Design Gráfico e UI/UX', 'juliana.mendes@email.com', 'Remoto - Finais de semana');

INSERT INTO campanhas (id, id_instituicao, nome, descricao, status, vagas) VALUES
(1, 1, 'Reforço Escolar de Matemática', 'Aulas de apoio para crianças do 5º ao 9º ano da rede pública.', 'ativa', 10),
(2, 2, 'Lógica de Programação para Jovens', 'Oficina introdutória de lógica e algoritmos para adolescentes.', 'ativa', 5),
(3, 3, 'Mutirão da Saúde Comunitária', 'Atendimento preventivo, aferição de pressão e exames básicos de glicemia.', 'ativa', 8),
(4, 4, 'Biblioteca Comunitária e Leitura', 'Organização do acervo de livros e mediação de leitura infantil.', 'planejada', 6),
(5, 5, 'Informática Básica para Idosos', 'Curso prático para ensinar idosos a utilizarem smartphones e computadores.', 'encerrada', 4),
(6, 3, 'Campanha de Doação de Sangue', 'Apoio logístico e triagem na campanha de conscientização sobre doação.', 'cancelada', 12);

INSERT INTO inscricoes (id, id_campanha, id_voluntario, status, horas) VALUES
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