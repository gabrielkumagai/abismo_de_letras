<?php
include 'conexao.php';
// Garante sessão
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$id_historia = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
$mensagem_comentario = "";
$mensagem_exclusao = "";
$usuario_logado_id = $_SESSION['id_usuario'] ?? 0;

if ($id_historia === 0) {
    header('Location: historias.php');
    exit;
}

// -----------------------------------------------------
// Lógica para Exclusão (Soft Delete)
// -----------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['excluir_historia'])) {
    if ($usuario_logado_id > 0) {
        $motivo = $conn->real_escape_string($_POST['motivo_exclusao']);
        $sql_delete = "UPDATE historias SET status_historia = 'deletado', motivo_exclusao = ?, acesso = 'restrito' WHERE id_historia = ? AND id_autor = ?";
        $stmt_delete = $conn->prepare($sql_delete);
        $stmt_delete->bind_param("sii", $motivo, $id_historia, $usuario_logado_id);

        if ($stmt_delete->execute() && $stmt_delete->affected_rows > 0) {
            header("Location: ver_historia.php?id=" . $id_historia . "&status=deleted");
            exit;
        } else {
            $mensagem_exclusao = "Erro ao tentar excluir. Permissão negada.";
        }
        $stmt_delete->close();
    }
}

// -----------------------------------------------------
// Processamento de Comentário
// -----------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['comentar'])) {
    if ($usuario_logado_id > 0) {
        $texto_comentario = $conn->real_escape_string($_POST['comentario_texto']);
        $sql_comment = "INSERT INTO interacoes (id_historia, id_usuario, texto) VALUES (?, ?, ?)";
        $stmt_comment = $conn->prepare($sql_comment);
        $stmt_comment->bind_param("iis", $id_historia, $usuario_logado_id, $texto_comentario);
        
        if ($stmt_comment->execute()) {
            $mensagem_comentario = "Comentário enviado!";
            // (Lógica de Badge omitida para brevidade, mas pode ser mantida)
        }
        $stmt_comment->close();
    }
}

if (isset($_GET['status']) && $_GET['status'] == 'deleted') {
    $mensagem_exclusao = "História encerrada com sucesso.";
}

// -----------------------------------------------------
// Busca Dados
// -----------------------------------------------------
$sql_historia = "SELECT h.titulo, h.conteudo, h.id_historia_original, u.nome AS autor, u.foto_perfil AS autor_foto, h.id_autor, h.capa_imagem, g.nome AS genero_nome, h.status_historia, h.motivo_exclusao, h.data_publicacao 
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
    header('Location: historias.php'); // Redireciona se não existir ou estiver deletada (e não for o dono)
    exit;
}

// Busca Versões
$sql_versoes = "SELECT h.id_historia, h.titulo, u.nome AS autor, h.data_publicacao FROM historias h JOIN usuarios u ON h.id_autor = u.id_usuario WHERE h.id_historia_original = ? AND h.acesso = 'publico' AND h.status_historia = 'ativo' ORDER BY h.data_publicacao DESC";
$stmt_versoes = $conn->prepare($sql_versoes);
$stmt_versoes->bind_param("i", $id_historia);
$stmt_versoes->execute();
$resultado_versoes = $stmt_versoes->get_result();
$stmt_versoes->close();

// Busca Comentários
$sql_comments = "SELECT i.texto, u.nome AS autor_comentario, u.foto_perfil AS autor_foto, i.data_interacao FROM interacoes i JOIN usuarios u ON i.id_usuario = u.id_usuario WHERE i.id_historia = ? ORDER BY i.data_interacao DESC";
$stmt_comments = $conn->prepare($sql_comments);
$stmt_comments->bind_param("i", $id_historia);
$stmt_comments->execute();
$resultado_comments = $stmt_comments->get_result();
$stmt_comments->close();

