<?php
include 'conecta_mysql.php';

// Define período padrão
$data_inicial = $_GET['inicio'] ?? '2025-06-01';
$data_final   = $_GET['fim'] ?? '2025-06-30';

/* ==============================
   1. Pressão < 1000 hPa
   ============================== */
$sql1 = "SELECT CONCAT(dataleitura, ' ', horaleitura) AS datahora_completa, pressao
         FROM leituraptqa
         WHERE dataleitura BETWEEN :inicio AND :fim
           AND pressao < 1000
         ORDER BY dataleitura, horaleitura ASC";
$stmt1 = $conecta->prepare($sql1);
$stmt1->execute([':inicio' => $data_inicial, ':fim' => $data_final]);
$resultado1 = $stmt1->fetchAll(PDO::FETCH_ASSOC);

/* ==============================
   2. Pressão mínima diária
   ============================== */
$sql2 = "SELECT dataleitura, MIN(pressao) AS pressao_minima
         FROM leituraptqa
         WHERE dataleitura BETWEEN :inicio AND :fim
         GROUP BY dataleitura
         ORDER BY dataleitura ASC";
$stmt2 = $conecta->prepare($sql2);
$stmt2->execute([':inicio' => $data_inicial, ':fim' => $data_final]);
$resultado2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);

/* ==============================
   3. JSON para gráficos
   ============================== */
if (isset($_GET['formato']) && $_GET['formato'] === 'json') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'pressao_baixa' => $resultado1,
        'pressao_minima' => $resultado2
    ]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Gráficos de Pressão - PTQA</title>
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

<h1>Gráficos de Pressão Atmosférica</h1>

<!-- Filtro -->
<form method="get">
    <label>Data inicial:</label>
    <input type="date" name="inicio" value="<?php echo $data_inicial; ?>">
    <label>Data final:</label>
    <input type="date" name="fim" value="<?php echo $data_final; ?>">
    <button type="submit">Filtrar</button>
</form>

<div id="loading">Carregando dados...</div>

<canvas id="graficoPressaoBaixa" width="800" height="400"></canvas>
<canvas id="graficoPressaoMinima" width="800" height="400" style="margin-top: 50px;"></canvas>

<!-- Passa as datas para JS -->
<script>
  const dataInicial = "<?php echo $data_inicial; ?>";
  const dataFinal = "<?php echo $data_final; ?>";
</script>
<script src="pressao.js"></script>

</body>
</html>
