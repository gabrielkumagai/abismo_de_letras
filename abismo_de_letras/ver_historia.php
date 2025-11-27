<?php
include 'conexao.php';
session_start();

$id_historia = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
$mensagem_comentario = "";

if ($id_historia === 0) {
    header('Location: historias.php');
    exit;
}

// -----------------------------------------------------
// Processamento de Comentário
// -----------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['comentar'])) {
    if (isset($_SESSION['id_usuario'])) {
        $id_usuario_comentario = $_SESSION['id_usuario'];
        $texto_comentario = $conn->real_escape_string($_POST['comentario_texto']);

        $sql_comment = "INSERT INTO interacoes (id_historia, id_usuario, texto) VALUES (?, ?, ?)";
        $stmt_comment = $conn->prepare($sql_comment);
        $stmt_comment->bind_param("iis", $id_historia, $id_usuario_comentario, $texto_comentario);
        
        if ($stmt_comment->execute()) {
            $mensagem_comentario = "Comentário adicionado com sucesso!";
        } else {
            $mensagem_comentario = "Erro ao adicionar comentário.";
        }
        $stmt_comment->close();
    } else {
        $mensagem_comentario = "Você precisa estar logado para comentar.";
    }
}


// -----------------------------------------------------
// Busca Dados da História (Original ou Versão)
// -----------------------------------------------------
$sql_historia = "SELECT h.titulo, h.conteudo, h.id_historia_original, u.nome AS autor, h.id_autor FROM historias h JOIN usuarios u ON h.id_autor = u.id_usuario WHERE h.id_historia = ? AND h.acesso = 'publico'";
$stmt_historia = $conn->prepare($sql_historia);
$stmt_historia->bind_param("i", $id_historia);
$stmt_historia->execute();
$historia_data = $stmt_historia->get_result()->fetch_assoc();
$stmt_historia->close();

if (!$historia_data) {
    echo "<p>História não encontrada ou não é pública.</p>";
    exit;
}

// -----------------------------------------------------
// Busca as Versões Alternativas (Filhas)
// -----------------------------------------------------
$sql_versoes = "SELECT h.id_historia, h.titulo, u.nome AS autor FROM historias h JOIN usuarios u ON h.id_autor = u.id_usuario WHERE h.id_historia_original = ? AND h.acesso = 'publico' ORDER BY h.data_publicacao DESC";
$stmt_versoes = $conn->prepare($sql_versoes);
$stmt_versoes->bind_param("i", $id_historia);
$stmt_versoes->execute();
$resultado_versoes = $stmt_versoes->get_result();
$stmt_versoes->close();

// -----------------------------------------------------
// Busca Comentários
// -----------------------------------------------------
$sql_comments = "SELECT i.texto, u.nome AS autor_comentario FROM interacoes i JOIN usuarios u ON i.id_usuario = u.id_usuario WHERE i.id_historia = ? ORDER BY i.data_interacao DESC";
$stmt_comments = $conn->prepare($sql_comments);
$stmt_comments->bind_param("i", $id_historia);
$stmt_comments->execute();
$resultado_comments = $stmt_comments->get_result();
$stmt_comments->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($historia_data['titulo']); ?> - Abismo de Letras</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; // CORREÇÃO: Inclui o cabeçalho externo ?>
    
    <main class="container">
        <h2><?php echo htmlspecialchars($historia_data['titulo']); ?></h2>
        
        <?php if ($historia_data['id_historia_original'] !== NULL): ?>
            <p style="color: var(--cor-secundaria); font-weight: bold;">[Versão Alternativa] Baseada em uma narrativa anterior. <a href="ver_historia.php?id=<?php echo $historia_data['id_historia_original']; ?>">Ver Raiz.</a></p>
        <?php endif; ?>

        <div class="card" style="border-left: 5px solid var(--cor-secundaria);">
            <p>Autor: **<?php echo htmlspecialchars($historia_data['autor']); ?>**</p>
            <hr>
            <div style="white-space: pre-wrap; line-height: 1.6;">
                <?php echo htmlspecialchars($historia_data['conteudo']); ?>
            </div>
        </div>
        
        <?php if (isset($_SESSION['id_usuario'])): ?>
            <a href='publicar.php?continuar=<?php echo $id_historia; ?>' class='btn-cta'>✍️ Criar Minha Versão Alternativa / Continuar</a>
        <?php endif; ?>

        <hr>

        <h3>🔗 Ramificações desta Narrativa (O Abismo)</h3>
        <div class="arvore-container">
            <h4>Ponto de Partida: <?php echo htmlspecialchars($historia_data['titulo']); ?></h4>
            <?php
            if ($resultado_versoes->num_rows > 0) {
                while ($versao = $resultado_versoes->fetch_assoc()) {
                    echo "<div class='versao-item card'>";
                    echo "<p>Título: **" . htmlspecialchars($versao['titulo']) . "**</p>";
                    echo "<p>Autor: " . htmlspecialchars($versao['autor']) . "</p>";
                    echo "<a href='ver_historia.php?id=" . $versao['id_historia'] . "' class='btn' style='margin-top:0;'>Ler esta Versão</a>";
                    echo "</div>";
                }
            } else {
                echo "<p>Nenhuma ramificação/versão foi criada a partir desta história ainda.</p>";
            }
            ?>
        </div>

        <hr>

        <h3>💬 Crítica Construtiva (Comentários)</h3>
        <?php if (!empty($mensagem_comentario)): ?>
            <div class="alert-success"><?php echo $mensagem_comentario; ?></div>
        <?php endif; ?>

        <?php if (isset($_SESSION['id_usuario'])): ?>
            <div class="card">
                <form method="POST" action="ver_historia.php?id=<?php echo $id_historia; ?>">
                    <label for="comentario_texto">Seu Feedback:</label>
                    <textarea id="comentario_texto" name="comentario_texto" rows="4" required></textarea>
                    
                    <input type="hidden" name="comentar" value="1">
                    <button type="submit" class="btn">Enviar Comentário</button>
                </form>
            </div>
        <?php else: ?>
            <p>Faça <a href="login.php">login</a> para deixar sua crítica construtiva.</p>
        <?php endif; ?>

        <div id="lista-comentarios">
            <?php
            if ($resultado_comments->num_rows > 0) {
                while ($comment = $resultado_comments->fetch_assoc()) {
                    echo "<div class='card' style='margin-top: 10px; background-color: #f7f7f7;'>";
                    echo "<p style='font-weight: bold;'>**" . htmlspecialchars($comment['autor_comentario']) . " comentou:**</p>";
                    echo "<p>" . nl2br(htmlspecialchars($comment['texto'])) . "</p>";
                    echo "</div>";
                }
            } else {
                echo "<p>Seja o primeiro a comentar!</p>";
            }
            ?>
        </div>
    </main>
    <footer><p>&copy; 2025 Abismo de Letras.</p></footer>
</body>
</html>
<?php $conn->close(); ?>