<?php
include 'conecta_mysql.php';

// Define o período padrão
$data_inicial = $_GET['inicio'] ?? '2025-02-01';
$data_final   = $_GET['fim'] ?? '2025-02-10';

// Consulta SQL — obtém a mínima pressão registrada por dia
$sql = "SELECT 
          dataleitura,
          MIN(pressao) AS pressao_minima
        FROM leituraptqa
        WHERE dataleitura BETWEEN :inicio AND :fim
        GROUP BY dataleitura
        ORDER BY dataleitura ASC";

$stmt = $conecta->prepare($sql);
$stmt->execute([':inicio' => $data_inicial, ':fim' => $data_final]);
$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Se o formato for JSON, retorna em JSON e encerra
if (isset($_GET['formato']) && $_GET['formato'] === 'json') {
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($resultados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
  exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Mínima Pressão Diária - PTQA</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 40px; background-color: #fafafa; }
    form { margin-bottom: 20px; }
    label { margin-right: 10px; }
    input[type="date"] { margin-right: 10px; }
    table { border-collapse: collapse; width: 400px; background-color: #fff; }
    th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
    th { background-color: #f0f0f0; }
  </style>
</head>
<body>
  <h2>Mínima Pressão Registrada por Dia</h2>

  <!-- Filtro de período -->
  <form method="get">
    <label>Data inicial:</label>
    <input type="date" name="inicio" value="<?php echo htmlspecialchars($data_inicial); ?>">
    <label>Data final:</label>
    <input type="date" name="fim" value="<?php echo htmlspecialchars($data_final); ?>">
    <button type="submit">Consultar</button>
  </form>

  <!-- Tabela de resultados -->
  <?php if (count($resultados) > 0): ?>
    <table>
      <tr>
        <th>Data</th>
        <th>Pressão Mínima</th>
      </tr>
      <?php foreach ($resultados as $linha): ?>
        <tr>
          <td><?php echo htmlspecialchars($linha['dataleitura'] ?? '-' ); ?></td>
          <td><?php echo htmlspecialchars($linha['pressao_minima'] ?? 'sem dados'); ?></td>
        </tr>
      <?php endforeach; ?>
    </table>
  <?php else: ?>
    <p>Nenhum registro encontrado para o período selecionado.</p>
  <?php endif; ?>
</body>
</html>
