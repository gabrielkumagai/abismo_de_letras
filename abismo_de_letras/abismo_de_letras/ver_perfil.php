<?php
include 'conexao.php';
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}

$id_usuario_logado = (int)$_SESSION['id_usuario'];
$id_perfil = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_perfil === 0) {
    header('Location: historias.php'); // Redireciona para um lugar seguro
    exit;
}

// Se o usuário estiver tentando ver o próprio perfil, redireciona para a página de edição
if ($id_perfil === $id_usuario_logado) {
    header('Location: perfil.php');
    exit;
}


// -----------------------------------------------------
// Busca Dados do Perfil Sendo Visualizado
// -----------------------------------------------------
$sql_user_data = "SELECT nome, tipo_usuario, foto_perfil FROM usuarios WHERE id_usuario = ?";
$stmt_user_data = $conn->prepare($sql_user_data);
$stmt_user_data->bind_param("i", $id_perfil);
$stmt_user_data->execute();
$user_data = $stmt_user_data->get_result()->fetch_assoc();
$stmt_user_data->close();

if (!$user_data) {
    echo "<p>Usuário não encontrado.</p>";
    exit;
}
$nome_usuario = $user_data['nome'];
$tipo_usuario = $user_data['tipo_usuario'];
$foto_perfil_src = $user_data['foto_perfil'] ?? 'default.png'; 


// -----------------------------------------------------
// Checa Status de Seguidor
// -----------------------------------------------------
$is_following = false;
$sql_check_follow = "SELECT 1 FROM seguidores WHERE id_seguidor = ? AND id_seguido = ?";
$stmt_check = $conn->prepare($sql_check_follow);
$stmt_check->bind_param("ii", $id_usuario_logado, $id_perfil);
$stmt_check->execute();
if ($stmt_check->get_result()->num_rows > 0) {
    $is_following = true;
}
$stmt_check->close();


// -----------------------------------------------------
// Busca Estatísticas do Usuário
// -----------------------------------------------------
$stats = [
    'total_historias' => 0,
    'total_comentarios' => 0,
    'total_seguidores' => 0
];

$sql_stats = "SELECT COUNT(id_historia) as total FROM historias WHERE id_autor = ?";
$stmt_stats = $conn->prepare($sql_stats);
$stmt_stats->bind_param("i", $id_perfil);
$stmt_stats->execute();
$stats['total_historias'] = $stmt_stats->get_result()->fetch_row()[0];
$stmt_stats->close();

$sql_comments = "SELECT COUNT(id_interacao) as total FROM interacoes WHERE id_usuario = ?";
$stmt_comments = $conn->prepare($sql_comments);
$stmt_comments->bind_param("i", $id_perfil);
$stmt_comments->execute();
$stats['total_comentarios'] = $stmt_comments->get_result()->fetch_row()[0];
$stmt_comments->close();

$sql_followers = "SELECT COUNT(id_seguidor) as total FROM seguidores WHERE id_seguido = ?";
$stmt_followers = $conn->prepare($sql_followers);
$stmt_followers->bind_param("i", $id_perfil);
$stmt_followers->execute();
$stats['total_seguidores'] = $stmt_followers->get_result()->fetch_row()[0];
$stmt_followers->close();


// -----------------------------------------------------
// Busca as Badges Conquistadas
// -----------------------------------------------------
$sql_badges = "SELECT b.nome, b.descricao, b.icone FROM usuario_badge ub JOIN badges b ON ub.id_badge = b.id_badge WHERE ub.id_usuario = ? ORDER BY ub.data_conquista DESC";
$stmt_badges = $conn->prepare($sql_badges);
$stmt_badges->bind_param("i", $id_perfil);
$stmt_badges->execute();
$resultado_badges = $stmt_badges->get_result();
$stmt_badges->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de <?php echo htmlspecialchars($nome_usuario); ?> - Abismo de Letras</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Replicando estilos de perfil e badge para garantir consistência */
        .profile-header { display: flex; align-items: flex-start; margin-bottom: 20px; }
        .profile-img { width: 100px; height: 100px; border-radius: 50%; object-fit: cover; margin-right: 20px; border: 3px solid var(--cor-acento); background-color: #eee; }
        .badge-item { display: inline-block; background-color: #f0f0f0; padding: 5px 10px; margin-right: 10px; margin-bottom: 10px; border-radius: 15px; font-size: 0.9em; border: 1px solid #ddd; }
        .badge-item span { font-size: 1.2em; margin-right: 5px; }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    <main class="container">
        <h2>Perfil do Escritor</h2>
        
        <div class="card">
            <div class="profile-header">
                <img src="<?php echo htmlspecialchars($foto_perfil_src); ?>" alt="Foto de Perfil" class="profile-img">
                <div>
                    <h3><?php echo htmlspecialchars($nome_usuario); ?></h3>
                    <p>Perfil: **<?php echo htmlspecialchars(ucfirst($tipo_usuario)); ?>**</p>
                    <p><strong>Seguidores:</strong> <?php echo $stats['total_seguidores']; ?></p>
                    
                    <?php if ($is_following): ?>
                        <a href="seguir.php?id=<?php echo $id_perfil; ?>&action=unfollow" class="btn" style="background-color: darkred;">
                            🚫 Parar de Seguir
                        </a>
                    <?php else: ?>
                        <a href="seguir.php?id=<?php echo $id_perfil; ?>&action=follow" class="btn-cta">
                            ➕ Seguir
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <h3>📊 Estatísticas de Escrita</h3>
        <div class="card">
            <p><strong>Total de Histórias/Versões Publicadas:</strong> <?php echo $stats['total_historias']; ?></p>
            <p><strong>Comentários Feitos:</strong> <?php echo $stats['total_comentarios']; ?></p>
        </div>

        <h3>🏆 Conquistas (Badges)</h3>
        <div class="card">
            <?php
            if ($resultado_badges->num_rows > 0) {
                while ($badge = $resultado_badges->fetch_assoc()) {
                    echo "<div class='badge-item' title='" . htmlspecialchars($badge['descricao']) . "'>";
                    echo "<span>" . htmlspecialchars($badge['icone']) . "</span>" . htmlspecialchars($badge['nome']);
                    echo "</div>";
                }
            } else {
                echo "<p>O usuário ainda não conquistou nenhuma badge.</p>";
            }
            ?>
        </div>
        
        <h3 style="margin-top: 30px;">Contribuições Recentes</h3>
        <p>Clique abaixo para ver as histórias e versões criadas por <?php echo htmlspecialchars($nome_usuario); ?>.</p>
        <a href="historias.php?autor=<?php echo $id_perfil; ?>" class="btn-cta">Ver Histórias de <?php echo htmlspecialchars($nome_usuario); ?></a>
        
    </main>
    <footer><p>&copy; 2025 Abismo de Letras.</p></footer>
</body>
</html>
<?php $conn->close(); ?>