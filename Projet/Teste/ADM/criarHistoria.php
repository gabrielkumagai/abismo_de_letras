<?php
header("Content-Type: application/json");
include "conexaoHistoria.php";

// Receber dados
$titulo   = $_POST["titulo"]   ?? "";
$genero   = $_POST["genero"]   ?? "";
$conteudo = $_POST["conteudo"] ?? "";
$idUser   = 1; // usuário fixo por enquanto
$data     = date("Y-m-d H:i:s");

if (empty($titulo) || empty($genero)) {
    echo json_encode(["success" => false, "error" => "Título e gênero obrigatórios"]);
    exit;
}

$sql = $conn->prepare("
    INSERT INTO historia (ID_usuario, Titulo, Conteudo, Genero, Data_publi)
    VALUES (?, ?, ?, ?, ?)
");

$sql->bind_param("issss", $idUser, $titulo, $conteudo, $genero, $data);

if ($sql->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => $conn->error]);
}
