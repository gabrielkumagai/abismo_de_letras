<?php
include 'conexao.php';
// Inicia sessão apenas se não existir
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// 1. Verificação de Segurança
if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}

// 2. Sanitização de Entradas
$id_seguidor = (int)$_SESSION['id_usuario'];
$id_seguido = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$action = isset($_GET['action']) ? $_GET['action'] : '';

// 3. Validação Lógica
if ($id_seguido == 0 || $id_seguidor == $id_seguido) {
    // Redireciona para o próprio perfil se tentar seguir a si mesmo ou ID for inválido
    header('Location: perfil.php');
    exit;
}

// 4. Execução da Ação
if ($action == 'follow') {
    $stmt = $conn->prepare("INSERT IGNORE INTO seguidores (id_seguidor, id_seguido) VALUES (?, ?)");
    $stmt->bind_param("ii", $id_seguidor, $id_seguido);
    $stmt->execute();
    $stmt->close();
} elseif ($action == 'unfollow') {
    $stmt = $conn->prepare("DELETE FROM seguidores WHERE id_seguidor = ? AND id_seguido = ?");
    $stmt->bind_param("ii", $id_seguidor, $id_seguido);
    $stmt->execute();
    $stmt->close();
}

$conn->close();

// 5. Redirecionamento
header('Location: ver_perfil.php?id=' . $id_seguido);
exit;
?>