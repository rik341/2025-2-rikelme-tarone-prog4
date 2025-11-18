<?php
include 'conecta_mysql.php';

// Define o período (com valores padrão)
$data_inicial = $_GET['inicio'] ?? '2025-06-01';
$data_final   = $_GET['fim'] ?? '2025-06-30';

// Consulta SQL — junta data e hora reais da inclusão
$sql = "SELECT 
          CONCAT(datainclusao, ' ', horainclusao) AS datahora_completa,
          hi
        FROM leituramabel
        WHERE datainclusao BETWEEN :inicio AND :fim
        ORDER BY datainclusao, horainclusao ASC";

$stmt = $conecta->prepare($sql);
$stmt->execute([':inicio' => $data_inicial, ':fim' => $data_final]);
$resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);



// Consulta SQL — calcula a média da umidade interna no intervalo
$sql = "SELECT 
          ROUND(AVG(hi), 2) AS media_umidade_interna
        FROM leituramabel
        WHERE datainclusao BETWEEN :inicio AND :fim";

$stmt = $conecta->prepare($sql);
$stmt->execute([':inicio' => $data_inicial, ':fim' => $data_final]);
$resultadomedia = $stmt->fetch(PDO::FETCH_ASSOC);

$media = $resultadomedia['media_umidade_interna'] ?? null;

if (isset($_GET['formato']) && $_GET['formato'] === 'json') {
  header("Content-Type: application/json; charset=utf-8");

  echo json_encode([
      "dados" => $resultado,
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
    <title>Temperatura Externa - MABEL</title>
    
    <link rel="stylesheet" href="../../frontend/style_mabel.css">
    
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script defer src="script.js"></script>
</head>

<body>
    <header>
        <nav class="navbar">
            <div class="logo">IFSC <span>Chapecó</span></div>
            <ul class="nav-links">
                <li><a href="index.html">Início</a></li>
            </ul>
        </nav>
    </header>   

    <div class="sidebar">
        <h2>Menu</h2>
        <a href="mabel_ti.html">Temperatura Interna</a>
        <a href="mabel_te.html">Temperatura Externa</a>
        <a href="mabel_hi.html">Umidade Interna</a>
        <a href="mabel_he.html">Umidade Externa</a>
    </div>

    <main class="content">
        
        <form id="formPeriodo">
            <label>Início: <input type="date" id="inicio"></label>
            <label>Fim: <input type="date" id="fim"></label>
            <button type="submit">filtrar</button>
        </form>
        
        <div id="mediaContainer" class="media-box">
            <strong>Média da Temperatura Externa:</strong> <span id="MediaUmidade">--</span> °C
        </div>

        <h2>Gráfico da Temperatura Externa</h2>
        <canvas id="graficoUmidadeInterna"></canvas>
        
    </main>
</body>
</html>


