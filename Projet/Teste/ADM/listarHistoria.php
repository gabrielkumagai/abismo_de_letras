<?php
header("Content-Type: application/json");
include "conexaoHistoria.php";

$sql = $conn->query("SELECT * FROM historia ORDER BY ID_historia DESC");

$lista = [];
while ($h = $sql->fetch_assoc()) {
    $lista[] = [
        "id"      => $h["ID_historia"],
        "titulo"  => $h["Titulo"],
        "genero"  => $h["Genero"],
        "data"    => $h["Data_publi"],
        "conteudo"=> $h["Conteudo"]
    ];
}

echo json_encode($lista);
