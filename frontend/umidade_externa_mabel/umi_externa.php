<?php
include 'conecta_mysql.php';

// Define o período (com valores padrão)
$data_inicial = $_GET['inicio'] ?? '2025-06-01';
$data_final   = $_GET['fim'] ?? '2025-06-30';

// 1) Consulta SQL — junta data e hora reais da inclusão
$sql = "SELECT 
          CONCAT(datainclusao, ' ', horainclusao) AS datahora_completa,
          he
        FROM leituramabel
        WHERE datainclusao BETWEEN :inicio AND :fim
        ORDER BY datainclusao, horainclusao ASC";

$stmt = $conecta->prepare($sql);
$stmt->execute([':inicio' => $data_inicial, ':fim' => $data_final]);
$dados = $stmt->fetchAll(PDO::FETCH_ASSOC);


// 2) Consulta SQL — calcula a média da umidade externa
$sql = "SELECT 
          ROUND(AVG(he), 2) AS media_umidade_externa
        FROM leituramabel
        WHERE datainclusao BETWEEN :inicio AND :fim";

$stmt = $conecta->prepare($sql);
$stmt->execute([':inicio' => $data_inicial, ':fim' => $data_final]);
$resultadoMedia = $stmt->fetch(PDO::FETCH_ASSOC);

$media = $resultadoMedia['media_umidade_externa'] ?? null;


// 3) Retorno JSON
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
    <title>Umidade Interna - MABEL</title>
    
    <link rel="stylesheet" href="../../frontend/style_mabel.css">
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script defer src="script.js"></script>
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
            <button type="submit">Filtrar</button>
        </form>
        
        <div id="mediaContainer" class="media-box">
            <strong>Média da Umidade Externa:</strong> 
            <span id="MediaUmidade">--</span> %
        </div>

        <h2>Gráfico da Umidade Externa</h2>
        <canvas id="graficoUmidadeexterna"></canvas>
        
    </main>
</body>
</html>
