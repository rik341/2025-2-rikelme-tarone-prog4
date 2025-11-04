<?php include("conexao.php"); ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Resultados PTQA</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="navbar">
  <div class="logo">
    <img src="./imagens/download.jpg">
  </div>
  <nav>
    <ul>
      <li><a href="index.html">Início</a></li>
      <li><a href="#">Consultas PTQA</a></li>
    </ul>
  </nav>
</header>

<section class="grafico-section">
<h1>Consultas SQL – PTQA (Grupo 1)</h1>
</section>

<div style="padding:40px">

<h2>1) Data, hora e temperatura</h2>
<table border="1">
<tr><th>Data/Hora</th><th>Temperatura</th></tr>
<?php
$sql = "SELECT datahora, temperatura FROM leituraptqa 
        WHERE datahora BETWEEN '2025-06-01' AND '2025-06-10'
        ORDER BY datahora ASC";

$result = mysqli_query($conn, $sql);
while($row = mysqli_fetch_assoc($result)){
    echo "<tr><td>".$row['datahora']."</td><td>".$row['temperatura']."</td></tr>";
}
?>
</table>
<br><br>

<h2>2) AQI ≥ 4</h2>
<table border="1">
<tr><th>Data/Hora</th><th>AQI</th></tr>
<?php
$sql = "SELECT datahora, aqi FROM leituraptqa 
        WHERE aqi >= 4
        AND datahora BETWEEN '2025-06-01' AND '2025-06-10'";

$result = mysqli_query($conn, $sql);
while($row = mysqli_fetch_assoc($result)){
    echo "<tr><td>".$row['datahora']."</td><td>".$row['aqi']."</td></tr>";
}
?>
</table>
<br><br>

<h2>3) Umidade > 70% (decrescente)</h2>
<table border="1">
<tr><th>Data/Hora</th><th>Umidade</th></tr>
<?php
$sql = "SELECT datahora, umidade FROM leituraptqa
        WHERE umidade > 70
        AND datahora BETWEEN '2025-06-01' AND '2025-06-10'
        ORDER BY umidade DESC";

$result = mysqli_query($conn, $sql);
while($row = mysqli_fetch_assoc($result)){
    echo "<tr><td>".$row['datahora']."</td><td>".$row['umidade']."</td></tr>";
}
?>
</table>
<br><br>

<h2>4) CO₂ > 1000 ppm</h2>
<table border="1">
<tr><th>Data/Hora</th><th>CO₂ (ppm)</th></tr>
<?php
$sql = "SELECT datahora, co2 FROM leituraptqa
        WHERE co2 > 1000
        AND datahora BETWEEN '2025-06-01' AND '2025-06-10'";

$result = mysqli_query($conn, $sql);
while($row = mysqli_fetch_assoc($result)){
    echo "<tr><td>".$row['datahora']."</td><td>".$row['co2']."</td></tr>";
}
?>
</table>
<br><br>

<h2>5) Pressão < 1000 hPa</h2>
<table border="1">
<tr><th>Data/Hora</th><th>Pressão</th></tr>
<?php
$sql = "SELECT datahora, pressao FROM leituraptqa
        WHERE pressao < 1000
        AND datahora BETWEEN '2025-06-01' AND '2025-06-10'";

$result = mysqli_query($conn, $sql);
while($row = mysqli_fetch_assoc($result)){
    echo "<tr><td>".$row['datahora']."</td><td>".$row['pressao']."</td></tr>";
}
?>
</table>
<br><br>

<h2>6) Gases voláteis > 200 ppb</h2>
<table border="1">
<tr><th>Data/Hora</th><th>Gases (ppb)</th></tr>
<?php
$sql = "SELECT datahora, gases FROM leituraptqa
        WHERE gases > 200
        AND datahora BETWEEN '2025-06-01' AND '2025-06-10'";

$result = mysqli_query($conn, $sql);
while($row = mysqli_fetch_assoc($result)){
    echo "<tr><td>".$row['datahora']."</td><td>".$row['gases']."</td></tr>";
}
?>
</table>

</div>
</body>
</html>