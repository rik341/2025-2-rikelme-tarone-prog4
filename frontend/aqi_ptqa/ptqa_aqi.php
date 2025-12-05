<?php
include 'conecta_mysql.php';
// define o período
$data_inicial = $_GET['inicio'] ?? '2025-06-01';
$data_final   = $_GET['fim'] ?? '2025-06-30';

// captura intervalo (padrão 20)
$intervalo = isset($_GET['intervalo']) && (int)$_GET['intervalo'] > 0 ? (int)$_GET['intervalo'] : 20;



// CONSULTA 1 
$sql = "SELECT 
          DATE_FORMAT(CONCAT(dataleitura, ' ', horaleitura), '%d/%m/%Y %H:%i:%s') AS datahora_completa,
          aqi
        FROM leituraptqa
        WHERE dataleitura BETWEEN :inicio AND :fim
          AND aqi >= 4
        ORDER BY dataleitura, horaleitura ASC";

$stmt = $conecta->prepare($sql);
$stmt->execute([':inicio' => $data_inicial, ':fim' => $data_final]);
$resultado_ruim = $stmt->fetchAll(PDO::FETCH_ASSOC);

// CONSULTA 2 (PRIMEIRA LEITURA DO DIA)

$sql2 = "SELECT 
            DATE_FORMAT(t1.dataleitura, '%d/%m/%Y') AS dataleitura,
            t1.horaleitura,
            t1.aqi
         FROM leituraptqa t1
         JOIN (
             SELECT 
                 DATE(dataleitura) AS data_dia,
                 MIN(horaleitura) AS primeira_hora
             FROM leituraptqa
             WHERE dataleitura BETWEEN :inicio AND :fim
               AND aqi = 1
             GROUP BY DATE(dataleitura)
         ) t2
           ON DATE(t1.dataleitura) = t2.data_dia
          AND t1.horaleitura = t2.primeira_hora
         WHERE t1.aqi = 1
         ORDER BY t1.dataleitura ASC";

$stmt2 = $conecta->prepare($sql2);
$stmt2->execute([':inicio' => $data_inicial, ':fim' => $data_final]);
$resultado_otimo = $stmt2->fetchAll(PDO::FETCH_ASSOC);


// retorno json

if (isset($_GET['formato']) && $_GET['formato'] === 'json') {
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode([
    "ruim"  => $resultado_ruim,
    "otimo" => $resultado_otimo
  ], JSON_UNESCAPED_UNICODE);
  exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Consulta de Qualidade do Ar - PTQA</title>
  <link rel="stylesheet" href="../../frontend/style.css">

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script defer src="./aqi.js"></script>

  <style>
    body { font-family: Arial, sans-serif; }
    .grafico-section { width: 80%; margin: 0 auto; text-align: center; }
    canvas { max-width: 100%; height: 400px; }
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

  <h1>Registros de Qualidade do Ar</h1>

 <form id="formPeriodo" method="get" action="ptqa_aqi.php">
    <label>Data inicial:</label>
    <input type="date" name="inicio" value="<?php echo htmlspecialchars($data_inicial); ?>">

    <label>Data final:</label>
    <input type="date" name="fim" value="<?php echo htmlspecialchars($data_final); ?>">

    <label for="intervalo">Intervalo (n):</label>
    <input id="intervalo" name="intervalo" type="number" min="1" step="1" value="<?php echo $intervalo; ?>" style="width:80px">

    <button type="submit">Filtrar</button>
</form>



  <div class="loading" id="loading">Carregando dados...</div>

  <!-- GRÁFICO -->
  <h2>Registros de Baixa Qualidade do Ar (AQI ≥ 4)</h2>
  <canvas id="graficoAqi"></canvas>

  <!-- TABELA-->
  <h2>Registros de Ótima Qualidade do Ar (AQI = 1)</h2>

  <?php if (count($resultado_otimo) === 0): ?>

      <p style="color:green;">Nenhum registro AQI = 1 encontrado no período.</p>

  <?php else: ?>

  <table>
      <thead>
          <tr>
              <th>Data</th>
              <th>Hora</th>
              <th>AQI</th>
          </tr>
      </thead>
      <tbody>
          <?php foreach ($resultado_otimo as $linha): ?>
            <tr>
              <td><?php echo $linha['dataleitura']; ?></td>
              <td><?php echo $linha['horaleitura']; ?></td>
              <td><?php echo $linha['aqi']; ?></td>
            </tr>
          <?php endforeach; ?>
      </tbody>
  </table>

  <?php endif; ?>

</section>

<script>
  const dataInicial = "<?php echo $data_inicial; ?>";
  const dataFinal   = "<?php echo $data_final; ?>";
  const intervalo   = <?php echo $intervalo; ?>;
</script>


</body>
</html>
