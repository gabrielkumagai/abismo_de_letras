<?php
$host = "localhost";
$user = "root";     
$pass = "";         
$db   = "abismo_de_letras";  // <-- coloque aqui o nome do seu banco

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}
?>
