<?php
include 'conecta_mysql.php';

// Define as datas de início e fim, com valores padrão caso não sejam passados via GET
$data_inicial = $_GET['inicio'] ?? '2025-06-01';
$data_final   = $_GET['fim'] ?? '2025-06-30';

// Consulta para pegar os registros de temperatura externa
$sql = "SELECT 
          CONCAT(datainclusao, ' ', horainclusao) AS datahora_completa,
          te
        FROM leituramabel
        WHERE datainclusao BETWEEN :inicio AND :fim
        ORDER BY datainclusao, horainclusao ASC";
$stmt = $conecta->prepare($sql);
$stmt->execute([':inicio' => $data_inicial, ':fim' => $data_final]);
$grafico = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Consulta para pegar a média da temperatura externa
$sql = "SELECT 
          ROUND(AVG(te), 2) AS media_temperatura_externa
        FROM leituramabel
        WHERE datainclusao BETWEEN :inicio AND :fim";
$stmt = $conecta->prepare($sql);
$stmt->execute([':inicio' => $data_inicial, ':fim' => $data_final]);
$media = $stmt->fetch(PDO::FETCH_ASSOC);

// Consulta para calcular a diferença média entre a temperatura interna (ti) e externa (te)
$sql = "SELECT 
          AVG(ABS(te - ti)) AS media_diferenca
        FROM leituramabel
        WHERE datainclusao BETWEEN :inicio AND :fim;";
$stmt = $conecta->prepare($sql);
$stmt->execute([':inicio' => $data_inicial, ':fim' => $data_final]);
$dif = $stmt->fetch(PDO::FETCH_ASSOC);

// Verifica se a requisição é para retornar no formato JSON
if (isset($_GET['formato']) && $_GET['formato'] === 'json') {
  header("Content-Type: application/json; charset=utf-8");
  echo json_encode([
      "dados"      => $grafico,
      "media"      => $media['media_temperatura_externa'],
      "diferenca"  => $dif['media_diferenca']
  ]);
  exit;
}
?>

<!-- HTML para exibição do gráfico -->
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Temperatura Externa - MABEL</title>
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
    <label>Início: <input type="date" id="inicio"></label>
    <label>Fim: <input type="date" id="fim"></label>
    <label>Intervalo de Leitura: <input type="number" id="intervalo" value="20" min="1"></label>
    <button type="submit">Gerar Gráfico</button>
</form>

        
        <div id="mediaContainer" class="media-box">
            <strong>Média da Temperatura Externa:</strong> <span id="valorMedia">--</span> °C
        </div>
        <div id="mediaContainer" class="media-box">
            <strong>Diferença média (TI − TE):</strong>
            <span id="valorMediaDif">--</span> °C
        </div>
        
        <h2>Gráfico da Temperatura Externa</h2>
        <canvas id="graficoTemperatura"></canvas>
    </main>
</body>
</html>
