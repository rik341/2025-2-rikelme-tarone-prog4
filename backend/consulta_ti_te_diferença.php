<?php
include 'conecta_mysql.php';

// Define o período padrão (caso o usuário não selecione)
$data_inicial = $_GET['inicio'] ?? '2025-06-01';
$data_final   = $_GET['fim'] ?? '2025-06-30';

// Consulta SQL — calcula a diferença média entre te e ti
$sql = "SELECT 
          AVG(te - ti) AS media_diferenca
        FROM leituramabel
        WHERE datainclusao BETWEEN :inicio AND :fim;";

$stmt = $conecta->prepare($sql);
$stmt->execute([':inicio' => $data_inicial, ':fim' => $data_final]);
$resultado = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Diferença Média entre Temperaturas - MABEL</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 40px; }
    table { border-collapse: collapse; width: 50%; margin-top: 20px; }
    th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
    th { background-color: #f2f2f2; }
  </style>
</head>
<body>
  <h2>Diferença Média entre Temperaturas Interna e Externa</h2>

  <!-- Filtro de data -->
  <form method="get">
    <label>Data inicial:</label>
    <input type="date" name="inicio" value="<?php echo $data_inicial; ?>">
    <label>Data final:</label>
    <input type="date" name="fim" value="<?php echo $data_final; ?>">
    <button type="submit">Filtrar</button>
  </form>

  <!-- Resultado -->
  <?php if ($resultado && $resultado['media_diferenca'] !== null): ?>
    <table>
      <tr>
        <th>Período</th>
        <th>Diferença Média (°C)</th>
      </tr>
      <tr>
        <td><?php echo htmlspecialchars($data_inicial . " a " . $data_final); ?></td>
        <td><?php echo number_format($resultado['media_diferenca'], 2, ',', '.'); ?></td>
      </tr>
    </table>
  <?php else: ?>
    <p>Nenhum registro encontrado no período selecionado.</p>
  <?php endif; ?>
</body>
</html>
