<?php
include 'conecta_mysql.php';

// Período padrão
$data_inicial = $_GET['inicio'] ?? '2025-06-01';
$data_final   = $_GET['fim'] ?? '2025-06-30';


// -----------------------------------------------
// 1) Consulta principal — dados formatados
// -----------------------------------------------
$sql = "SELECT 
          DATE_FORMAT(STR_TO_DATE(CONCAT(datainclusao,' ',horainclusao), '%Y-%m-%d %H:%i:%s'),
                       '%d/%m/%Y %H:%i:%s') AS datahora_completa,

          ROUND(he, 1) AS he
        FROM leituramabel
        WHERE datainclusao BETWEEN :inicio AND :fim
        ORDER BY datainclusao, horainclusao ASC";

$stmt = $conecta->prepare($sql);
$stmt->execute([':inicio' => $data_inicial, ':fim' => $data_final]);
$dados = $stmt->fetchAll(PDO::FETCH_ASSOC);


// -----------------------------------------------
// 2) Consulta — média formatada
// -----------------------------------------------
$sql = "SELECT 
          ROUND(AVG(he), 2) AS media_umidade_externa
        FROM leituramabel
        WHERE datainclusao BETWEEN :inicio AND :fim";

$stmt = $conecta->prepare($sql);
$stmt->execute([':inicio' => $data_inicial, ':fim' => $data_final]);
$res = $stmt->fetch(PDO::FETCH_ASSOC);

$media = $res['media_umidade_externa'] ?? null;


// -----------------------------------------------
// 3) Se pediram JSON → envia e encerra
// -----------------------------------------------
if (isset($_GET['formato']) && $_GET['formato'] === 'json') {
    header("Content-Type: application/json; charset=utf-8");
    echo json_encode([
        "dados" => $dados,
        "media" => $media
    ]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Umidade Externa - MABEL</title>

    <link rel="stylesheet" href="../../frontend/style.css">

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script defer src="script.js"></script>
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
        <input type="number" id="intervalo" value="20" min="1">
    </label>

    <button type="submit">Gerar Gráfico</button>
</form>

<div id="mediaContainer" class="media-box">
    <strong>Média da Umidade Externa:</strong>
    <span id="MediaUmidade">--</span> %
</div>

<h2>Gráfico da Umidade Externa</h2>
<canvas id="graficoUmidadeExterna"></canvas>

</main>
</body>
</html>