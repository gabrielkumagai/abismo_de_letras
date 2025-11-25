<?php
$conn = new mysqli("localhost", "root", "", "seu_banco");

if ($conn->connect_error) {
    die("Erro na conexão");
}

$titulo = $_POST['titulo'];
$genero = $_POST['genero'];

$sql = $conn->prepare("INSERT INTO historias (titulo, genero) VALUES (?, ?)");
$sql->bind_param("ss", $titulo, $genero);

if ($sql->execute()) {
    echo "OK";
} else {
    echo "ERRO: " . $conn->error;
}

$conn->close();
?>
