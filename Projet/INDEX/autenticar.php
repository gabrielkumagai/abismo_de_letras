<?php
session_start();
include("conexao.php");

$email = $_POST['email'];
$senha = $_POST['senha']; // senha normal

$sql = "SELECT * FROM usuario WHERE email = ? AND senha = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $email, $senha);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows > 0) {
    $usuario = $resultado->fetch_assoc();

    $_SESSION['id'] = $usuario['id'];
    $_SESSION['nome'] = $usuario['nome'];
    $_SESSION['email'] = $usuario['email'];

    header("Location: ../Teste/pag_home_copia.php");
    exit();
} else {
    header("Location: entrar.php?erro=1");
    exit();
}

$conn->close();
?>
