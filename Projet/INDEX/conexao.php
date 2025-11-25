<?php
// Dados de conexão
$host = "127.0.0.1:3307";   // Servidor local (XAMPP ou WAMP)
$usuario = "root";     // Usuário padrão do MySQL local
$senha = "";           // Senha (geralmente vazia no XAMPP)
$banco = "abismo_de_letras"; // Nome do banco de dados

// Criando a conexão
$conn = new mysqli($host, $usuario, $senha, $banco);

// Verificando erros
if ($conn->connect_error) {
    die("❌ Erro na conexão: " . $conn->connect_error);
} else {
    // echo "✅ Conexão bem-sucedida!";
}
?>

