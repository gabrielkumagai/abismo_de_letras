<?php
include 'conexao.php';
// Garante sessão
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}

// Busca modelos (usando apenas colunas existentes)
$sql = "SELECT titulo, tema, texto_modelo, nota 
        FROM modelos_nota_mil 
        ORDER BY nota DESC";

$resultado = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acervo de Redações - Abismo de Letras</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Playfair+Display:wght@400;700&family=Lato:wght@300;400;700&family=Merriweather:wght@300;400;700&display=swap" rel="stylesheet">
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
            background-color: var(--cream);
            color: var(--text-dark);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        h1, h2, h3 { font-family: 'Playfair Display', serif; }
        .brand-font { font-family: 'Great Vibes', cursive; }

        /* --- Hero Section --- */
        .page-header {
            background: linear-gradient(rgba(45, 56, 38, 0.9), rgba(45, 56, 38, 0.8)), url('https://images.unsplash.com/photo-1455390582262-044cdead277a?q=80&w=2000&auto=format&fit=crop');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            padding: 140px 0 80px;
            color: white;
            text-align: center;
            margin-bottom: 50px;
        }
        
        .header-badge {
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid var(--gold);
            color: var(--gold);
            padding: 6px 20px; border-radius: 50px;
            font-size: 0.85rem; text-transform: uppercase; letter-spacing: 2px;
            margin-bottom: 20px; display: inline-block;
            backdrop-filter: blur(5px);
        }

        /* --- Cards de Modelo --- */
        .essay-card {
            background: white;
            border-radius: 15px;
            border: none;
            box-shadow: 0 10px 20px rgba(0,0,0,0.05);
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            height: 100%;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            position: relative;
        }

        .essay-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.12);
        }

        .essay-card::before {
            content: ''; position: absolute; top: 0; left: 0; width: 5px; height: 100%;
            background: var(--gold);
        }

        .card-body { padding: 30px 25px; flex-grow: 1; }

        .essay-theme {
            font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px;
            color: #888; font-weight: 700; margin-bottom: 10px; display: block;
        }

        .essay-title {
            font-family: 'Playfair Display', serif; font-weight: 700;
            color: var(--dark-green); margin-bottom: 15px; font-size: 1.3rem;
            line-height: 1.4;
        }

        .score-badge {
            background: rgba(46, 204, 113, 0.15); color: #27ae60;
            padding: 5px 12px; border-radius: 8px; font-weight: 800; font-size: 0.9rem;
            display: inline-flex; align-items: center; gap: 5px;
        }

        .btn-read-essay {
            width: 100%; padding: 14px; border: none;
            background: #f8f9fa; color: var(--dark-green);
            font-weight: 700; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 1px;
            transition: 0.3s; margin-top: auto;
        }
        .btn-read-essay:hover { background: var(--primary-green); color: white; }

        /* --- Modal de Leitura (Papel Digital) --- */
        .modal-content {
            border-radius: 15px; overflow: hidden; border: none;
        }
        .modal-header {
            background: var(--dark-green); color: white; padding: 20px 30px;
        }
        .modal-title { font-family: 'Playfair Display', serif; }
        .btn-close-white { filter: invert(1); }

        .modal-body {
            background-color: #fffdf7; /* Tom de papel levemente amarelado */
            padding: 40px 30px;
            color: #333;
        }

        .paper-content {
            font-family: 'Merriweather', serif; /* Fonte de leitura acadêmica */
            font-size: 1.1rem;
            line-height: 1.8;
            white-space: pre-wrap; /* Preserva parágrafos do banco */
            text-align: justify;
        }

                footer { background: #222; color: #aaa; padding: 60px 0 20px; }
        .footer-heading { color: white; font-family: 'Great Vibes'; font-size: 2rem; margin-bottom: 20px; }
    </style>
</head>
<body>

    <?php include 'header.php'; ?>

    <header class="page-header">
        <div class="container" data-aos="fade-down">
            <span class="header-badge">Acervo Premium</span>
            <h1 class="display-4 fw-bold">Modelos de Excelência</h1>
            <p class="lead text-white-50 mx-auto" style="max-width: 700px;">
                Analise a estrutura, coesão e argumentação de redações que atingiram a pontuação máxima. Use como base para construir seu próprio estilo.
            </p>
            <a href="enem.php" class="btn btn-outline-light rounded-pill mt-4 px-4 btn-sm">
                <i class="fas fa-arrow-left me-2"></i> Voltar ao Laboratório
            </a>
        </div>
    </header>

    <main class="container mb-5">
        
        <?php if ($resultado && $resultado->num_rows > 0): ?>
            
            <div class="row g-4">
                <?php 
                $count = 0;
                while ($modelo = $resultado->fetch_assoc()): 
                    $count++;
                    $modalId = "modalEssay" . $count;
                    
                    // Valores padrão
                    $autor = "Estudante Exemplar"; 
                    $ano = "2024";
                ?>
                
                <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="<?php echo $count * 50; ?>">
                    <div class="essay-card">
                        <div class="card-body">
                            <span class="essay-theme">
                                <i class="fas fa-tag me-1"></i> <?php echo htmlspecialchars($modelo['tema']); ?>
                            </span>
                            
                            <h3 class="essay-title"><?php echo htmlspecialchars($modelo['titulo']); ?></h3>
                            
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="score-badge">
                                    <i class="fas fa-star"></i> <?php echo htmlspecialchars($modelo['nota']); ?>
                                </span>
                                <small class="text-muted text-uppercase" style="font-size: 0.7rem; letter-spacing: 1px;">
                                    ENEM <?php echo htmlspecialchars($ano); ?>
                                </small>
                            </div>
                            
                            <p class="text-muted small mb-0">
                                Escrito por: <strong><?php echo htmlspecialchars($autor); ?></strong>
                            </p>
                        </div>
                        
                        <button type="button" class="btn-read-essay" data-bs-toggle="modal" data-bs-target="#<?php echo $modalId; ?>">
                            Ler Análise Completa <i class="fas fa-book-open ms-2"></i>
                        </button>
                    </div>
                </div>

                <div class="modal fade" id="<?php echo $modalId; ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header">
                                <div>
                                    <h5 class="modal-title fw-bold text-white mb-1"><?php echo htmlspecialchars($modelo['titulo']); ?></h5>
                                    <small class="text-white-50 text-uppercase letter-spacing-1">Tema: <?php echo htmlspecialchars($modelo['tema']); ?></small>
                                </div>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="paper-content">
                                    <?php echo htmlspecialchars($modelo['texto_modelo']); ?>
                                </div>
                                
                                <div class="mt-5 p-3 bg-light rounded border border-warning">
                                    <h6 class="fw-bold text-warning mb-2"><i class="fas fa-lightbulb me-2"></i>Dica de Estudo:</h6>
                                    <p class="small text-muted mb-0">
                                        Observe como o autor utiliza os conectivos para ligar os parágrafos e como a proposta de intervenção (último parágrafo) resolve os problemas levantados na tese.
                                    </p>
                                </div>
                            </div>
                            <div class="modal-footer bg-light">
                                <button type="button" class="btn btn-secondary rounded-pill btn-sm" data-bs-dismiss="modal">Fechar</button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php endwhile; ?>
            </div>

        <?php else: ?>
            
            <div class="text-center py-5" data-aos="zoom-in">
                <div class="opacity-50 mb-3">
                    <i class="fas fa-folder-open fa-4x text-muted"></i>
                </div>
                <h3 class="text-muted fw-light">Nenhum modelo encontrado no momento.</h3>
                <p class="text-muted">Nosso banco de dados está sendo atualizado. Volte em breve!</p>
                <a href="enem.php" class="btn btn-primary rounded-pill px-4" style="background-color: var(--primary-green); border:none;">Voltar</a>
            </div>

        <?php endif; ?>

    </main>

  <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="footer-heading">Abismo de Letras</div>
                    <p class="small">Projeto de TCC desenvolvido na Etec Monsenhor Antonio Magliano. Incentivando a leitura e escrita colaborativa.</p>
                </div>
                <div class="col-md-4 mb-4">
                    <h5 class="text-white mb-3">Links Rápidos</h5>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-decoration-none text-secondary">Sobre Nós</a></li>
                        <li><a href="enem.php" class="text-decoration-none text-secondary">Material ENEM</a></li>
                        <li><a href="#" class="text-decoration-none text-secondary">Termos de Uso</a></li>
                    </ul>
                </div>
                <div class="col-md-4 mb-4 text-md-end">
                    <h5 class="text-white mb-3">Contato</h5>
                    <p class="small"><i class="fas fa-envelope me-2"></i> contato@abismodelatras.com</p>
                    <div class="mt-3">
                        <a href="#" class="text-white me-3"><i class="fab fa-instagram fa-lg"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-linkedin fa-lg"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-github fa-lg"></i></a>
                    </div>
                </div>
            </div>
            <hr class="border-secondary mt-4">
            <p class="text-center small mb-0">&copy; 2025 Abismo de Letras. Todos os direitos reservados.</p>
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