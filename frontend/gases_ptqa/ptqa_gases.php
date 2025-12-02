<?php
// Conectar ao banco de dados
include 'conecta_mysql.php';

// Definir datas padrão
$data_inicial = $_GET['inicio'] ?? '2025-06-01';
$data_final   = $_GET['fim'] ?? '2025-06-30';

/* =====================================================
   1) TVOC > 200 ppb — DATA E HORA FORMATADAS
   ===================================================== */
$sql1 = "SELECT 
            CONCAT(
                DATE_FORMAT(dataleitura, '%d/%m/%Y'),
                ' ',
                DATE_FORMAT(horaleitura, '%H:%i:%s')
            ) AS datahora,
            tvoc
         FROM leituraptqa
         WHERE dataleitura BETWEEN :inicio AND :fim
           AND tvoc > 200
         ORDER BY dataleitura ASC, horaleitura ASC";

$stmt1 = $conecta->prepare($sql1);
$stmt1->execute([':inicio' => $data_inicial, ':fim' => $data_final]);
$resultado_gases = $stmt1->fetchAll(PDO::FETCH_ASSOC);

/* =====================================================
   2) MÉDIA TVOC AGRUPADA POR AQI
   ===================================================== */
$sql2 = "SELECT 
            aqi,
            AVG(tvoc) AS media_tvoc
         FROM leituraptqa
         WHERE dataleitura BETWEEN :inicio AND :fim
           AND tvoc IS NOT NULL
         GROUP BY aqi
         ORDER BY aqi ASC";

$stmt2 = $conecta->prepare($sql2);
$stmt2->execute([':inicio' => $data_inicial, ':fim' => $data_final]);
$resultado_media_aqi = $stmt2->fetchAll(PDO::FETCH_ASSOC);

/* =====================================================
   JSON PARA O JAVASCRIPT
   ===================================================== */
if (isset($_GET['formato']) && $_GET['formato'] === 'json') {
    header('Content-Type: application/json; charset=utf-8');

    echo json_encode([
        'gases_acima_200' => $resultado_gases,
        'media_aqi' => $resultado_media_aqi
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gráfico TVOC</title>
    <link rel="stylesheet" href="../../frontend/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body { font-family: Arial, sans-serif; }
        canvas { max-width: 100%; height: 400px; }
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

<section class="content">
    <h1>Gráfico de TVOC Acima de 200 ppb</h1>

    <form id="formPeriodo">
        <label>Data inicial:</label>
        <input type="date" name="inicio" value="<?= $data_inicial ?>">
        <label>Data final:</label>
        <input type="date" name="fim" value="<?= $data_final ?>">
        <button type="submit">Filtrar</button>
    </form>

    <h2>TVOC acima de 200 ppb</h2>
    <canvas id="graficoGasesAcima"></canvas>

    <h2>Média de TVOC agrupada por AQI</h2>
    <canvas id="graficoMediaAQI"></canvas>
</section>

<script>
const dataInicial = "<?= $data_inicial ?>";
const dataFinal   = "<?= $data_final ?>";

document.addEventListener("DOMContentLoaded", () => {

    fetch(`<?= $_SERVER['PHP_SELF'] ?>?inicio=${dataInicial}&fim=${dataFinal}&formato=json`)
        .then(r => r.json())
        .then(dados => {

            /* -------------------------------
               GRÁFICO 1 — TVOC > 200 ppb
               ------------------------------- */
            const labels1 = dados.gases_acima_200.map(d => d.datahora);
            const valores1 = dados.gases_acima_200.map(d => Number(d.tvoc));

            new Chart(document.getElementById("graficoGasesAcima"), {
                type: "line",
                data: {
                    labels: labels1,
                    datasets: [{
                        label: "TVOC > 200 ppb",
                        data: valores1,
                        borderColor: "red",
                        backgroundColor: "rgba(255, 0, 0, 0.2)",
                        fill: true,
                        tension: 0.3,
                        pointRadius: 3
                    }]
                }
            });

            /* -------------------------------
               GRÁFICO 2 — MÉDIA TVOC / AQI
               ------------------------------- */
            const labels2 = dados.media_aqi.map(d => "AQI " + d.aqi);
            const valores2 = dados.media_aqi.map(d => Number(d.media_tvoc));

            new Chart(document.getElementById("graficoMediaAQI"), {
                type: "bar",
                data: {
                    labels: labels2,
                    datasets: [{
                        label: "Média TVOC (ppb)",
                        data: valores2,
                        backgroundColor: "blue"
                    }]
                }
            });

        })
        .catch(() => alert("Erro ao carregar os dados."));
});
</script>

</body>
</html>
