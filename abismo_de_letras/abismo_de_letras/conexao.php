<?php
$servidor = "localhost"; 
$usuario = "root";     
$senha = ""; // <-- Mantenha a senha correta (ou "" se não tiver)
$banco = "abismo_de_letras";

// NOVO: Adicione a porta que você configurou no XAMPP
$porta = "3307"; // Exemplo: 3307 ou 3308


$conn = new mysqli($servidor, $usuario, $senha, $banco, $porta);

// Verifica a conexão
if ($conn->connect_error) {
    die("Falha na conexão com o banco de dados: " . $conn->connect_error);
}
$conn->set_charset("utf8");
?>