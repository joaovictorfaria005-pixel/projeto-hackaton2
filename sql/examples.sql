-- Exemplos de INSERT e SELECT

-- INSERT em alertas
INSERT INTO alertas (tipo, mensagem, criado_em)
VALUES ('Informativo', 'Verifique seu kit de emergência e plano familiar.', NOW());

-- SELECT simples
SELECT id, tipo, mensagem, criado_em FROM alertas ORDER BY criado_em DESC;


