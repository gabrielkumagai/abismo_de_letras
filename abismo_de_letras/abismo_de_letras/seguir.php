<?php
include 'conexao.php';
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}

$id_seguidor = (int)$_SESSION['id_usuario'];
$id_seguido = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($id_seguido == 0 || $id_seguidor == $id_seguido) {
    // Não pode seguir a si mesmo ou ID inválido
    header('Location: perfil.php');
    exit;
}

if ($action == 'follow') {
    // Tenta seguir (INSERT IGNORE evita duplicatas)
    $sql = "INSERT IGNORE INTO seguidores (id_seguidor, id_seguido) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id_seguidor, $id_seguido);
    $stmt->execute();
    $stmt->close();
} elseif ($action == 'unfollow') {
    // Deixar de seguir (DELETE)
    $sql = "DELETE FROM seguidores WHERE id_seguidor = ? AND id_seguido = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id_seguidor, $id_seguido);
    $stmt->execute();
    $stmt->close();
}

$conn->close();

// Redireciona de volta para o perfil visualizado
header('Location: ver_perfil.php?id=' . $id_seguido);
exit;