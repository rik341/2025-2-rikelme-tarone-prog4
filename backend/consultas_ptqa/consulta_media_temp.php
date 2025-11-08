<?php
include 'conecta_mysql.php';

// Define o período padrão (pode ser alterado via GET)
$data_inicial = $_GET['inicio'] ?? '2025-02-01';
$data_final   = $_GET['fim'] ?? '2025-02-28';

// Consulta SQL para calcular a média da temperatura no período
$sql = "SELECT 
          AVG(temperatura) AS temperatura_media
        FROM leituraptqa
        WHERE dataleitura BETWEEN :inicio AND :fim";

$stmt = $conecta->prepare($sql);
$stmt->execute([':inicio' => $data_inicial, ':fim' => $data_final]);
$resultado = $stmt->fetch(PDO::FETCH_ASSOC);
$media = $resultado['temperatura_media'] ?? null;

// Retorno em JSON, se solicitado
if (isset($_GET['formato']) && $_GET['formato'] === 'json') {
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode([
    'data_inicial' => $data_inicial,
    'data_final' => $data_final,
    'temperatura_media' => round($media, 2)
  ]);
  exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Média da Temperatura - PTQA</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 40px; }
    form { margin-bottom: 20px; }
    .resultado {
      background-color: #f2f2f2;
      border-radius: 8px;
      padding: 15px;
      width: 300px;
    }
  </style>
</head>
<body>
  <h2>Temperatura Média Registrada</h2>

  <!-- Formulário de filtro -->
  <form method="get">
    <label>Data inicial:</label>
    <input type="date" name="inicio" value="<?php echo $data_inicial; ?>">
    <label>Data final:</label>
    <input type="date" name="fim" value="<?php echo $data_final; ?>">
    <button type="submit">Calcular</button>
  </form>

  <!-- Exibição do resultado -->
  <div class="resultado">
    <?php if ($media !== null): ?>
      <p><strong>Período:</strong> <?php echo htmlspecialchars($data_inicial); ?> a <?php echo htmlspecialchars($data_final); ?></p>
      <p><strong>Temperatura média:</strong> <?php echo round($media, 2); ?> °C</p>
    <?php else: ?>
      <p>Nenhum dado encontrado no período informado.</p>
    <?php endif; ?>
  </div>
</body>
</html>
