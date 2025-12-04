<?php
error_reporting(0);
ini_set('display_errors', 0);
ob_start();

include 'conecta_mysql.php';

$data_inicial = $_GET['inicio'] ?? '2025-06-01';
$data_final   = $_GET['fim'] ?? '2025-06-30';


/* ----------------------------------------------------
   1) REGISTROS COMPLETOS DA TEMPERATURA INTERNA (TI)
------------------------------------------------------ */

$sql = "SELECT 
          DATE_FORMAT(
              STR_TO_DATE(CONCAT(datainclusao,' ',horainclusao), '%Y-%m-%d %H:%i:%s'),
              '%d/%m/%Y %H:%i:%s'
          ) AS datahora_completa,
          ROUND(ti, 2) AS ti
        FROM leituramabel
        WHERE datainclusao BETWEEN :inicio AND :fim
        ORDER BY datainclusao, horainclusao ASC";

$stmt = $conecta->prepare($sql);
$stmt->execute([':inicio'=>$data_inicial, ':fim'=>$data_final]);
$ti = $stmt->fetchAll(PDO::FETCH_ASSOC);


/* ----------------------------------------------------
   2) MÉDIA GERAL TI
------------------------------------------------------ */

$sql = "SELECT ROUND(AVG(ti), 2) AS media_temperatura_interna
        FROM leituramabel
        WHERE datainclusao BETWEEN :inicio AND :fim";

$stmt = $conecta->prepare($sql);
$stmt->execute([':inicio'=>$data_inicial, ':fim'=>$data_final]);
$media = $stmt->fetch(PDO::FETCH_ASSOC);
$media_ti_inicial = $media['media_temperatura_interna'] ?? null;


/* ----------------------------------------------------
   3) MÉDIA DIÁRIA
------------------------------------------------------ */

$sql = "SELECT 
          datainclusao,
          ROUND(AVG(ti), 2) AS media_diaria_ti
        FROM leituramabel
        WHERE datainclusao BETWEEN :inicio AND :fim
        GROUP BY datainclusao
        ORDER BY datainclusao ASC";

$stmt = $conecta->prepare($sql);
$stmt->execute([':inicio'=>$data_inicial, ':fim'=>$data_final]);
$mediadiaria = $stmt->fetchAll(PDO::FETCH_ASSOC);


/* ----------------------------------------------------
   4) DIFERENÇA ENTRE TI - TE
------------------------------------------------------ */

$sql = "SELECT ROUND(AVG(ABS(te - ti)), 2) AS media_diferenca
        FROM leituramabel
        WHERE datainclusao BETWEEN :inicio AND :fim";

$stmt = $conecta->prepare($sql);
$stmt->execute([':inicio'=>$data_inicial, ':fim'=>$data_final]);
$dif = $stmt->fetch(PDO::FETCH_ASSOC);
$media_dif_inicial = $dif['media_diferenca'] ?? null;


/* ----------------------------------------------------
   5) RETORNO JSON
------------------------------------------------------ */

if (isset($_GET['formato']) && $_GET['formato'] === 'json') {
    ob_clean();
    header("Content-Type: application/json; charset=utf-8");

    echo json_encode([
        "registros_ti" => $ti,
        "media_ti"     => $media_ti_inicial,
        "media_diaria" => $mediadiaria,
        "diferenca"    => $media_dif_inicial
    ], JSON_UNESCAPED_UNICODE);

    exit;
}
?>


<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Temperatura Interna - MABEL</title>
    
    <link rel="stylesheet" href="../style.css">
    
    
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
    <label>Início: <input type="date" id="inicio"></label>
    <label>Fim: <input type="date" id="fim"></label>
    <label>Intervalo de Leitura: <input type="number" id="intervalo" value="20" min="1"></label>
    <button type="submit">Gerar Gráfico</button>
</form>

        
        <div id="mediaContainerTI" class="media-box">
            <strong>Média da Temperatura Interna:</strong> <span id="valorMedia"><?= $media_ti_inicial ??
                '--' ?></span> °C
        </div>
        <div id="mediaContainerDif" class="media-box">
             <strong>Diferença média (TI − TE):</strong>
            <span id="valorMediaDif"><?= $media_dif_inicial ?? '--' ?></span> °C
        </div>
        
        <h2>Gráfico da Temperatura Interna</h2>
        <canvas id="graficoInterna"></canvas>

        <h2>Gráfico da Média diária</h2>
        <canvas id="graficoMediaDiaria"></canvas>
        
    </main>
</body>
</html>