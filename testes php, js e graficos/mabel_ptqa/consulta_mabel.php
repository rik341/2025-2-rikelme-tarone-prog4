<?php
include 'conecta_mysql.php';

// Define o período padrão
$data_inicial = $_GET['inicio'] ?? '2025-06-01';
$data_final   = $_GET['fim'] ?? '2025-06-30';

// Consulta SQL
$sql = "SELECT datahora, ti
        FROM leituramabel
        WHERE datahora BETWEEN :inicio AND :fim
        ORDER BY datahora ASC";

$stmt = $conecta->prepare($sql);
$stmt->execute([':inicio' => $data_inicial, ':fim' => $data_final]);
$resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Temperatura Interna - MABEL</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 40px; }
    table { border-collapse: collapse; width: 60%; margin-top: 20px; }
    th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
    th { background-color: #f2f2f2; }
  </style>
</head>
<body>
  <h2>Temperaturas Internas (Campo: ti)</h2>

  <form method="get">
    <label>Data inicial:</label>
    <input type="date" name="inicio" value="<?php echo $data_inicial; ?>">
    <label>Data final:</label>
    <input type="date" name="fim" value="<?php echo $data_final; ?>">
    <button type="submit">Filtrar</button>
  </form>

  <table>
    <tr>
      <th>Data e Hora</th>
      <th>Temperatura Interna (°C)</th>
    </tr>
    <?php if ($resultado): ?>
      <?php foreach ($resultado as $linha): ?>
        <tr>
          <td><?php echo htmlspecialchars($linha['datahora']); ?></td>
          <td><?php echo htmlspecialchars($linha['ti']); ?></td>
        </tr>
      <?php endforeach; ?>
    <?php else: ?>
      <tr><td colspan="2">Nenhum dado encontrado.</td></tr>
    <?php endif; ?>
  </table>
</body>
</html>
