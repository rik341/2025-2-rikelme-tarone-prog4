<?php
include 'conecta_mysql.php';

// Período com valores padrão
$data_inicial = $_GET['inicio'] ?? '2025-06-01';
$data_final   = $_GET['fim'] ?? '2025-06-30';

/* ===========================================================
   1) BUSCA REGISTROS COMPLETOS COM FORMATAÇÃO
   =========================================================== */
$sql = "SELECT 
          DATE_FORMAT(
              STR_TO_DATE(CONCAT(datainclusao, ' ', horainclusao), '%Y-%m-%d %H:%i:%s'),
              '%d/%m/%Y %H:%i:%s'
          ) AS datahora_completa,
          ROUND(ninho, 1) AS ninho
        FROM leituramabel
        WHERE datainclusao BETWEEN :inicio AND :fim
        ORDER BY datainclusao, horainclusao ASC";

$stmt = $conecta->prepare($sql);
$stmt->execute([':inicio' => $data_inicial, ':fim' => $data_final]);
$resultados_registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ===========================================================
   2) BUSCA MÁXIMA FORMATADA
   =========================================================== */
$sql = "SELECT ROUND(MAX(ninho), 1) AS temperatura_maxima
        FROM leituramabel
        WHERE datainclusao BETWEEN :inicio AND :fim";

$stmt = $conecta->prepare($sql);
$stmt->execute([':inicio' => $data_inicial, ':fim' => $data_final]);
$linhaMax = $stmt->fetch(PDO::FETCH_ASSOC);
$temperatura_maxima = $linhaMax['temperatura_maxima'] ?? null;

/* ===========================================================
   3) BUSCA MÍNIMA FORMATADA
   =========================================================== */
$sql = "SELECT ROUND(MIN(ninho), 1) AS temperatura_minima
        FROM leituramabel
        WHERE datainclusao BETWEEN :inicio AND :fim";

$stmt = $conecta->prepare($sql);
$stmt->execute([':inicio' => $data_inicial, ':fim' => $data_final]);
$linhaMin = $stmt->fetch(PDO::FETCH_ASSOC);
$temperatura_minima = $linhaMin['temperatura_minima'] ?? null;

/* ===========================================================
   4) JSON
   =========================================================== */
if (isset($_GET['formato']) && $_GET['formato'] === 'json') {
    header("Content-Type: application/json; charset=utf-8");
    echo json_encode([
        "status"    => "ok",
        "inicio"    => $data_inicial,
        "fim"       => $data_final,
        "registros" => $resultados_registros,
        "maximo"    => $temperatura_maxima,
        "minimo"    => $temperatura_minima
    ]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ninho - MABEL</title>
    
    <link rel="stylesheet" href="../../frontend/style.css">
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script defer src="./script.js"></script>
</head>

<body>
    <header>
        <nav class="navbar">
            <div class="logo">IFSC <span>Chapecó</span></div>
            <ul class="nav-links">
                <li><a href="../aqi_ptqa/ptqa_aqi.php">PTQA</a></li>
                <li><a href="../index.html">Início</a></li>
            </ul>
        </nav>
    </header>   

    <div class="sidebar">
        <h2>Menu</h2>
        <a href="../temperatura_interna_mabel/temp_interna.php">Temperatura Interna</a>
        <a href="../temperatura_externa_mabel/temp_externa.php">Temperatura Externa</a>
        <a href="../umidade_interna_mabel/umi_interna.php">Umidade Interna</a>
        <a href="../umidade_externa_mabel/umi_externa.php">Umidade Externa</a>
        <a href="../ninho/ninho.php">Temperatura do Ninho</a>
    </div>

    <main class="content">

<form id="formPeriodo">
    <label>Início:
        <input type="date" id="inicio" value="2025-06-01">
    </label>
    <label>Fim:
        <input type="date" id="fim" value="2025-06-07">
    </label>
    <label>Intervalo de Leitura:
        <input type="number" id="intervalo" value="20" min="1" />
    </label>
    <button type="submit">Gerar Gráfico</button>
</form>

        
        <div id="mediaContainer" class="media-box">
            <strong>temperatura máxima do ninho</strong> 
            <span id="valormax">--</span> °C
        </div>
        
        <div id="mediaContainer" class="media-box">
            <strong>temperatura mínima do ninho</strong>
            <span id="valormin">--</span> °C
        </div>
        
        <h2>Gráfico da temperatura do ninho</h2>
        <canvas id="graficoTemperatura"></canvas>
        
    </main>
</body>
</html>