<?php
include 'conecta_mysql.php';

// Define o período padrão
$data_inicial = $_GET['inicio'] ?? '2025-06-01';
$data_final   = $_GET['fim'] ?? '2025-06-30';

// Consulta SQL — AQI ≥ 4
$sql = "SELECT 
          CONCAT(dataleitura, ' ', horaleitura) AS datahora_completa,
          aqi
        FROM leituraptqa
        WHERE dataleitura BETWEEN :inicio AND :fim
          AND aqi >= 4
        ORDER BY dataleitura, horaleitura ASC";

$stmt = $conecta->prepare($sql);
$stmt->execute([':inicio' => $data_inicial, ':fim' => $data_final]);
$resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Retorno JSON (para o gráfico)
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
  <title>Consulta de Registros de Baixa Qualidade do Ar (AQI ≥ 4) - PTQA</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <!-- Navbar -->
  <div class="navbar">
    <div class="logo">
      <img src="logo.png" alt="Logo PTQA">
    </div>
    <ul>
      <li><a href="index.php">Início</a></li>
      <li><a href="ptqa_aqi.php">Gráficos</a></li>
      <li><a href="#">Sobre</a></li>
      <li><a href="#">Contato</a></li>
    </ul>
  </div>

  <!-- Seção principal -->
  <section class="grafico-section">
    <h1>Registros de Baixa Qualidade do Ar (AQI ≥ 4)</h1>

    <!-- Filtro -->
    <form method="get">
      <label>Data inicial:</label>
      <input type="date" name="inicio" value="<?php echo $data_inicial; ?>">
      <label>Data final:</label>
      <input type="date" name="fim" value="<?php echo $data_final; ?>">
      <button type="submit">Filtrar</button>
    </form>

    <div class="loading" id="loading">Carregando dados...</div>

    <!-- Gráfico -->
    <canvas id="graficoAqi"></canvas>
  </section>

  <!-- Script externo -->
  <script src="aqi.js"></script>
  <script>
    // Passa as datas PHP para o JS
    const dataInicial = "<?php echo $data_inicial; ?>";
    const dataFinal = "<?php echo $data_final; ?>";
  </script>
</body>
</html>
