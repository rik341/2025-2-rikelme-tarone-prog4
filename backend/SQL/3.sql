SELECT *
FROM leituraptqa
WHERE umidade > 70
  AND datahora BETWEEN '2025-06-01' AND '2025-06-10'
ORDER BY umidade DESC;