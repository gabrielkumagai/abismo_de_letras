<?php
include("conexao.php");

// Dados recebidos
$nome = $_POST['nome'];
$email = $_POST['email'];
$senha = $_POST['senha']; // <-- agora senha normal

$sql = "INSERT INTO usuario (nome, email, senha) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $nome, $email, $senha);

if ($stmt->execute()) {
    header("Location: ../Teste/pag_home_copia.php?sucesso=1");
    exit();
} else {
    echo "Erro ao cadastrar: " . $conn->error;
}

$conn->close();
?>
