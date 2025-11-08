<?php
include 'conecta_mysql.php';

// Define o período padrão (pode ser alterado pelo formulário)
$data_inicial = $_GET['inicio'] ?? '2025-02-01';
$data_final   = $_GET['fim'] ?? '2025-02-28';

// Consulta SQL — obtém o valor máximo de CO₂ (eco2) e a data/hora correspondente
$sql = "SELECT 
          dataleitura,
          horaleitura,
          eco2
        FROM leituraptqa
        WHERE dataleitura BETWEEN :inicio AND :fim
        ORDER BY eco2 DESC
        LIMIT 1";

$stmt = $conecta->prepare($sql);
$stmt->execute([':inicio' => $data_inicial, ':fim' => $data_final]);
$resultado = $stmt->fetch(PDO::FETCH_ASSOC);

// Extrai dados, se existirem
$data_max = $resultado['dataleitura'] ?? null;
$hora_max = $resultado['horaleitura'] ?? null;
$co2_max  = $resultado['eco2'] ?? null;

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
  <title>Máxima Concentração de CO₂ - PTQA</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 40px; background-color: #fafafa; }
    form { margin-bottom: 20px; }
    label { margin-right: 10px; }
    input[type="date"] { margin-right: 10px; }
    .resultado {
      background-color: #f2f2f2;
      padding: 15px;
      border-radius: 8px;
      width: 400px;
      border: 1px solid #ccc;
    }
  </style>
</head>
<body>
  <h2>Máxima Concentração de CO₂ Registrada</h2>

  <!-- Filtro de período -->
  <form method="get">
    <label>Data inicial:</label>
    <input type="date" name="inicio" value="<?php echo htmlspecialchars($data_inicial); ?>">
    <label>Data final:</label>
    <input type="date" name="fim" value="<?php echo htmlspecialchars($data_final); ?>">
    <button type="submit">Consultar</button>
  </form>

  <!-- Resultado -->
  <?php if ($co2_max !== null): ?>
    <div class="resultado">
      <p><strong>Período:</strong> <?php echo htmlspecialchars($data_inicial); ?> a <?php echo htmlspecialchars($data_final); ?></p>
      <p><strong>Maior concentração de CO₂:</strong> <?php echo htmlspecialchars($co2_max); ?> ppm</p>
      <p><strong>Registrada em:</strong> <?php echo htmlspecialchars($data_max); ?> às <?php echo htmlspecialchars($hora_max); ?></p>
    </div>
  <?php else: ?>
    <p>Nenhum dado encontrado no período selecionado.</p>
  <?php endif; ?>
</body>
</html>
