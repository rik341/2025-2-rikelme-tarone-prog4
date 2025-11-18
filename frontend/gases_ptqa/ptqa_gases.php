<?php
// Conectar ao banco de dados
include 'conecta_mysql.php';  // Substitua pelo arquivo de conexão real

// Definir as variáveis para as datas
$data_inicial = $_GET['inicio'] ?? '2025-06-01';
$data_final = $_GET['fim'] ?? '2025-06-30';

// Realiza a consulta para TVOC > 200 ppb
$sql1 = "SELECT 
            CONCAT(dataleitura, ' ', horaleitura) AS datahora,
            tvoc
         FROM leituraptqa
         WHERE dataleitura BETWEEN :inicio AND :fim
           AND tvoc > 200
         ORDER BY dataleitura, horaleitura ASC";

$stmt1 = $conecta->prepare($sql1);
$stmt1->execute([':inicio' => $data_inicial, ':fim' => $data_final]);
$resultado_gases = $stmt1->fetchAll(PDO::FETCH_ASSOC);

// Realiza a consulta para a média de TVOC agrupada por AQI
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

// Se a consulta retornar os dados corretamente, transmitimos para o JavaScript em formato JSON
if (isset($_GET['formato']) && $_GET['formato'] === 'json') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'gases_acima_200' => $resultado_gases,
        'media_aqi' => $resultado_media_aqi
    ]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gráfico TVOC</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

    <!-- Seção do gráfico -->
    <section class="grafico-section">
        <h1>Gráfico de TVOC Acima de 200 ppb</h1>

        <!-- Filtro de datas -->
        <form method="get">
            <label>Data inicial:</label>
            <input type="date" name="inicio" value="<?php echo $data_inicial; ?>">
            <label>Data final:</label>
            <input type="date" name="fim" value="<?php echo $data_final; ?>">
            <button type="submit">Filtrar</button>
        </form>

        <!-- Exibe o gráfico -->
        <h2>TVOC acima de 200 ppb</h2>
        <canvas id="graficoGasesAcima"></canvas>

        <!-- Gráfico para Média por AQI -->
        <h2>Média de TVOC agrupada por AQI</h2>
        <canvas id="graficoMediaAQI"></canvas>

    </section>

    <script>
        // Passando as variáveis PHP para o JavaScript corretamente
        const dataInicial = "<?php echo $data_inicial; ?>";
        const dataFinal = "<?php echo $data_final; ?>";

        // Função que irá buscar os dados do PHP e criar o gráfico
        window.addEventListener("DOMContentLoaded", () => {
            const loading = document.getElementById("loading");

            // Define a URL para obter os dados em JSON
            fetch(`<?php echo $_SERVER['PHP_SELF']; ?>?inicio=${dataInicial}&fim=${dataFinal}&formato=json`)
                .then(res => res.json())
                .then(dados => {
                    // Caso os dados existam
                    if (!dados.gases_acima_200 || !dados.media_aqi) {
                        alert("Nenhum dado encontrado para o período informado.");
                        return;
                    }

                    // -------------------------------
                    // GRÁFICO 1 — TVOC ACIMA DE 200 ppb
                    // -------------------------------
                    const labels1 = dados.gases_acima_200.map(d => d.datahora);
                    const valores1 = dados.gases_acima_200.map(d => parseFloat(d.tvoc));

                    const canvas1 = document.getElementById("graficoGasesAcima");
                    const ctx1 = canvas1.getContext("2d");

                    new Chart(ctx1, {
                        type: "line",
                        data: {
                            labels: labels1,
                            datasets: [{
                                label: "TVOC > 200 ppb",
                                data: valores1,
                                borderColor: "rgba(255, 99, 132, 1)",
                                backgroundColor: "rgba(255, 99, 132, 0.2)",
                                fill: true,
                                tension: 0.3,
                                pointRadius: 3
                            }]
                        },
                        options: {
                            responsive: true,
                            scales: {
                                x: {
                                    title: { display: true, text: "Data e Hora" },
                                    ticks: { autoSkip: true, maxTicksLimit: 10 }
                                },
                                y: {
                                    title: { display: true, text: "TVOC (ppb)" },
                                    beginAtZero: true
                                }
                            },
                            plugins: {
                                legend: { display: true, position: "top" },
                                title: { display: true, text: "TVOC Acima de 200 ppb" }
                            }
                        }
                    });

                    // -------------------------------
                    // GRÁFICO 2 — MÉDIA DE TVOC AGRUPADA POR AQI
                    // -------------------------------
                    const labels2 = dados.media_aqi.map(d => "AQI " + d.aqi);
                    const valores2 = dados.media_aqi.map(d => parseFloat(d.media_tvoc));

                    const canvas2 = document.getElementById("graficoMediaAQI");
                    const ctx2 = canvas2.getContext("2d");

                    new Chart(ctx2, {
                        type: "bar",
                        data: {
                            labels: labels2,
                            datasets: [{
                                label: "Média de TVOC (ppb)",
                                data: valores2,
                                backgroundColor: "rgba(30, 150, 255, 0.5)",
                                borderColor: "rgba(30, 150, 255, 1)",
                                borderWidth: 2
                            }]
                        },
                        options: {
                            responsive: true,
                            scales: {
                                x: { title: { display: true, text: "AQI" } },
                                y: { title: { display: true, text: "Média de TVOC (ppb)" }, beginAtZero: true }
                            },
                            plugins: {
                                legend: { display: true, position: "top" },
                                title: { display: true, text: "Média de TVOC Agrupada por AQI" }
                            }
                        }
                    });

                })
                .catch(() => {
                    alert("Erro ao carregar os dados.");
                });
        });
    </script>
</body>
</html>
