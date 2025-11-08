<?php
include 'conecta_mysql.php';

// Define o formato (html ou json)
$formato = $_GET['formato'] ?? 'html';

// Define o período (padrão)
$data_inicial = $_GET['inicio'] ?? '2025-06-01';
$data_final   = $_GET['fim'] ?? '2025-06-30';

// Consulta SQL — data, hora e temperatura em ordem crescente
$sql = "SELECT 
            datainclusao AS data,
            horainclusao AS hora,
            temperatura
        FROM leituraptqa
        WHERE datainclusao BETWEEN :inicio AND :fim
        ORDER BY datainclusao ASC, horainclusao ASC";

$stmt = $conecta->prepare($sql);
$stmt->execute([':inicio' => $data_inicial, ':fim' => $data_final]);
$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Se o formato for JSON, retorna só os dados
if ($formato === 'json') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($resultados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Consulta de Temperaturas - Projeto MABEL</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 40px; background-color: #f8f8f8; }
    h2 { color: #333; }
    table { border-collapse: collapse; width: 80%; background-color: #fff; }
    th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
    th { background-color: #eee; }
    form { margin-bottom: 20px; }
  </style>
</head>
<body>

  <h2>Temperaturas registradas por data e hora</h2>

  <form method="get">
    <label>Data inicial:</label>
    <input type="date" name="inicio" value="<?php echo htmlspecialchars($data_inicial ?? '', ENT_QUOTES); ?>">
    <label>Data final:</label>
    <input type="date" name="fim" value="<?php echo htmlspecialchars($data_final ?? '', ENT_QUOTES); ?>">
    <button type="submit">Filtrar</button>
  </form>

  <?php if ($resultados): ?>
    <table>
      <tr>
        <th>Data</th>
        <th>Hora</th>
        <th>Temperatura (°C)</th>
      </tr>
      <?php foreach ($resultados as $linha): ?>
        <tr>
          <td><?php echo htmlspecialchars($linha['data']); ?></td>
          <td><?php echo htmlspecialchars($linha['hora']); ?></td>
          <td><?php echo htmlspecialchars($linha['temperatura']); ?></td>
        </tr>
      <?php endforeach; ?>
    </table>
  <?php else: ?>
    <p>Nenhum registro encontrado no período informado.</p>
  <?php endif; ?>

  <p><a href="?inicio=<?php echo $data_inicial; ?>&fim=<?php echo $data_final; ?>&formato=json">Ver em JSON</a></p>

</body>
</html>