// Fallbacks Visuais
$capa_url = !empty($historia_data['capa_imagem']) ? $historia_data['capa_imagem'] : 'https://placehold.co/400x600/e0e0e0/888888?text=Sem+Capa';
$autor_foto_url = !empty($historia_data['autor_foto']) ? $historia_data['autor_foto'] : 'https://cdn-icons-png.flaticon.com/512/3135/3135715.png';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($historia_data['titulo']); ?> - Leitura</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Playfair+Display:wght@400;700&family=Merriweather:wght@300;400;700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        :root {
            --primary-green: #7d8c66;
            --dark-green: #4a5d3f;
            --cream: #f9f7f2;
            --gold: #d4af37;
            --text-dark: #2c3e50;
            --paper-color: #fdfbf7;
        }

        body {
            font-family: 'Lato', sans-serif;
            background-color: var(--cream);
            color: var(--text-dark);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        h1, h2, h3 { font-family: 'Playfair Display', serif; }
        
        /* --- LAYOUT DE LEITURA --- */
        .reading-container {
            max-width: 1100px;
            margin: 100px auto 60px;
            padding: 0 20px;
        }

        /* Sidebar com Info do Livro (Sticky) */
        .book-sidebar {
            position: sticky;
            top: 100px;
        }

        .book-cover-large {
            width: 100%;
            border-radius: 10px;
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
            margin-bottom: 20px;
            border: 5px solid white;
        }

        .author-card {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            text-align: center;
            margin-bottom: 20px;
        }
        
        .author-avatar-small {
            width: 60px; height: 60px; border-radius: 50%;
            object-fit: cover; border: 2px solid var(--gold);
            margin-bottom: 10px;
        }

        .btn-action {
            width: 100%; margin-bottom: 10px;
            border-radius: 50px; font-weight: bold;
            padding: 10px; transition: 0.3s;
        }
        
        .btn-continue {
            background: linear-gradient(135deg, var(--dark-green), var(--primary-green));
            color: white; border: none;
        }
        .btn-continue:hover { transform: translateY(-2px); color: white; box-shadow: 0 5px 15px rgba(0,0,0,0.2); }

        /* Área de Texto Principal (Papel) */
        .reading-paper {
            background: var(--paper-color);
            padding: 50px 60px;
            border-radius: 5px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            font-family: 'Merriweather', serif; /* Fonte de Livro */
            font-size: 1.15rem;
            line-height: 1.9;
            color: #333;
            min-height: 80vh;
            border-left: 5px solid var(--gold);
        }

        .story-title {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem; margin-bottom: 5px;
            color: var(--dark-green); font-weight: 700;
        }
        
        .story-meta {
            color: #888; font-family: 'Lato', sans-serif;
            font-size: 0.9rem; margin-bottom: 40px;
            border-bottom: 1px solid #eee; padding-bottom: 20px;
        }

        .story-content { white-space: pre-wrap; text-align: justify; }

        /* Mensagens de Alerta */
        .alert-custom { border-radius: 10px; border: none; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }

        /* Árvore de Versões */
        .versions-section { margin-top: 60px; padding-top: 40px; border-top: 1px solid #ddd; }
        .version-card {
            background: white; border-radius: 10px; padding: 15px;
            margin-bottom: 15px; border-left: 4px solid var(--primary-green);
            transition: 0.3s; text-decoration: none; color: inherit; display: block;
        }
        .version-card:hover { transform: translateX(5px); background: #f9f9f9; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }

        /* Comentários */
        .comments-section { background: white; padding: 40px; border-radius: 15px; margin-top: 40px; }
        .comment-item { border-bottom: 1px solid #f0f0f0; padding: 20px 0; }
        .comment-item:last-child { border-bottom: none; }
        .comment-avatar { width: 40px; height: 40px; border-radius: 50%; margin-right: 15px; object-fit: cover; }
        
        footer { margin-top: auto; background: #222; color: #aaa; padding: 50px 0; text-align: center; }

        /* Responsividade */
        @media (max-width: 992px) {
            .book-sidebar { position: static; margin-bottom: 40px; }
            .reading-paper { padding: 30px 20px; }
        }
    </style>
</head>
<body>

    <?php include 'header.php'; ?>

    <main class="reading-container">
        
        <?php if (!empty($mensagem_exclusao)): ?>
            <div class="alert alert-danger alert-custom mb-4" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i> <?php echo $mensagem_exclusao; ?>
            </div>
        <?php endif; ?>

        <div class="row">
            
            <div class="col-lg-4">
                <div class="book-sidebar">
                    <img src="<?php echo htmlspecialchars($capa_url); ?>" alt="Capa" class="book-cover-large" data-aos="zoom-in">
                    
                    <div class="author-card" data-aos="fade-up">
                        <img src="<?php echo htmlspecialchars($autor_foto_url); ?>" class="author-avatar-small" alt="Autor">
                        <h5 class="fw-bold mb-0"><?php echo htmlspecialchars($historia_data['autor']); ?></h5>
                        <small class="text-muted">Autor Original</small>
                        <hr>
                        <div class="d-flex justify-content-between text-start small">
                            <span><i class="fas fa-tag text-success"></i> Gênero:</span>
                            <span class="fw-bold"><?php echo htmlspecialchars($historia_data['genero_nome'] ?? 'Geral'); ?></span>
                        </div>
                        <div class="d-flex justify-content-between text-start small mt-2">
                            <span><i class="far fa-calendar-alt text-success"></i> Data:</span>
                            <span><?php echo date('d/m/Y', strtotime($historia_data['data_publicacao'])); ?></span>
                        </div>
                    </div>

                    <?php if (isset($_SESSION['id_usuario'])): ?>
                        <div data-aos="fade-up" data-aos-delay="100">
                            <a href='publicar.php?continuar=<?php echo $id_historia; ?>' class='btn btn-action btn-continue'>
                                <i class="fas fa-code-branch me-2"></i> Criar Ramificação
                            </a>
                            
                            <?php if ($historia_data['id_autor'] == $usuario_logado_id && $historia_data['status_historia'] == 'ativo'): ?>
                                <button type="button" class="btn btn-action btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                    <i class="fas fa-trash-alt me-2"></i> Encerrar História
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-lg-8">
                
                <article class="reading-paper" data-aos="fade-left">
                    <?php if ($historia_data['status_historia'] == 'deletado'): ?>
                        <div class="alert alert-danger mb-4">
                            <strong><i class="fas fa-lock me-2"></i> História Encerrada</strong>
                            <p class="mb-0 mt-1 small">Motivo: <?php echo htmlspecialchars($historia_data['motivo_exclusao']); ?></p>
                        </div>
                    <?php endif; ?>

                    <h1 class="story-title"><?php echo htmlspecialchars($historia_data['titulo']); ?></h1>
                    
                    <div class="story-meta">
                        <?php if ($historia_data['id_historia_original'] !== NULL): ?>
                            <span class="badge bg-warning text-dark mb-2"><i class="fas fa-code-branch"></i> Versão Alternativa</span>
                            <br>
                            Baseado em uma obra original. <a href="ver_historia.php?id=<?php echo $historia_data['id_historia_original']; ?>" class="text-success fw-bold">Ler Raiz</a>
                        <?php else: ?>
                            <span class="badge bg-success mb-2"><i class="fas fa-seedling"></i> História Original</span>
                        <?php endif; ?>
                    </div>

                    <div class="story-content">
                        <?php echo nl2br(htmlspecialchars($historia_data['conteudo'])); ?>
                    </div>
                </article>

                <div class="versions-section">
                    <h4 class="mb-4 brand-font"><i class="fas fa-project-diagram me-2"></i> O Multiverso desta História</h4>
                    
                    <?php if ($resultado_versoes->num_rows > 0): ?>
                        <div class="row">
                            <?php while ($versao = $resultado_versoes->fetch_assoc()): ?>
                                <div class="col-md-6">
                                    <a href="ver_historia.php?id=<?php echo $versao['id_historia']; ?>" class="version-card">
                                        <h6 class="fw-bold mb-1"><?php echo htmlspecialchars($versao['titulo']); ?></h6>
                                        <small class="text-muted">por <?php echo htmlspecialchars($versao['autor']); ?></small>
                                    </a>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted small fst-italic">Esta história ainda não possui versões alternativas. Seja o primeiro a ramificar!</p>
                    <?php endif; ?>
                </div>

                <div class="comments-section">
                    <h4 class="mb-4 fw-bold"><i class="far fa-comments me-2"></i> Discussão</h4>

                    <?php if (!empty($mensagem_comentario)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo $mensagem_comentario; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['id_usuario']) && $historia_data['status_historia'] == 'ativo'): ?>
                        <form method="POST" action="ver_historia.php?id=<?php echo $id_historia; ?>" class="mb-5">
                            <div class="form-floating mb-3">
                                <textarea class="form-control" placeholder="Deixe seu comentário" id="comentario_texto" name="comentario_texto" style="height: 100px" required></textarea>
                                <label for="comentario_texto">Escreva sua crítica ou elogio...</label>
                            </div>
                            <input type="hidden" name="comentar" value="1">
                            <button type="submit" class="btn btn-dark rounded-pill px-4">Publicar Comentário</button>
                        </form>
                    <?php endif; ?>

                    <div class="comment-list">
                        <?php if ($resultado_comments->num_rows > 0): ?>
                            <?php while ($comment = $resultado_comments->fetch_assoc()): 
                                $foto_com = !empty($comment['autor_foto']) ? $comment['autor_foto'] : 'https://cdn-icons-png.flaticon.com/512/3135/3135715.png';
                            ?>
                                <div class="comment-item d-flex">
                                    <img src="<?php echo htmlspecialchars($foto_com); ?>" class="comment-avatar" alt="Avatar">
                                    <div>
                                        <h6 class="fw-bold mb-0"><?php echo htmlspecialchars($comment['autor_comentario']); ?></h6>
                                        <small class="text-muted d-block mb-2"><?php echo date('d/m/Y H:i', strtotime($comment['data_interacao'])); ?></small>
                                        <p class="mb-0 text-secondary"><?php echo nl2br(htmlspecialchars($comment['texto'])); ?></p>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p class="text-muted text-center py-3">Nenhum comentário ainda.</p>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <footer>
        <div class="container text-center">
            <p class="mb-1">&copy; 2025 Abismo de Letras.</p>
            <p class="small opacity-75">Projeto de TCC - Etec Monsenhor Antonio Magliano.</p>
        </div>
    </footer>

    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fw-bold"><i class="fas fa-exclamation-circle me-2"></i> Encerrar História</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Tem certeza? Isso irá impedir novos comentários e marcará a história como encerrada. Esta ação é irreversível.</p>
                    <form method="POST" action="ver_historia.php?id=<?php echo $id_historia; ?>">
                        <div class="mb-3">
                            <label for="motivo" class="form-label fw-bold">Motivo do Encerramento:</label>
                            <textarea class="form-control" id="motivo" name="motivo_exclusao" rows="3" required placeholder="Explique brevemente aos leitores..."></textarea>
                        </div>
                        <input type="hidden" name="excluir_historia" value="1">
                        <div class="d-grid">
                            <button type="submit" class="btn btn-danger fw-bold">Confirmar Encerramento</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({ duration: 800, once: true });
    </script>
</body>
</html>
<?php $conn->close(); ?>