<?php
include 'conecta_mysql.php';

// Define o período padrão (caso o usuário não escolha)
$data_inicial = $_GET['inicio'] ?? '2025-06-01';
$data_final   = $_GET['fim'] ?? '2025-06-30';

// Consulta SQL — pega o menor valor de tn no intervalo e mostra data/hora
$sql = "SELECT 
          CONCAT(datainclusao, ' ', horainclusao) AS datahora_completa,
          ti AS temperatura_interna
        FROM leituramabel
        WHERE ti = (
            SELECT MIN(ti)
            FROM leituramabel
            WHERE datainclusao BETWEEN :inicio AND :fim
        )
        ORDER BY datainclusao, horainclusao
        LIMIT 1;";

$stmt = $conecta->prepare($sql);
$stmt->execute([':inicio' => $data_inicial, ':fim' => $data_final]);
$resultado = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Temperatura Mínima do Ninho - MABEL</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 40px; }
    table { border-collapse: collapse; width: 50%; margin-top: 20px; }
    th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
    th { background-color: #f2f2f2; }
  </style>
</head>
<body>
  <h2>Temperatura Mínima do Ninho (Campo: ti)</h2>

  <form method="get">
    <label>Data inicial:</label>
    <input type="date" name="inicio" value="<?php echo $data_inicial; ?>">
    <label>Data final:</label>
    <input type="date" name="fim" value="<?php echo $data_final; ?>">
    <button type="submit">Filtrar</button>
  </form>

  <?php if ($resultado): ?>
    <table>
      <tr>
        <th>Data e Hora</th>
        <th>Temperatura Mínima (°C)</th>
      </tr>
      <tr>
        <td><?php echo htmlspecialchars($resultado['datahora_completa']); ?></td>
        <td><?php echo htmlspecialchars($resultado['temperatura_interna']); ?></td>
      </tr>
    </table>
  <?php else: ?>
    <p>Nenhum registro encontrado no período selecionado.</p>
  <?php endif; ?>
</body>
</html>
