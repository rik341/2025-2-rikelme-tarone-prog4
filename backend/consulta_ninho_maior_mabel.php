<?php
include 'conecta_mysql.php';

// Define o período padrão (pode ser alterado no formulário)
$data_inicial = $_GET['inicio'] ?? '2025-06-01';
$data_final   = $_GET['fim'] ?? '2025-06-30';

// Consulta SQL — obtém o valor máximo da temperatura interna no intervalo
$sql = "SELECT 
          MAX(ninho) AS temperatura_maxima
        FROM leituramabel
        WHERE datainclusao BETWEEN :inicio AND :fim";

$stmt = $conecta->prepare($sql);
$stmt->execute([':inicio' => $data_inicial, ':fim' => $data_final]);
$resultado = $stmt->fetch(PDO::FETCH_ASSOC);

$maxima = $resultado['temperatura_maxima'] ?? null;
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Temperatura Máxima do Ninho - MABEL</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 40px; background-color: #fafafa; }
    form { margin-bottom: 20px; }
    label { margin-right: 10px; }
    input[type="date"] { margin-right: 10px; }
    .resultado {
      background-color: #ffe;
      padding: 20px;
      border-radius: 8px;
      display: inline-block;
      margin-top: 10px;
      border: 1px solid #ccc;
    }
  </style>
</head>
<body>
  <h2>Temperatura Máxima do Ninho (Campo: <code>ninho</code>)</h2>

  <!-- Filtro de período -->
  <form method="get">
    <label>Data inicial:</label>
    <input type="date" name="inicio" value="<?php echo htmlspecialchars($data_inicial); ?>">
    <label>Data final:</label>
    <input type="date" name="fim" value="<?php echo htmlspecialchars($data_final); ?>">
    <button type="submit">Consultar</button>
  </form>

  <?php if ($maxima !== null): ?>
    <div class="resultado">
      <strong>Período:</strong> <?php echo htmlspecialchars($data_inicial); ?> a <?php echo htmlspecialchars($data_final); ?><br>
      <strong>Temperatura Máxima do Ninho:</strong> <?php echo htmlspecialchars($maxima); ?> °C
    </div>
  <?php else: ?>
    <p>Nenhum registro encontrado para o período selecionado.</p>
  <?php endif; ?>
</body>
</html>