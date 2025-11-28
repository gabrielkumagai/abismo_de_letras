<?php
include 'conexao.php';
session_start();

$id_historia = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
$mensagem_comentario = "";
$mensagem_exclusao = "";
$usuario_logado_id = $_SESSION['id_usuario'] ?? 0;

if ($id_historia === 0) {
    header('Location: historias.php');
    exit;
}

// -----------------------------------------------------
// Lógica para Exclusão da História (Soft Delete)
// -----------------------------------------------------
// (Mantida a lógica anterior)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['excluir_historia'])) {
    if ($usuario_logado_id > 0) {
        $motivo = $conn->real_escape_string($_POST['motivo_exclusao']);

        // Apenas o autor original pode excluir
        $sql_delete = "UPDATE historias SET status_historia = 'deletado', motivo_exclusao = ?, acesso = 'restrito' WHERE id_historia = ? AND id_autor = ?";
        $stmt_delete = $conn->prepare($sql_delete);
        $stmt_delete->bind_param("sii", $motivo, $id_historia, $usuario_logado_id);

        if ($stmt_delete->execute() && $stmt_delete->affected_rows > 0) {
            $mensagem_exclusao = "A história foi marcada como deletada com sucesso. Motivo registrado.";
            header("Location: ver_historia.php?id=" . $id_historia . "&status=deleted");
            exit;
        } else {
            $mensagem_exclusao = "Erro ao tentar excluir a história. Você é o autor original?";
        }
        $stmt_delete->close();
    } else {
        $mensagem_exclusao = "Você precisa estar logado para realizar esta ação.";
    }
}


// -----------------------------------------------------
// Processamento de Comentário (COM LÓGICA DE BADGE)
// -----------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['comentar'])) {
    if ($usuario_logado_id > 0) {
        $id_usuario_comentario = $usuario_logado_id;
        $texto_comentario = $conn->real_escape_string($_POST['comentario_texto']);

        $sql_comment = "INSERT INTO interacoes (id_historia, id_usuario, texto) VALUES (?, ?, ?)";
        $stmt_comment = $conn->prepare($sql_comment);
        $stmt_comment->bind_param("iis", $id_historia, $id_usuario_comentario, $texto_comentario);
        
        if ($stmt_comment->execute()) {
            $mensagem_comentario = "Comentário adicionado com sucesso!";

            // *** Lógica para a Badge "Crítico Engajado" (10+ Comentários) ***
            $sql_count_comments = "SELECT COUNT(*) FROM interacoes WHERE id_usuario = ?";
            $stmt_count = $conn->prepare($sql_count_comments);
            $stmt_count->bind_param("i", $id_usuario_comentario);
            $stmt_count->execute();
            $count_comments = $stmt_count->get_result()->fetch_row()[0];
            $stmt_count->close();

            if ($count_comments >= 10) {
                // Tenta inserir a badge. IGNORE evita erro se ela já existir.
                $sql_give_badge = "INSERT IGNORE INTO usuario_badge (id_usuario, id_badge) SELECT ?, id_badge FROM badges WHERE nome = 'Crítico Engajado'";
                $stmt_badge = $conn->prepare($sql_give_badge);
                $stmt_badge->bind_param("i", $id_usuario_comentario);
                $stmt_badge->execute();
                if ($stmt_badge->affected_rows > 0) {
                    $mensagem_comentario .= " 🎉 Nova Conquista: 'Crítico Engajado'!";
                }
                $stmt_badge->close();
            }
            // *** Fim da Lógica da Badge ***
        } else {
            $mensagem_comentario = "Erro ao adicionar comentário.";
        }
        $stmt_comment->close();
    } else {
        $mensagem_comentario = "Você precisa estar logado para comentar.";
    }
}

// Exibir mensagem após redirecionamento de exclusão
if (isset($_GET['status']) && $_GET['status'] == 'deleted') {
    $mensagem_exclusao = "A história foi marcada como deletada com sucesso. O motivo foi registrado para a comunidade.";
}


// -----------------------------------------------------
// Busca Dados da História (Original ou Versão)
// -----------------------------------------------------
$sql_historia = "SELECT h.titulo, h.conteudo, h.id_historia_original, u.nome AS autor, h.id_autor, h.capa_imagem, g.nome AS genero_nome, h.status_historia, h.motivo_exclusao 
                FROM historias h 
                JOIN usuarios u ON h.id_autor = u.id_usuario 
                LEFT JOIN generos g ON h.id_genero = g.id_genero
                WHERE h.id_historia = ?";
$stmt_historia = $conn->prepare($sql_historia);
$stmt_historia->bind_param("i", $id_historia);
$stmt_historia->execute();
$historia_data = $stmt_historia->get_result()->fetch_assoc();
$stmt_historia->close();

if (!$historia_data || $historia_data['status_historia'] == 'deletado' && $historia_data['id_autor'] != $usuario_logado_id) {
    // Redireciona se não for encontrada ou se estiver deletada e o usuário não for o autor
    header('Location: historias.php');
    exit;
}

