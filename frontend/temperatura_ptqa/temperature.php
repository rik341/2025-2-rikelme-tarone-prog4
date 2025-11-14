<?php
include 'conecta_mysql.php';

// Período padrão
$data_inicial = $_GET['inicio'] ?? '2025-06-01';
$data_final   = $_GET['fim'] ?? '2025-06-30';

/* =====================================================
   1) DATA, HORA E TEMPERATURA – ORDEM CRESCENTE
   ===================================================== */
$sql1 = "SELECT 
            dataleitura AS data,
            horaleitura AS hora,
            temperatura
         FROM leituraptqa
         WHERE dataleitura BETWEEN :inicio AND :fim
         ORDER BY dataleitura ASC, horaleitura ASC";

$stmt1 = $conecta->prepare($sql1);
$stmt1->execute([':inicio' => $data_inicial, ':fim' => $data_final]);
$lista_temperaturas = $stmt1->fetchAll(PDO::FETCH_ASSOC);

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
   JSON PARA OS GRÁFICOS
   ===================================================== */
if (isset($_GET['formato']) && $_GET['formato'] === 'json') {
    header('Content-Type: application/json; charset=utf-8');

    echo json_encode([
        'lista' => $lista_temperaturas,
        'media_periodo' => $media_resultado,
        'max_min_med' => $max_min_med
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Gráficos de Temperatura - PTQA</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link rel="stylesheet" href="style.css">
</head>
<body>

<h1>Gráficos de Temperatura</h1>

<form method="get">
    <label>Início:</label>
    <input type="date" name="inicio" value="<?= $data_inicial ?>">
    <label>Fim:</label>
    <input type="date" name="fim" value="<?= $data_final ?>">
    <button type="submit">Filtrar</button>
</form>

<div id="loading">Carregando dados...</div>

<h2>Temperatura ao longo do tempo</h2>
<canvas id="graficoTemperatura" height="400"></canvas>

<h2>Temperatura Média</h2>
<p id="valorMedia">Carregando...</p>

<h2>Máxima / Mínima / Média</h2>
<canvas id="graficoMaxMinMed" height="300"></canvas>

<script>
const dataInicial = "<?= $data_inicial ?>";
const dataFinal = "<?= $data_final ?>";
</script>

<script src="temperatura.js"></script>

</body>
</html>
