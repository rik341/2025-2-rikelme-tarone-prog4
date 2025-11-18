<?php
include 'conecta_mysql.php';

// Define o período padrão
$data_inicial = $_GET['inicio'] ?? '2025-06-01';
$data_final   = $_GET['fim'] ?? '2025-06-30';

// ---------------------------
// CONSULTA 1 — AQI ≥ 4
// ---------------------------
$sql = "SELECT 
          CONCAT(dataleitura, ' ', horaleitura) AS datahora_completa,
          aqi
        FROM leituraptqa
        WHERE dataleitura BETWEEN :inicio AND :fim
          AND aqi >= 4
        ORDER BY dataleitura, horaleitura ASC";

$stmt = $conecta->prepare($sql);
$stmt->execute([':inicio' => $data_inicial, ':fim' => $data_final]);
$resultado_ruim = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ---------------------------
// CONSULTA 2 — AQI = 1 (ÓTIMO)
// ---------------------------
$sql2 = "SELECT 
           dataleitura,
           horaleitura,
           aqi
         FROM leituraptqa
         WHERE dataleitura BETWEEN :inicio AND :fim
           AND aqi = 1
         ORDER BY dataleitura, horaleitura ASC";

$stmt2 = $conecta->prepare($sql2);
$stmt2->execute([':inicio' => $data_inicial, ':fim' => $data_final]);
$resultado_otimo = $stmt2->fetchAll(PDO::FETCH_ASSOC);

// Retorno JSON (somente para o gráfico AQI ≥ 4)
if (isset($_GET['formato']) && $_GET['formato'] === 'json') {
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode([
    "ruim" => $resultado_ruim
  ]);
  exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Consulta de Qualidade do Ar - PTQA</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <link rel="stylesheet" href="style.css">
</head>
<body>

  <!-- Navbar -->
  <div class="navbar">
    <div class="logo">
      <img src="logo.png" alt="Logo PTQA">
    </div>
    <ul>
      <li><a href="index.php">Início</a></li>
      <li><a href="ptqa_aqi.php">Gráficos</a></li>
      <li><a href="#">Sobre</a></li>
      <li><a href="#">Contato</a></li>
    </ul>
  </div>

  <section class="grafico-section">
    <h1>Registros de Qualidade do Ar</h1>

    <!-- Filtro -->
    <form method="get">
      <label>Data inicial:</label>
      <input type="date" name="inicio" value="<?php echo $data_inicial; ?>">
      <label>Data final:</label>
      <input type="date" name="fim" value="<?php echo $data_final; ?>">
      <button type="submit">Filtrar</button>
    </form>

    <div class="loading" id="loading">Carregando dados...</div>

    <!-- GRÁFICO AQI ≥ 4 -->
    <h2>Registros de Baixa Qualidade do Ar (AQI ≥ 4)</h2>
    <canvas id="graficoAqi"></canvas>

    <!-- TABELA AQI = 1 -->
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

  <script src="aqi.js"></script>
  <script>
    const dataInicial = "<?php echo $data_inicial; ?>";
    const dataFinal   = "<?php echo $data_final; ?>";
  </script>

</body>
</html>
