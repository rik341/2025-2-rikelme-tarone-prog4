<?php
error_reporting(E_ALL); 
ini_set("display_errors", 1);

$servername = "localhost";
$username = "root";        // padrão do XAMPP
$password = "";            // sem senha
$dbname = "mabel_ptqa_agata_enzo_rikelme_taynan";	

try {
  $conecta = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
  $conecta->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
  echo "Connection failed: " . $e->getMessage();
}
?>
