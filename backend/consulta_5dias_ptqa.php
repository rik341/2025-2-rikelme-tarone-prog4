<?php
include 'conecta_mysql.php';

// Define o período (com valores padrão)
$data_inicial = $_GET['inicio'] ?? '2025-06-01';
$data_final   = $_GET['fim'] ?? '2025-06-30';

// Consulta SQL — busca a data e a temperatura interna
$sql = "SELECT 
          DATE(dataleitura) AS dia,
          AVG(eco2) AS media_eco2
        FROM leituraptqa
        WHERE dataleitura BETWEEN :inicio AND :fim
            AND eco2 >= 4
        GROUP BY dia
        ORDER BY media_eco2 DESC
        LIMIT 5";

$stmt = $conecta->prepare($sql);
$stmt->execute([':inicio' => $data_inicial, ':fim' => $data_final]);
$resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Se for pedido no formato JSON (para o gráfico no futuro)
if (isset($_GET['formato']) && $_GET['formato'] === 'json') {
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($resultado);
  exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>5 dias com maior média de concentração de gás carbônico (CO2) - PTQA</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 40px; }
    table { border-collapse: collapse; width: 60%; margin-top: 20px; }
    th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
    th { background-color: #f2f2f2; }
  </style>
</head>
<body>
  <h2>Registros dos 5 dias com maior média de concentração de gás carbônico (Campo: eco2)</h2>

  <!-- Filtro de data -->
  <form method="get">
    <label>Data inicial:</label>
    <input type="date" name="inicio" value="<?php echo $data_inicial; ?>">
    <label>Data final:</label>
    <input type="date" name="fim" value="<?php echo $data_final; ?>">
    <button type="submit">Filtrar</button>
  </form>

  <!-- Tabela com os resultados -->
  <table>
    <tr>
      <th>Data e Hora</th>
      <th>5 dias com maior média de concentração de gás carbônico (CO2) - PTQA</th>
    </tr>

    <?php if (count($resultado) > 0): ?>
      <?php foreach ($resultado as $linha): ?>
        <tr>
          <td><?php echo htmlspecialchars($linha['dia']); ?></td>
          <td><?php echo htmlspecialchars($linha['media_eco2']); ?></td>
        </tr>
      <?php endforeach; ?>
    <?php else: ?>
      <tr><td colspan="2">Nenhum registro encontrado no período selecionado.</td></tr>
    <?php endif; ?>
  </table>
</body>
</html>
