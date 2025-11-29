<?php
include 'conexao.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}

$id_redacao = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_redacao === 0) {
    header('Location: pratica_enem.php');
    exit;
}

// Busca a Redação
$sql_base = "SELECT r.titulo, r.tema, r.texto, u.nome AS autor, r.id_usuario AS id_autor, r.tipo_contribuicao, r.data_salva, u.foto_perfil
             FROM redacoes_enem r 
             JOIN usuarios u ON r.id_usuario = u.id_usuario 
             WHERE r.id_redacao = ?";
$stmt_base = $conn->prepare($sql_base);
$stmt_base->bind_param("i", $id_redacao);
$stmt_base->execute();
$redacao_data = $stmt_base->get_result()->fetch_assoc();
$stmt_base->close();

if (!$redacao_data) {
    header('Location: pratica_enem.php');
    exit;
}

// Busca Contribuições
$sql_contribuicoes = "SELECT r.id_redacao, r.titulo, u.nome AS autor, r.tipo_contribuicao, r.data_salva, u.foto_perfil 
                      FROM redacoes_enem r 
                      JOIN usuarios u ON r.id_usuario = u.id_usuario 
                      WHERE r.id_redacao_original = ?
                      ORDER BY r.data_salva DESC";
$stmt_contribuicoes = $conn->prepare($sql_contribuicoes);
$stmt_contribuicoes->bind_param("i", $id_redacao);
$stmt_contribuicoes->execute();
$resultado_contribuicoes = $stmt_contribuicoes->get_result();
$stmt_contribuicoes->close();

