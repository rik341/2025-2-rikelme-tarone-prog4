SELECT datahora, horaleitura, temperatura
FROM leituraptqa
WHERE datahora BETWEEN '2025-06-01' AND '2025-06-10'
ORDER BY datahora ASC;