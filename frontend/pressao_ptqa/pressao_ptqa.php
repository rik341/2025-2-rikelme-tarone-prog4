<?php
include 'conecta_mysql.php';

// Define período padrão
$data_inicial = $_GET['inicio'] ?? '2025-02-17';
$data_final   = $_GET['fim']    ?? '2025-02-25';

// Função para limpar valores de pressão
function limparPressao($valor) {
    if ($valor === null || $valor === "") return null;

    // Troca vírgula por ponto
    $valor = str_replace(',', '.', trim($valor));

    // Mantém apenas números e ponto
    if (!is_numeric($valor)) return null;

    return floatval($valor);
}

/* ==============================
   1. Pressão < 1000 hPa
   ============================== */
$sql1 = "SELECT 
            DATE_FORMAT(dataleitura, '%d/%m/%Y') AS data_leitura,
            horaleitura,
            CONCAT(DATE_FORMAT(dataleitura, '%d/%m/%Y'), ' ', horaleitura) AS datahora_completa,
            pressao
         FROM leituraptqa
         WHERE dataleitura BETWEEN :inicio AND :fim
           AND pressao < 1200  -- evita lixo
         ORDER BY dataleitura ASC, horaleitura ASC";

$stmt1 = $conecta->prepare($sql1);
$stmt1->execute([':inicio' => $data_inicial, ':fim' => $data_final]);
$dados1 = $stmt1->fetchAll(PDO::FETCH_ASSOC);

// limpar valores
$resultado1 = [];
foreach ($dados1 as $d) {
    $p = limparPressao($d['pressao']);
    if ($p !== null && $p > 100 && $p < 1100) { // validade real
        $d['pressao'] = $p;
        $resultado1[] = $d;
    }
}

/* ==============================
   2. Pressão mínima diária
   ============================== */
$sql2 = "SELECT 
            DATE_FORMAT(dataleitura, '%d/%m/%Y') AS data_leitura,
            pressao
         FROM leituraptqa
         WHERE dataleitura BETWEEN :inicio AND :fim
         ORDER BY dataleitura ASC";

$stmt2 = $conecta->prepare($sql2);
$stmt2->execute([':inicio' => $data_inicial, ':fim' => $data_final]);
$dados2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);

$resultado2 = [];
$minDiaria = [];

foreach ($dados2 as $d) {
    $p = limparPressao($d['pressao']);
    if ($p !== null && $p > 100 && $p < 1100) {
        // pega o menor do dia
        if (!isset($minDiaria[$d['data_leitura']]) || $p < $minDiaria[$d['data_leitura']]) {
            $minDiaria[$d['data_leitura']] = $p;
        }
    }
}


foreach ($minDiaria as $dia => $valor) {
    $resultado2[] = [
        "data_leitura" => $dia,
        "pressao_minima" => $valor
    ];
}

/* ==============================
   3. JSON para gráficos
   ============================== */
if (isset($_GET['formato']) && $_GET['formato'] === 'json') {
    header('Content-Type: application/json; charset=utf-8');

    echo json_encode([
        'pressao_baixa' => $resultado1,
        'pressao_minima' => $resultado2
    ], JSON_UNESCAPED_UNICODE);

    exit;
}
?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Gráficos de Pressão - PTQA</title>
  <link rel="stylesheet" href="../../frontend/style.css">

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
<header>
    <nav class="navbar">
        <div class="logo">IFSC <span>Chapecó</span></div>
        <ul class="nav-links">
            <li><a href="../temperatura_interna_mabel/temp_interna.php">Mabel</a></li>
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
<h1>Gráficos de Pressão Atmosférica</h1>

<!-- Filtro -->
<form id="formPeriodo" method="get">
    <label>Data inicial:</label>
    <input type="date" name="inicio" value="<?php echo $data_inicial; ?>">

    <label>Data final:</label>
    <input type="date" name="fim" value="<?php echo $data_final; ?>">

    <label>Intervalo de leituras:</label>
    <input type="number" id="intervalo" name="intervalo" 
           value="<?php echo $_GET['intervalo'] ?? 20; ?>" min="1">

    <button type="submit">Filtrar</button>
</form>

<div id="loading">Carregando dados...</div>

<canvas id="graficoPressaoBaixa" width="800" height="400"></canvas>
<canvas id="graficoPressaoMinima" width="800" height="400"></canvas>

</section>

<!-- Passa dados do PHP para o JS -->
<script>
  const dataInicial = "<?php echo $data_inicial; ?>";
  const dataFinal   = "<?php echo $data_final; ?>";
  const intervalo   = <?php echo $_GET['intervalo'] ?? 20; ?>;
</script>

<!-- Agora pode carregar o script, pois as variáveis já existem -->
<script src="pressao.js"></script>

</body>
</html>