<?php
error_reporting(0);
ini_set('display_errors', 0);
ob_start();

include 'conecta_mysql.php';


// 1) Definir datas padrão
$data_inicial = $_GET['inicio'] ?? '2025-06-01';
$data_final   = $_GET['fim'] ?? '2025-06-30';

/* ============================================================
   2) CONSULTA: registros de temperatura interna (ti)
============================================================ */
$sql = "SELECT 
          CONCAT(datainclusao, ' ', horainclusao) AS datahora_completa,
          ti
        FROM leituramabel
        WHERE datainclusao BETWEEN :inicio AND :fim
        ORDER BY datainclusao, horainclusao ASC";

$stmt = $conecta->prepare($sql);
$stmt->execute([':inicio' => $data_inicial, ':fim' => $data_final]);
$ti = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ============================================================
   3) CONSULTA: média geral da TI
============================================================ */
$sql = "SELECT ROUND(AVG(ti), 2) AS media_temperatura_interna
        FROM leituramabel
        WHERE datainclusao BETWEEN :inicio AND :fim";

$stmt = $conecta->prepare($sql);
$stmt->execute([':inicio' => $data_inicial, ':fim' => $data_final]);
$media = $stmt->fetch(PDO::FETCH_ASSOC);

/* ============================================================
   4) CONSULTA: média diária da TI
============================================================ */
$sql = "SELECT 
          datainclusao,
          ROUND(AVG(ti), 2) AS media_diaria_ti
        FROM leituramabel
        WHERE datainclusao BETWEEN :inicio AND :fim
        GROUP BY datainclusao
        ORDER BY datainclusao ASC";

$stmt = $conecta->prepare($sql);
$stmt->execute([':inicio' => $data_inicial, ':fim' => $data_final]);
$mediadiaria = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ============================================================
   5) CONSULTA: diferença média entre TE e TI
============================================================ */
$sql = "SELECT AVG(ABS(te - ti)) AS media_diferenca
        FROM leituramabel
        WHERE datainclusao BETWEEN :inicio AND :fim";

$stmt = $conecta->prepare($sql);
$stmt->execute([':inicio' => $data_inicial, ':fim' => $data_final]);
$dif = $stmt->fetch(PDO::FETCH_ASSOC);

/* ============================================================
   6) Retorno JSON (para o JS)
============================================================ */
if (isset($_GET['formato']) && $_GET['formato'] === 'json') {

    ob_clean(); // limpa qualquer HTML que escapou
    header("Content-Type: application/json; charset=utf-8");

    echo json_encode([
        "registros_ti"  => $ti,
        "media_ti"      => $media['media_temperatura_interna'],
        "media_diaria"  => $mediadiaria,
        "diferenca"     => $dif['media_diferenca']
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
    
    <link rel="stylesheet" href="../../frontend/style_mabel.css">
    
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script defer src="./script.js"></script>
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
            <button type="submit">Gerar Gráfico</button>
        </form>

        <div id="mediaContainer" class="media-box">
            <strong>Média da Temperatura Interna:</strong> <span id="valorMedia">--</span> °C
        </div>
        
        <div>
            <strong>Diferença média (TI − TE):</strong>
            <span id="valorMediaDif">--</span> °C
        </div>
        
        <h2>Gráfico da Temperatura Interna</h2>
        <canvas id="graficoInterna"></canvas>

        <h2>Gráfico da Média diária</h2>
        <canvas id="graficoMediaDiaria"></canvas>
        
    </main>
</body>
</html>

