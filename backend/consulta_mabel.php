<?php
include 'conecta_mysql.php';

// Define o período (com valores padrão)
$data_inicial = $_GET['inicio'] ?? '2025-06-01';
$data_final   = $_GET['fim'] ?? '2025-06-30';

// Consulta SQL — junta data e hora reais da inclusão
$sql = "SELECT 
          CONCAT(datainclusao, ' ', horainclusao) AS datahora_completa,
          hi, he, te, ti
        FROM leituramabel
        WHERE datainclusao BETWEEN :inicio AND :fim
        ORDER BY datainclusao, horainclusao ASC";

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
  <title>Consulta de Umidade Externa, Umidade interna, Temperatura interna e Temperatura externa - MABEL</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 40px; }
    table { border-collapse: collapse; width: 60%; margin-top: 20px; }
    th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
    th { background-color: #f2f2f2; }
  </style>
</head>
<body>
  <h2>Umidade Externa, Umidade interna, Temperatura interna e Temperatura externa (Campo: he, hi, ti, te)</h2>

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
      <th>Umidade Interna (%)</th>
    </tr>

    <?php if (count($resultado) > 0): ?>
      <?php foreach ($resultado as $linha): ?>
        <tr>
          <td><?php echo htmlspecialchars($linha['datahora_completa']); ?></td>
          <td><?php echo htmlspecialchars($linha['hi']); ?></td>
          <td><?php echo htmlspecialchars($linha['he']); ?></td>
          <td><?php echo htmlspecialchars($linha['te']); ?></td>
          <td><?php echo htmlspecialchars($linha['ti']); ?></td>

        </tr>
      <?php endforeach; ?>
    <?php else: ?>
      <tr><td colspan="2">Nenhum registro encontrado no período selecionado.</td></tr>
    <?php endif; ?>
  </table>
</body>
</html>