<?php
include 'conecta_mysql.php';

// Define o período padrão (caso o usuário não selecione)
$data_inicial = $_GET['inicio'] ?? '2025-06-01';
$data_final   = $_GET['fim'] ?? '2025-06-30';

// Consulta SQL — média diária da temperatura interna
$sql = "SELECT 
          datainclusao,
          ROUND(AVG(ti), 2) AS media_diaria_ti
        FROM leituramabel
        WHERE datainclusao BETWEEN :inicio AND :fim
        GROUP BY datainclusao
        ORDER BY datainclusao ASC;";

$stmt = $conecta->prepare($sql);
$stmt->execute([':inicio' => $data_inicial, ':fim' => $data_final]);
$resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Média Diária da Temperatura Interna - MABEL</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 40px; }
    table { border-collapse: collapse; width: 60%; margin-top: 20px; }
    th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
    th { background-color: #f2f2f2; }
  </style>
</head>
<body>
  <h2>Média Diária da Temperatura Interna (Campo: ti)</h2>

  <!-- Filtro de data -->
  <form method="get">
    <label>Data inicial:</label>
    <input type="date" name="inicio" value="<?php echo $data_inicial; ?>">
    <label>Data final:</label>
    <input type="date" name="fim" value="<?php echo $data_final; ?>">
    <button type="submit">Filtrar</button>
  </form>

  <!-- Tabela de resultados -->
  <table>
    <tr>
      <th>Data</th>
      <th>Média Diária (°C)</th>
    </tr>

    <?php if ($resultado && count($resultado) > 0): ?>
      <?php foreach ($resultado as $linha): ?>
        <tr>
          <td><?php echo htmlspecialchars($linha['datainclusao']); ?></td>
          <td><?php echo htmlspecialchars($linha['media_diaria_ti']); ?></td>
        </tr>
      <?php endforeach; ?>
    <?php else: ?>
      <tr><td colspan="2">Nenhum registro encontrado no período selecionado.</td></tr>
    <?php endif; ?>
  </table>
</body>
</html>