$foto_autor = !empty($redacao_data['foto_perfil']) ? $redacao_data['foto_perfil'] : 'https://cdn-icons-png.flaticon.com/512/3135/3135715.png';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revisão: <?php echo htmlspecialchars($redacao_data['titulo']); ?> - Abismo de Letras</title>
    
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
        }

        body {
            font-family: 'Lato', sans-serif;
            background-color: #f0f2f5;
            color: var(--text-dark);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        h1, h2, h3 { font-family: 'Playfair Display', serif; }

        /* --- LAYOUT DE REVISÃO --- */
        .review-container {
            max-width: 900px;
            margin: 120px auto 60px;
            padding: 0 20px;
        }

        /* Cabeçalho do Texto */
        .essay-header-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            border-left: 5px solid var(--gold);
        }

        .theme-badge {
            font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px;
            color: #888; font-weight: 700; margin-bottom: 10px; display: block;
        }

        /* Papel de Redação */
        .essay-paper {
            background: #fff;
            padding: 60px;
            border-radius: 5px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.08);
            font-family: 'Merriweather', serif;
            font-size: 1.15rem;
            line-height: 2; /* Espaçamento duplo para facilitar leitura/correção */
            color: #333;
            margin-bottom: 40px;
            position: relative;
        }
        /* Linhas de caderno opcionais */
        .essay-paper::before {
            content: ''; position: absolute; top: 0; left: 40px; bottom: 0; width: 2px;
            background: rgba(255, 0, 0, 0.1); /* Margem vermelha suave */
        }

        .author-meta {
            display: flex; align-items: center; gap: 15px; margin-top: 30px;
            padding-top: 20px; border-top: 1px solid #eee;
        }
        .author-img-small {
            width: 50px; height: 50px; border-radius: 50%; object-fit: cover;
            border: 2px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        /* Botões de Ação Fixos (Mobile friendly) */
        .action-bar {
            position: sticky; bottom: 20px; z-index: 100;
            background: rgba(255,255,255,0.9); backdrop-filter: blur(10px);
            padding: 15px; border-radius: 50px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
            display: flex; justify-content: center; gap: 15px;
            max-width: 600px; margin: 0 auto; border: 1px solid rgba(0,0,0,0.05);
        }

        .btn-action {
            border-radius: 50px; font-weight: bold; padding: 10px 25px;
            transition: 0.3s; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 1px;
        }
        .btn-correct { background: var(--primary-green); color: white; border: none; }
        .btn-correct:hover { background: var(--dark-green); color: white; transform: translateY(-2px); }
        
        .btn-back { background: white; color: #666; border: 1px solid #ddd; }
        .btn-back:hover { background: #f8f9fa; color: #333; }

        /* Timeline de Contribuições */
        .timeline-section { margin-top: 60px; padding-top: 40px; border-top: 1px solid #ddd; }
        
        .timeline-item {
            display: flex; gap: 20px; margin-bottom: 30px; position: relative;
        }
        .timeline-item::before {
            content: ''; position: absolute; left: 24px; top: 50px; bottom: -30px;
            width: 2px; background: #e0e0e0; z-index: 0;
        }
        .timeline-item:last-child::before { display: none; }

        .timeline-icon {
            width: 50px; height: 50px; border-radius: 50%;
            background: white; border: 2px solid var(--gold);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem; color: var(--gold); z-index: 1; flex-shrink: 0;
        }
        
        .timeline-content {
            background: white; border-radius: 15px; padding: 20px;
            flex-grow: 1; box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: 0.3s; border: 1px solid rgba(0,0,0,0.02);
        }
        .timeline-content:hover { transform: translateX(5px); border-color: var(--gold); }

        .badge-type {
            font-size: 0.7rem; font-weight: 700; text-transform: uppercase;
            padding: 4px 10px; border-radius: 4px;
        }
        .type-correction { background: rgba(46, 204, 113, 0.1); color: #27ae60; }
        .type-continue { background: rgba(52, 152, 219, 0.1); color: #3498db; }

        footer { margin-top: auto; background: #222; color: #aaa; padding: 40px 0; text-align: center; }
    </style>
</head>
<body>

    <?php include 'header.php'; ?>

    <main class="review-container">
        
        <div class="essay-header-card" data-aos="fade-down">
            <span class="theme-badge"><i class="fas fa-quote-left me-2"></i> Tema da Redação</span>
            <h2 class="fw-bold mb-3" style="color: var(--dark-green);"><?php echo htmlspecialchars($redacao_data['tema']); ?></h2>
            <div class="d-flex align-items-center gap-3 text-muted small">
                <span><i class="far fa-calendar-alt me-1"></i> <?php echo date('d/m/Y', strtotime($redacao_data['data_salva'])); ?></span>
                <span class="badge bg-light text-dark border">
                    <?php echo ucfirst($redacao_data['tipo_contribuicao']); ?>
                </span>
            </div>
        </div>

        <article class="essay-paper" data-aos="fade-up">
            <h3 class="fw-bold mb-4"><?php echo htmlspecialchars($redacao_data['titulo']); ?></h3>
            
            <div style="white-space: pre-wrap;">
                <?php echo htmlspecialchars($redacao_data['texto']); ?>
            </div>

            <div class="author-meta">
                <img src="<?php echo htmlspecialchars($foto_autor); ?>" class="author-img-small" alt="Autor">
                <div>
                    <strong class="d-block text-dark"><?php echo htmlspecialchars($redacao_data['autor']); ?></strong>
                    <span class="small text-muted">Autor do Texto</span>
                </div>
            </div>
        </article>

        <div class="action-bar" data-aos="fade-up" data-aos-offset="0">
            <a href="pratica_enem.php" class="btn btn-action btn-back">
                <i class="fas fa-arrow-left me-2"></i> Voltar
            </a>
            <a href="publicar_redacao.php?original_id=<?php echo $id_redacao; ?>" class="btn btn-action btn-correct">
                <i class="fas fa-pen-alt me-2"></i> Contribuir / Corrigir
            </a>
        </div>

        <div class="timeline-section">
            <h4 class="mb-4 fw-bold text-dark"><i class="fas fa-history me-2"></i> Histórico de Colaboração</h4>
            
            <?php if ($resultado_contribuicoes->num_rows > 0): ?>
                <?php while ($contrib = $resultado_contribuicoes->fetch_assoc()): 
                    $tipo_class = ($contrib['tipo_contribuicao'] == 'correcao_peer') ? 'type-correction' : 'type-continue';
                    $tipo_label = ($contrib['tipo_contribuicao'] == 'correcao_peer') ? 'Correção' : 'Continuação';
                    $foto_contrib = !empty($contrib['foto_perfil']) ? $contrib['foto_perfil'] : 'https://cdn-icons-png.flaticon.com/512/3135/3135715.png';
                ?>
                <div class="timeline-item" data-aos="fade-left">
                    <div class="timeline-icon">
                        <img src="<?php echo htmlspecialchars($foto_contrib); ?>" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                    </div>
                    <div class="timeline-content">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <span class="badge-type <?php echo $tipo_class; ?> mb-1 d-inline-block"><?php echo $tipo_label; ?></span>
                                <h6 class="fw-bold mb-0"><?php echo htmlspecialchars($contrib['titulo']); ?></h6>
                            </div>
                            <small class="text-muted"><?php echo date('d/m H:i', strtotime($contrib['data_salva'])); ?></small>
                        </div>
                        <p class="small text-muted mb-3">Colaboração de <strong><?php echo htmlspecialchars($contrib['autor']); ?></strong></p>
                        <a href="ver_redacao.php?id=<?php echo $contrib['id_redacao']; ?>" class="btn btn-sm btn-outline-dark rounded-pill">
                            Ver esta Versão <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="text-center py-4 text-muted bg-white rounded-4 shadow-sm">
                    <i class="far fa-comment-dots fa-2x mb-2 opacity-50"></i>
                    <p class="mb-0">Ainda não há contribuições para este texto. Seja o primeiro!</p>
                </div>
            <?php endif; ?>
        </div>

    </main>

    <footer>
        <div class="container text-center">
            <p class="mb-1">&copy; 2025 Abismo de Letras.</p>
            <p class="small opacity-75">Projeto de TCC - Etec Monsenhor Antonio Magliano.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({ duration: 800, once: true });
    </script>
</body>
</html>
<?php $conn->close(); ?>