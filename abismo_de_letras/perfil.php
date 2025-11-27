<?php
include 'conexao.php';
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}

$id_usuario = (int)$_SESSION['id_usuario'];
$nome_usuario = $_SESSION['nome'];
$tipo_usuario = $_SESSION['tipo_usuario'];
$mensagem_conquista = "";

// -----------------------------------------------------
// Lógica para conferir a Badge de "Iniciador de Abismos"
// -----------------------------------------------------
// (Mantida do código anterior, funciona como um gatilho simples)
$sql_check_inicial = "SELECT COUNT(*) FROM historias WHERE id_autor = ? AND id_historia_original IS NULL";
$stmt_inicial = $conn->prepare($sql_check_inicial);
$stmt_inicial->bind_param("i", $id_usuario);
$stmt_inicial->execute();
$count_inicial = $stmt_inicial->get_result()->fetch_row()[0];
$stmt_inicial->close();

if ($count_inicial > 0) {
    $sql_give_badge = "INSERT IGNORE INTO usuario_badge (id_usuario, id_badge) SELECT ?, id_badge FROM badges WHERE nome = 'Iniciador de Abismos'";
    $stmt_badge = $conn->prepare($sql_give_badge);
    $stmt_badge->bind_param("i", $id_usuario);
    $stmt_badge->execute();
    if ($stmt_badge->affected_rows > 0) {
        $mensagem_conquista = "Parabéns! Você conquistou a badge 'Iniciador de Abismos'!";
    }
    $stmt_badge->close();
}

// -----------------------------------------------------
// Busca Estatísticas do Usuário
// -----------------------------------------------------
$stats = [
    'palavras_escritas' => 0,
    'total_historias' => 0,
    'total_comentarios' => 0,
    'total_seguidores' => 0
];

$sql_stats = "SELECT SUM(LENGTH(conteudo)) as palavras, COUNT(id_historia) as total FROM historias WHERE id_autor = ?";
$stmt_stats = $conn->prepare($sql_stats);
$stmt_stats->bind_param("i", $id_usuario);
$stmt_stats->execute();
$res_stats = $stmt_stats->get_result()->fetch_assoc();
$stmt_stats->close();
if ($res_stats) {
    // Estimativa simples: divide o total de caracteres por 5 (média de letras por palavra)
    $stats['palavras_escritas'] = round($res_stats['palavras'] / 5);
    $stats['total_historias'] = $res_stats['total'];
}

$sql_comments = "SELECT COUNT(id_interacao) as total FROM interacoes WHERE id_usuario = ?";
$stmt_comments = $conn->prepare($sql_comments);
$stmt_comments->bind_param("i", $id_usuario);
$stmt_comments->execute();
$stats['total_comentarios'] = $stmt_comments->get_result()->fetch_row()[0];
$stmt_comments->close();

$sql_followers = "SELECT COUNT(id_seguidor) as total FROM seguidores WHERE id_seguido = ?";
$stmt_followers = $conn->prepare($sql_followers);
$stmt_followers->bind_param("i", $id_usuario);
$stmt_followers->execute();
$stats['total_seguidores'] = $stmt_followers->get_result()->fetch_row()[0];
$stmt_followers->close();


// -----------------------------------------------------
// Busca as Badges Conquistadas
// -----------------------------------------------------
$sql_badges = "SELECT b.nome, b.descricao, b.icone FROM usuario_badge ub JOIN badges b ON ub.id_badge = b.id_badge WHERE ub.id_usuario = ? ORDER BY ub.data_conquista DESC";
$stmt_badges = $conn->prepare($sql_badges);
$stmt_badges->bind_param("i", $id_usuario);
$stmt_badges->execute();
$resultado_badges = $stmt_badges->get_result();
$stmt_badges->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Perfil - Abismo de Letras</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; // Inclui o cabeçalho ?>
    <main class="container">
        <h2>Bem-vindo, <?php echo htmlspecialchars($nome_usuario); ?>!</h2>
        <div class="card">
            <p>Seu Perfil: **<?php echo htmlspecialchars(ucfirst($tipo_usuario)); ?>**</p>
            <p><strong>Seguidores:</strong> <?php echo $stats['total_seguidores']; ?></p>
            </div>

        <?php if (!empty($mensagem_conquista)): ?>
            <div class="alert-success"><?php echo $mensagem_conquista; ?></div>
        <?php endif; ?>

        <h3>📊 Estatísticas de Escrita</h3>
        <div class="card">
            <p><strong>Total de Histórias/Versões Publicadas:</strong> <?php echo $stats['total_historias']; ?></p>
            <p><strong>Palavras Escritas (Estimado):</strong> <?php echo number_format($stats['palavras_escritas'], 0, ',', '.'); ?></p>
            <p><strong>Comentários Feitos:</strong> <?php echo $stats['total_comentarios']; ?></p>
        </div>

        <h3>🏆 Minhas Conquistas (Badges)</h3>
        <div class="card">
            <?php
            if ($resultado_badges->num_rows > 0) {
                while ($badge = $resultado_badges->fetch_assoc()) {
                    echo "<div class='badge-item' title='" . htmlspecialchars($badge['descricao']) . "'>";
                    echo "<span>" . htmlspecialchars($badge['icone']) . "</span>" . htmlspecialchars($badge['nome']);
                    echo "</div>";
                }
            } else {
                echo "<p>Nenhuma badge conquistada ainda. Comece a publicar e interagir!</p>";
            }
            ?>
        </div>
    </main>
    <footer><p>&copy; 2025 Abismo de Letras.</p></footer>
</body>
</html>
<?php $conn->close(); ?>