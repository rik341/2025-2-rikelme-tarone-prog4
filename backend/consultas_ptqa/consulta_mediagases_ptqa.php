<?php
include 'conecta_mysql.php';

// Define o período (com valores padrão)
$data_inicial = $_GET['inicio'] ?? '2025-06-01';
$data_final   = $_GET['fim'] ?? '2025-06-30';

// Consulta SQL — média de gases voláteis (TVOC) agrupada pelo índice de qualidade do ar (AQI)
$sql = "SELECT 
          aqi,
          AVG(tvoc) AS media_tvoc
        FROM leituraptqa
        WHERE dataleitura BETWEEN :inicio AND :fim
          AND tvoc IS NOT NULL
        GROUP BY aqi
        ORDER BY aqi ASC";

$stmt = $conecta->prepare($sql);
$stmt->execute([':inicio' => $data_inicial, ':fim' => $data_final]);
$resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Caso o usuário queira o resultado em JSON (para gráfico futuramente)
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
  <title>Média de gases voláteis por índice de qualidade do ar </title>
  <style>
    body { font-family: Arial, sans-serif; margin: 40px; }
    table { border-collapse: collapse; width: 60%; margin-top: 20px; }
    th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
    th { background-color: #f2f2f2; }
  </style>
</head>
<body>
  <h2>Média de gases voláteis agrupada por índice de qualidade do ar</h2>

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
      <th>Índice de Qualidade do Ar</th>
      <th>Média de Gases Voláteis</th>
    </tr>

    <?php if (count($resultado) > 0): ?>
      <?php foreach ($resultado as $linha): ?>
        <tr>
          <td><?php echo htmlspecialchars($linha['aqi']); ?></td>
          <td><?php echo number_format($linha['media_tvoc'], 2, ',', '.'); ?></td>
        </tr>
      <?php endforeach; ?>
    <?php else: ?>
      <tr><td colspan="2">Nenhum registro encontrado no período selecionado.</td></tr>
    <?php endif; ?>
  </table>
</body>
</html>
