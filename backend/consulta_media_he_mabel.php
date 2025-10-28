<?php
include 'conecta_mysql.php';

// Define o período (com valores padrão)
$data_inicial = $_GET['inicio'] ?? '2025-06-01';
$data_final   = $_GET['fim'] ?? '2025-06-30';

// Consulta SQL — calcula a média da umidade externa no intervalo
$sql = "SELECT 
          ROUND(AVG(he), 2) AS media_umidade_externa
        FROM leituramabel
        WHERE datainclusao BETWEEN :inicio AND :fim";

$stmt = $conecta->prepare($sql);
$stmt->execute([':inicio' => $data_inicial, ':fim' => $data_final]);
$resultado = $stmt->fetch(PDO::FETCH_ASSOC);

$media = $resultado['media_umidade_externa'] ?? null;
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Média da umidade externa da Colmeia - MABEL</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 40px; background-color: #fafafa; }
    form { margin-bottom: 20px; }
    label { margin-right: 10px; }
    input[type="date"] { margin-right: 10px; }
    .resultado {
      background-color: #eef;
      padding: 20px;
      border-radius: 8px;
      display: inline-block;
      margin-top: 10px;
    }
  </style>
</head>
<body>
  <h2>Média da umidade externa da Colmeia (Campo: <code>he</code>)</h2>

  <!-- Filtro de período -->
  <form method="get">
    <label>Data inicial:</label>
    <input type="date" name="inicio" value="<?php echo htmlspecialchars($data_inicial); ?>">
    <label>Data final:</label>
    <input type="date" name="fim" value="<?php echo htmlspecialchars($data_final); ?>">
    <button type="submit">Calcular Média</button>
  </form>

  <?php if ($media !== null): ?>
    <div class="resultado">
      <strong>Período:</strong> <?php echo htmlspecialchars($data_inicial); ?> a <?php echo htmlspecialchars($data_final); ?><br>
      <strong>Umidade externa Média:</strong> <?php echo htmlspecialchars($media); ?> °C
    </div>
  <?php else: ?>
    <p>Nenhum dado encontrado para o período selecionado.</p>
  <?php endif; ?>
</body>
</html>