// -----------------------------------------------------
// Busca as Versões Alternativas (Filhas)
// -----------------------------------------------------
$sql_versoes = "SELECT h.id_historia, h.titulo, u.nome AS autor FROM historias h JOIN usuarios u ON h.id_autor = u.id_usuario WHERE h.id_historia_original = ? AND h.acesso = 'publico' AND h.status_historia = 'ativo' ORDER BY h.data_publicacao DESC";
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
    <style>
        .historia-capa-visualizacao { max-width: 250px; height: auto; float: left; margin: 0 20px 20px 0; border: 1px solid #ccc; }
        .motivo-card { background-color: #fcebeb; color: #8b1a1a; padding: 15px; border-left: 5px solid #8b1a1a; margin-bottom: 20px; }
        #form-exclusao { background-color: #f7f7f7; padding: 20px; border-radius: 5px; }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <main class="container">
        <h2><?php echo htmlspecialchars($historia_data['titulo']); ?></h2>

        <?php if (!empty($mensagem_exclusao)): ?>
            <div class="alert-success"><?php echo $mensagem_exclusao; ?></div>
        <?php endif; ?>
        
        <?php if ($historia_data['id_historia_original'] !== NULL): ?>
            <p style="color: var(--cor-secundaria); font-weight: bold;">[Versão Alternativa] Baseada em uma narrativa anterior. <a href="ver_historia.php?id=<?php echo $historia_data['id_historia_original']; ?>">Ver Raiz.</a></p>
        <?php endif; ?>

        <?php if ($historia_data['status_historia'] == 'deletado'): ?>
            <div class="motivo-card">
                <p style="font-weight: bold;">⚠️ História Encerrada pelo Autor ⚠️</p>
                <p>O autor decidiu encerrar esta narrativa. O motivo registrado foi:</p>
                <p><em>"<?php echo nl2br(htmlspecialchars($historia_data['motivo_exclusao'])); ?>"</em></p>
            </div>
        <?php endif; ?>

        <div class="card" style="border-left: 5px solid var(--cor-secundaria); overflow: auto;">
            <img src="<?php echo htmlspecialchars($historia_data['capa_imagem']); ?>" alt="Capa da História" class="historia-capa-visualizacao">
            
            <p>Gênero: **<?php echo htmlspecialchars($historia_data['genero_nome'] ?? 'Não Definido'); ?>**</p>
            
            <p>Autor: **<a href="ver_perfil.php?id=<?php echo $historia_data['id_autor']; ?>"><?php echo htmlspecialchars($historia_data['autor']); ?></a>**</p>
            
            <hr style="clear: both;">
            <div style="white-space: pre-wrap; line-height: 1.6;">
                <?php echo htmlspecialchars($historia_data['conteudo']); ?>
            </div>
        </div>
        
        <?php if (isset($_SESSION['id_usuario'])): ?>
            <a href='publicar.php?continuar=<?php echo $id_historia; ?>' class='btn-cta'>✍️ Criar Minha Versão Alternativa / Continuar</a>
            
            <?php if ($historia_data['id_autor'] == $usuario_logado_id && $historia_data['id_historia_original'] === NULL && $historia_data['status_historia'] == 'ativo'): ?>
                <button onclick="document.getElementById('form-exclusao').style.display='block';" class="btn" style="background-color: darkred; margin-left: 10px;">
                    ❌ Encerrar História
                </button>
            <?php endif; ?>

        <?php endif; ?>

        <div id="form-exclusao" style="display: none; margin-top: 20px;">
            <h3>Motivo do Encerramento</h3>
            <p>Para encerrar esta história permanentemente e informar a comunidade, por favor, deixe um comentário abaixo:</p>
            <form method="POST" action="ver_historia.php?id=<?php echo $id_historia; ?>">
                <label for="motivo_exclusao">Motivo (Obrigatório):</label>
                <textarea id="motivo_exclusao" name="motivo_exclusao" rows="5" required></textarea>
                
                <input type="hidden" name="excluir_historia" value="1">
                <button type="submit" class="btn" style="background-color: darkred;">Confirmar Encerramento e Publicar Motivo</button>
            </form>
        </div>


        <hr style="clear: both;">

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

        <?php if (isset($_SESSION['id_usuario']) && $historia_data['status_historia'] == 'ativo'): ?>
            <div class="card">
                <form method="POST" action="ver_historia.php?id=<?php echo $id_historia; ?>">
                    <label for="comentario_texto">Seu Feedback:</label>
                    <textarea id="comentario_texto" name="comentario_texto" rows="4" required></textarea>
                    
                    <input type="hidden" name="comentar" value="1">
                    <button type="submit" class="btn">Enviar Comentário</button>
                </form>
            </div>
        <?php else: ?>
            <p>Faça <a href="login.php">login</a> para deixar sua crítica construtiva. (Comentários são bloqueados em histórias encerradas.)</p>
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