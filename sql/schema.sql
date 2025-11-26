-- Criação de schema (setup inicial)
CREATE DATABASE IF NOT EXISTS tornados;
USE tornados;

CREATE TABLE IF NOT EXISTS previsoes_tempo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cidade VARCHAR(100) NOT NULL,
    latitude DECIMAL(10,6) NOT NULL,
    longitude DECIMAL(10,6) NOT NULL,
    temperatura_c DECIMAL(5,2) NOT NULL,
    umidade INT NOT NULL,
    vento_kmh DECIMAL(5,2) NOT NULL,
    risco_tornado VARCHAR(50) NOT NULL,
    fonte VARCHAR(100) NOT NULL,
    criado_em DATETIME NOT NULL
);

CREATE TABLE IF NOT EXISTS tornados_ativos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    latitude DECIMAL(10,6) NOT NULL,
    longitude DECIMAL(10,6) NOT NULL,
    severidade VARCHAR(50) NOT NULL,
    fonte VARCHAR(100) NOT NULL,
    observado_em DATETIME NOT NULL
);

CREATE TABLE IF NOT EXISTS alertas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo VARCHAR(100) NOT NULL,
    mensagem TEXT NOT NULL,
    criado_em DATETIME NOT NULL
);

CREATE TABLE usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  email VARCHAR(100) UNIQUE NOT NULL,
  senha VARCHAR(100) NOT NULL,
  cidade VARCHAR(100) NOT NULL
);
