CREATE DATABASE SOLARCONTROL;



## TABELA DE USUÁRIOS

CREATE TABLE usuarios(

ID_USUARIO INT PRIMARY KEY AUTO_INCREMENT,

nome VARCHAR(100),

email VARCHAR(100) UNIQUE,

senha VARCHAR(20),

id_endereco int,

TIPO ENUM("cliente", "funcionario", "adm") DEFAULT "cliente",

-- FK de endereco
FOREIGN KEY(id_endereco)

REFERENCES enderecos (id_endereco)


);


## TABELA DE ENDEREÇOS

CREATE TABLE enderecos(

ID_ENDERECO INT PRIMARY KEY AUTO_INCREMENT,

CEP VARCHAR(8),

estado VARCHAR(3),

cidade VARCHAR(50), 

logradouro VARCHAR(50),

numero VARCHAR(5)

); 



## TABELA DE SISTEMAS FOTOVOLTAICOS

CREATE TABLE fotovoltaicos(

ID_fotovoltaicos INT PRIMARY KEY AUTO_INCREMENT,

ID_USUARIO INT,

modelo_painel VARCHAR(100),

data_intalacao DATE,

status_sistema VARCHAR(20),

FOREIGN KEY(ID_USUARIO)

REFERENCES usuarios (ID_USUARIO)

);



## TABELA DE REGISTRO DE ENERGIA E CONSUMO

CREATE TABLE registros(

ID_REGISTRO INT PRIMARY KEY AUTO_INCREMENT,

ID_fotovoltaicos INT,

data_registro DATE,

energia_gerada INT,

energia_consumida INT,

FOREIGN KEY(ID_fotovoltaicos)

REFERENCES fotovoltaicos (ID_fotovoltaicos)

);



## TABELA DE MANUTENÇÃO E INSTALAÇÃO

CREATE TABLE manutencao_e_instalacao(

ID_MANUTENCAO INT PRIMARY KEY AUTO_INCREMENT,

ID_SISTEMAS INT,

data_agendada DATE,

tempo_estimado VARCHAR(50),

qualidade_sistema VARCHAR(100),

previsao_da_proxima_manutencao DATE,

FOREIGN KEY(ID_SISTEMAS)

REFERENCES sistemas_fotovoltaicos (ID_SISTEMAS)

); 


