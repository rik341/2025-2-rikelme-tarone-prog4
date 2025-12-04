<?php
include 'conecta_mysql.php';

// Período padrão
$data_inicial = $_GET['inicio'] ?? '2025-06-01';
$data_final   = $_GET['fim'] ?? '2025-06-30';
$intervalo    = $_GET['intervalo'] ?? 20;

/* =====================================================
   1) CO2 > 1000 ppm — DATA E HORA FORMATADAS
   ===================================================== */
$sql1 = "SELECT 
            DATE_FORMAT(dataleitura, '%d/%m/%Y') AS data,
            DATE_FORMAT(horaleitura, '%H:%i:%s') AS hora,
            eco2
         FROM leituraptqa
         WHERE dataleitura BETWEEN :inicio AND :fim
           AND eco2 > 1000
         ORDER BY dataleitura ASC, horaleitura ASC";

$stmt1 = $conecta->prepare($sql1);
$stmt1->execute([':inicio' => $data_inicial, ':fim' => $data_final]);
$co2_acima_1000 = $stmt1->fetchAll(PDO::FETCH_ASSOC);

/* =====================================================
   2) CO2 MÁXIMO NO PERÍODO
   ===================================================== */
$sql2 = "SELECT MAX(eco2) AS co2_maximo
         FROM leituraptqa
         WHERE dataleitura BETWEEN :inicio AND :fim";

$stmt2 = $conecta->prepare($sql2);
$stmt2->execute([':inicio' => $data_inicial, ':fim' => $data_final]);
$co2_maximo_periodo = $stmt2->fetch(PDO::FETCH_ASSOC);

/* =====================================================
   3) TOP 5 DIAS DO MÊS — MÉDIA DE CO2
   ===================================================== */
$mes = substr($data_inicial, 0, 7);

$sql3 = "SELECT 
            DATE_FORMAT(dataleitura, '%d/%m/%Y') AS dia,
            AVG(eco2) AS media_co2
         FROM leituraptqa
         WHERE dataleitura LIKE :mesFiltro
         GROUP BY dataleitura
         ORDER BY media_co2 DESC
         LIMIT 5";

$stmt3 = $conecta->prepare($sql3);
$stmt3->execute([':mesFiltro' => "$mes%"]);
$top5_medias_co2 = $stmt3->fetchAll(PDO::FETCH_ASSOC);

/* =====================================================
   JSON PARA O JAVASCRIPT
   ===================================================== */
if (isset($_GET['formato']) && $_GET['formato'] === 'json') {
    header('Content-Type: application/json; charset=utf-8');

    echo json_encode([
        'co2_acima_1000' => $co2_acima_1000,
        'co2_maximo_periodo' => $co2_maximo_periodo,
        'top5_medias_co2' => $top5_medias_co2
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    exit;
}
?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Gráficos de CO2 - PTQA</title>
<link rel="stylesheet" href="../../frontend/style.css">

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script defer src="co2.js"></script>
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
    form label {
        margin-right: 5px;
    }
    form input, form button {
        margin-right: 10px;
    }
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

<section class="content">
    <h1>Análises de CO₂</h1>

    <form id="formPeriodo">
        <label for="inicio">Início:</label>
        <input type="date" id="inicio" name="inicio" value="<?= $data_inicial ?>">

        <label for="fim">Fim:</label>
        <input type="date" id="fim" name="fim" value="<?= $data_final ?>">

        <label for="intervalo">Intervalo de leituras:</label>
        <input type="number" id="intervalo" name="intervalo" 
               value="<?= $intervalo ?>" min="1">

        <button type="submit">Filtrar</button>
    </form>

    <div class="loading" id="loading">Carregando dados...</div>

    <div id="mediaContainer" class="media-box">
        <strong>Maior concentração no período: </strong>
        <span id="co2Max">--</span>
    </div>

    <h2>Registros com CO₂ acima de 1000 ppm</h2>
    <canvas id="graficoAcima"></canvas>

    <h2>Top 5 dias do mês com maior média de CO₂</h2>
    <canvas id="graficoTop5"></canvas>
</section>

<script>
const dataInicial = "<?= $data_inicial ?>";
const dataFinal = "<?= $data_final ?>";
const intervalo = "<?= $intervalo ?>";

// Atualizar gráfico ao enviar o formulário
document.getElementById("formPeriodo").addEventListener("submit", e => {
    e.preventDefault();
    const inicio = document.getElementById("inicio").value;
    const fim = document.getElementById("fim").value;
    const intervalo = document.getElementById("intervalo").value;

    location.href = `?inicio=${inicio}&fim=${fim}&intervalo=${intervalo}`;
});
</script>

</body>
</html>
