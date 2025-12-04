<?php
include 'conecta_mysql.php';

// Período padrão
$data_inicial = $_GET['inicio'] ?? '2025-06-01';
$data_final   = $_GET['fim'] ?? '2025-06-30';

/* =====================================================
   1) DATA, HORA E TEMPERATURA – ORDEM CRESCENTE
   ===================================================== */
$sql1 = "SELECT 
            DATE_FORMAT(STR_TO_DATE(dataleitura, '%Y-%m-%d'), '%d/%m/%Y') AS data,
            DATE_FORMAT(STR_TO_DATE(horaleitura, '%H:%i:%s'), '%H:%i') AS hora,
            temperatura
         FROM leituraptqa
         WHERE dataleitura BETWEEN :inicio AND :fim
         ORDER BY dataleitura ASC, horaleitura ASC";

$stmt1 = $conecta->prepare($sql1);
$stmt1->execute([':inicio' => $data_inicial, ':fim' => $data_final]);
$lista_temperaturas = $stmt1->fetchAll(PDO::FETCH_ASSOC);

// Formatar as datas e horas para o formato brasileiro (DD/MM/AAAA HH:MM)
foreach ($lista_temperaturas as &$registro) {
    $registro['data'] = date('d/m/Y', strtotime($registro['data']));
    $registro['hora'] = date('H:i', strtotime($registro['hora']));
}

/* =====================================================
   2) TEMPERATURA MÉDIA
   ===================================================== */
$sql2 = "SELECT AVG(temperatura) AS temperatura_media
         FROM leituraptqa
         WHERE dataleitura BETWEEN :inicio AND :fim";

$stmt2 = $conecta->prepare($sql2);
$stmt2->execute([':inicio' => $data_inicial, ':fim' => $data_final]);
$media_resultado = $stmt2->fetch(PDO::FETCH_ASSOC);

/* =====================================================
   3) TEMPERATURA MÁXIMA, MÍNIMA E MÉDIA
   ===================================================== */
$sql3 = "SELECT 
            MAX(temperatura) AS temp_maxima,
            MIN(temperatura) AS temp_minima,
            AVG(temperatura) AS temp_media
         FROM leituraptqa
         WHERE dataleitura BETWEEN :inicio AND :fim";

$stmt3 = $conecta->prepare($sql3);
$stmt3->execute([':inicio' => $data_inicial, ':fim' => $data_final]);
$max_min_med = $stmt3->fetch(PDO::FETCH_ASSOC);

/* =====================================================
   4) UMIDADE > 70% – ORDEM DECRESCENTE
   ===================================================== */
$sql4 = "SELECT 
            DATE_FORMAT(STR_TO_DATE(dataleitura, '%Y-%m-%d'), '%d/%m/%Y') AS data,
            DATE_FORMAT(STR_TO_DATE(horaleitura, '%H:%i:%s'), '%H:%i') AS hora,
            umidade
         FROM leituraptqa
         WHERE dataleitura BETWEEN :inicio AND :fim
           AND umidade > 70
         ORDER BY umidade DESC";

$stmt4 = $conecta->prepare($sql4);
$stmt4->execute([':inicio' => $data_inicial, ':fim' => $data_final]);
$umidades_altas = $stmt4->fetchAll(PDO::FETCH_ASSOC);

// Formatar as datas e horas para o formato brasileiro (DD/MM/AAAA HH:MM)
foreach ($umidades_altas as &$registro) {
    $registro['data'] = date('d/m/Y', strtotime($registro['data']));
    $registro['hora'] = date('H:i', strtotime($registro['hora']));
}

/* =====================================================
   JSON PARA O JS
   ===================================================== */
if (isset($_GET['formato']) && $_GET['formato'] === 'json') {
    header('Content-Type: application/json; charset=utf-8');

    echo json_encode([
        'lista' => $lista_temperaturas,
        'media_periodo' => $media_resultado,
        'max_min_med' => $max_min_med,
        'umidade_alta' => $umidades_altas
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Gráficos de Temperatura - PTQA</title>
<link rel="stylesheet" href="../../frontend/style.css">

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- VARIÁVEIS PHP -> JS -->
<script>
    const dataInicial = "<?= $data_inicial ?>";
    const dataFinal   = "<?= $data_final ?>";
</script>

<script defer src="./temperatura.js"></script>

<style>
    body { font-family: Arial, sans-serif; }
    canvas { max-width: 100%; }
</style>
</head>

<body>

<header>
    <nav class="navbar">
        <div class="logo">IFSC <span>Chapecó</span></div>
        <ul class="nav-links">
            <li><a href="../temperatura_interna_mabel/temp_interna.php">Mabel</a></li>
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

<div class="content">
    <h1>Gráficos de Temperatura</h1>
    <form id="formPeriodo">
        <label>Início:</label>
        <input type="date" name="inicio" value="<?= $data_inicial ?>">
        <label>Fim:</label>
        <input type="date" name="fim" value="<?= $data_final ?>">
      <label>Intervalo:</label>
<input type="number" id="intervalo" name="intervalo" value="<?= $_GET['intervalo'] ?? 20 ?>" min="1">


        <button type="submit">Filtrar</button>
    </form>

    <div id="loading">Carregando dados...</div>

    <div id="mediaContainer" class="media-box">
            <strong>temperatura média</strong> 
            <span id="valorMedia">--</span> °C
        </div>

    <h2>Temperatura ao longo do tempo</h2>
    <canvas id="graficoTemperatura" height="400"></canvas>

    <h2>Máxima / Mínima / Média</h2>
    <canvas id="graficoMaxMinMed" height="300"></canvas>

    <h2>Registros com Umidade acima de 70%</h2>
    <canvas id="graficoUmidade" height="300"></canvas>
</div>

</body>
</html>
