<?php
include 'conecta_mysql.php';

// Define o período padrão
$data_inicial = $_GET['inicio'] ?? '2025-06-01';
$data_final   = $_GET['fim'] ?? '2025-06-30';

// ---------------------------
// CONSULTA 1 — AQI ≥ 4
// ---------------------------
$sql = "SELECT 
          CONCAT(dataleitura, ' ', horaleitura) AS datahora_completa,
          aqi
        FROM leituraptqa
        WHERE dataleitura BETWEEN :inicio AND :fim
          AND aqi >= 4
        ORDER BY dataleitura, horaleitura ASC";

$stmt = $conecta->prepare($sql);
$stmt->execute([':inicio' => $data_inicial, ':fim' => $data_final]);
$resultado_ruim = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ---------------------------
// CONSULTA 2 — AQI = 1 (ÓTIMO)
// ---------------------------
$sql2 = "SELECT 
           dataleitura,
           horaleitura,
           aqi
         FROM leituraptqa
         WHERE dataleitura BETWEEN :inicio AND :fim
           AND aqi = 1
         ORDER BY dataleitura, horaleitura ASC";

$stmt2 = $conecta->prepare($sql2);
$stmt2->execute([':inicio' => $data_inicial, ':fim' => $data_final]);
$resultado_otimo = $stmt2->fetchAll(PDO::FETCH_ASSOC);

// Retorno JSON (somente para o gráfico AQI ≥ 4)
if (isset($_GET['formato']) && $_GET['formato'] === 'json') {
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode([
    "ruim" => $resultado_ruim
  ]);
  exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Consulta de Qualidade do Ar - PTQA</title>
  <link rel="stylesheet" href="../../frontend/style_mabel.css">
    
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script defer src="./script.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .grafico-section {
            width: 80%;
            margin: 0 auto;
            text-align: center;
        }
        canvas {
            max-width: 100%;
            height: 400px;
        }
    </style>
</head>

<body>
    <header>
        <nav class="navbar">
            <div class="logo">IFSC <span>Chapecó</span></div>
            <ul class="nav-links">
                <li><a href="../index.html">Início</a></li>
            </ul>
        </nav>
    </header>   

    <div class="sidebar">
        <h2>Menu</h2>
        <a href="../aqi_ptqa/ptqa_aqi.php">Qualidade do ar</a>
        <a href="../co2_ptqa/co2.php">Emissões de CO2</a>
        <a href="../gases_ptqa/ptqa_gases.php">Gases Voláteis</a>
        <a href="../pressao_ptqa/pressao_ptqa.php">Pressão atmosférica</a>
        <a href="../temperatura_ptqa/temperature.php">Temperatura e umidade</a>
    </div>

  <section class="grafico-section">
    <h1>Registros de Qualidade do Ar</h1>

    <!-- Filtro -->
    <form method="get">
      <label>Data inicial:</label>
      <input type="date" name="inicio" value="<?php echo $data_inicial; ?>">
      <label>Data final:</label>
      <input type="date" name="fim" value="<?php echo $data_final; ?>">
      <button type="submit">Filtrar</button>
    </form>

    <div class="loading" id="loading">Carregando dados...</div>

    <!-- GRÁFICO AQI ≥ 4 -->
    <h2>Registros de Baixa Qualidade do Ar (AQI ≥ 4)</h2>
    <canvas id="graficoAqi"></canvas>

    <!-- TABELA AQI = 1 -->
    <h2>Registros de Ótima Qualidade do Ar (AQI = 1)</h2>

    <?php if (count($resultado_otimo) === 0): ?>

        <p style="color:green;">Nenhum registro AQI = 1 encontrado no período.</p>

    <?php else: ?>

    <table>
        <thead>
            <tr>
                <th>Data</th>
                <th>Hora</th>
                <th>AQI</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($resultado_otimo as $linha): ?>
              <tr>
                <td><?php echo $linha['dataleitura']; ?></td>
                <td><?php echo $linha['horaleitura']; ?></td>
                <td><?php echo $linha['aqi']; ?></td>
              </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php endif; ?>

  </section>

  <script src="aqi.js"></script>
  <script>
    const dataInicial = "<?php echo $data_inicial; ?>";
    const dataFinal   = "<?php echo $data_final; ?>";
  </script>

</body>
</html>
