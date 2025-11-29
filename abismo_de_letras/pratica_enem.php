<?php
include 'conexao.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}

// --- LÓGICA DE FILTROS ---
// Captura o filtro da URL (padrão: 'recentes')
$filtro_atual = isset($_GET['filtro']) ? $_GET['filtro'] : 'recentes';
$id_usuario_logado = $_SESSION['id_usuario'];

// Início da Query Base
$sql = "SELECT r.id_redacao, r.titulo, r.tema, r.data_salva, 
               u.nome AS autor, u.foto_perfil, u.tipo_usuario 
        FROM redacoes_enem r 
        JOIN usuarios u ON r.id_usuario = u.id_usuario 
        WHERE r.id_redacao_original IS NULL AND r.tipo_contribuicao = 'rascunho'";

// Aplica condições baseadas no filtro
switch ($filtro_atual) {
    case 'sem_correcao':
        // Busca redações cujo ID NÃO aparece como 'original' em nenhuma outra linha (ou seja, ninguém respondeu ainda)
        $sql .= " AND r.id_redacao NOT IN (SELECT DISTINCT id_redacao_original FROM redacoes_enem WHERE id_redacao_original IS NOT NULL)";
        $msg_vazio_titulo = "Tudo corrigido!";
        $msg_vazio_desc = "Incrível! Todos os rascunhos atuais já receberam alguma correção ou comentário.";
        break;

    case 'meus_temas':
        // Busca apenas as redações do usuário logado
        $sql .= " AND r.id_usuario = $id_usuario_logado";
        $msg_vazio_titulo = "Você ainda não publicou";
        $msg_vazio_desc = "Você ainda não enviou nenhum rascunho para o mural.";
        break;

    case 'recentes':
    default:
        // Padrão: mostra tudo
        $msg_vazio_titulo = "O Mural está vazio";
        $msg_vazio_desc = "Nenhum rascunho pendente no momento. Que tal começar o seu?";
        break;
}

// Ordenação Final
$sql .= " ORDER BY r.data_salva DESC";

$resultado = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Oficina de Redação - Abismo de Letras</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Playfair+Display:wght@400;700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        :root {
            --primary-green: #7d8c66;
            --dark-green: #4a5d3f;
            --cream: #f9f7f2;
            --gold: #d4af37;
            --text-dark: #2c3e50;
            --card-hover-shadow: 0 20px 40px rgba(0,0,0,0.1);
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

        /* --- HERO SECTION --- */
        .practice-hero {
            background: linear-gradient(rgba(45, 56, 38, 0.9), rgba(45, 56, 38, 0.8)), url('https://images.unsplash.com/photo-1517842645767-c639042777db?q=80&w=2000&auto=format&fit=crop');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            padding: 160px 0 120px;
            color: white;
            text-align: center;
            position: relative;
            margin-bottom: 60px;
        }

        .hero-icon-wrapper {
            display: inline-block;
            padding: 20px;
            border-radius: 50%;
            border: 2px solid rgba(255,255,255,0.2);
            background: rgba(255,255,255,0.05);
            backdrop-filter: blur(5px);
            margin-bottom: 25px;
            animation: pulse 3s infinite;
        }

        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.2); }
            70% { box-shadow: 0 0 0 15px rgba(255, 255, 255, 0); }
            100% { box-shadow: 0 0 0 0 rgba(255, 255, 255, 0); }
        }

        .btn-create-hero {
            background: var(--gold); color: white; border: none;
            padding: 15px 40px; border-radius: 50px; font-weight: bold;
            text-transform: uppercase; letter-spacing: 1px; transition: 0.3s;
            box-shadow: 0 10px 30px rgba(212, 175, 55, 0.4);
            font-size: 0.9rem; text-decoration: none; display: inline-block;
        }
        .btn-create-hero:hover { 
            background: white; color: var(--gold); transform: translateY(-5px); 
        }

        /* --- HOW IT WORKS --- */
        .process-steps {
            margin-top: -80px; position: relative; z-index: 10;
            margin-bottom: 60px;
        }
        .step-card {
            background: white; padding: 30px 20px; border-radius: 20px;
            box-shadow: 0 15px 30px rgba(0,0,0,0.05); text-align: center;
            transition: 0.3s; height: 100%; border-bottom: 4px solid transparent;
        }
        .step-card:hover {
            transform: translateY(-10px); border-bottom-color: var(--primary-green);
        }
        .step-number {
            background: var(--cream); color: var(--dark-green);
            width: 40px; height: 40px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-weight: bold; margin: 0 auto 15px; font-family: 'Playfair Display';
        }
        .step-icon { font-size: 2rem; color: var(--primary-green); margin-bottom: 15px; }

        /* --- FILTROS DE MURAL (Atualizado) --- */
        .feed-header {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 30px; flex-wrap: wrap; gap: 15px;
        }
        
        .filter-btn {
            border: 1px solid #ddd; background: white; color: #666;
            padding: 8px 20px; border-radius: 30px; font-size: 0.85rem;
            transition: 0.2s; text-decoration: none; display: inline-block;
        }
        /* Classe active dinâmica */
        .filter-btn.active, .filter-btn:hover {
            border-color: var(--primary-green); color: var(--primary-green); 
            background: rgba(125, 140, 102, 0.1); font-weight: bold;
        }

        /* --- CARDS DE REDAÇÃO --- */
        .essay-ticket {
            background: white; border-radius: 16px; overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05); transition: 0.3s;
            border: 1px solid rgba(0,0,0,0.03); height: 100%;
            display: flex; flex-direction: column; position: relative;
        }
        .essay-ticket:hover {
            transform: translateY(-5px); box-shadow: var(--card-hover-shadow);
            border-color: rgba(125, 140, 102, 0.4);
        }

        .essay-ticket::before {
            content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 6px;
            background: #e0e0e0; transition: 0.3s;
        }
        .essay-ticket:hover::before { background: var(--gold); }

        .ticket-body { padding: 25px; flex-grow: 1; padding-left: 35px; }

        .ticket-tags { margin-bottom: 15px; }
        .tag-badge {
            font-size: 0.7rem; text-transform: uppercase; font-weight: 700;
            padding: 4px 10px; border-radius: 6px; letter-spacing: 0.5px;
        }
        .tag-open { background: rgba(46, 204, 113, 0.1); color: #27ae60; }
        .tag-theme { background: #f8f9fa; color: #666; border: 1px solid #eee; margin-left: 5px;}

        .ticket-title {
            font-family: 'Playfair Display'; font-weight: 700; font-size: 1.2rem;
            color: var(--text-dark); margin-bottom: 8px; line-height: 1.3;
        }
        .ticket-theme {
            font-size: 0.9rem; color: #777; margin-bottom: 20px;
            display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
        }

        .ticket-footer {
            padding: 15px 25px 15px 35px; border-top: 1px solid #f9f9f9;
            display: flex; justify-content: space-between; align-items: center;
            background: #fafafa;
        }

        .author-info { display: flex; align-items: center; gap: 10px; }
        .author-avatar {
            width: 30px; height: 30px; border-radius: 50%; object-fit: cover;
            border: 2px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .author-text { font-size: 0.8rem; line-height: 1.2; }
        .author-role { font-size: 0.7rem; color: var(--primary-green); font-weight: bold; }

        .btn-collab {
            width: 35px; height: 35px; border-radius: 50%;
            background: white; border: 1px solid #eee; color: var(--dark-green);
            display: flex; align-items: center; justify-content: center;
            transition: 0.3s; box-shadow: 0 2px 5px rgba(0,0,0,0.05); text-decoration: none;
        }
        .btn-collab:hover { background: var(--primary-green); color: white; border-color: var(--primary-green); }

        footer { margin-top: auto; background: #222; color: #aaa; padding: 50px 0; }
    </style>
</head>
<body>

    <?php include 'header.php'; ?>

    <section class="practice-hero">
        <div class="container" data-aos="fade-down">
            <div class="hero-icon-wrapper">
                <i class="fas fa-edit fa-3x text-white"></i>
            </div>
            <h1 class="display-3 fw-bold mb-3 text-white">Oficina de Redação</h1>
            <p class="lead text-white-50 mx-auto mb-5" style="max-width: 700px;">
                Um ambiente seguro para errar, corrigir e evoluir. Publique seus rascunhos e contribua com o crescimento de outros estudantes.
            </p>
            <a href="publicar_redacao.php" class="btn-create-hero">
                <i class="fas fa-plus me-2"></i> Submeter Nova Redação
            </a>
        </div>
    </section>

    <main class="container">
        
        <div class="row process-steps g-4 justify-content-center">
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                <div class="step-card">
                    <div class="step-number">1</div>
                    <div class="step-icon"><i class="fas fa-pencil-ruler"></i></div>
                    <h5>Escreva</h5>
                    <p class="small text-muted mb-0">Desenvolva seu texto com base nos temas do ENEM e publique o rascunho.</p>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                <div class="step-card">
                    <div class="step-number">2</div>
                    <div class="step-icon"><i class="fas fa-comments"></i></div>
                    <h5>Colabore</h5>
                    <p class="small text-muted mb-0">Receba feedbacks construtivos e correções de outros membros da comunidade.</p>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                <div class="step-card">
                    <div class="step-number">3</div>
                    <div class="step-icon"><i class="fas fa-graduation-cap"></i></div>
                    <h5>Evolua</h5>
                    <p class="small text-muted mb-0">Refine sua técnica, melhore seus argumentos e conquiste a nota 1000.</p>
                </div>
            </div>
        </div>

        <section class="mb-5">
            <div class="feed-header" data-aos="fade-right">
                <div>
                    <h3 class="fw-bold text-dark mb-1">Mural de Correções</h3>
                    <p class="text-muted small mb-0">Textos recentes aguardando análise</p>
                </div>
                
                <div>
                    <a href="pratica_enem.php?filtro=recentes" class="filter-btn <?php echo ($filtro_atual == 'recentes') ? 'active' : ''; ?>">Recentes</a>
                    
                    <a href="pratica_enem.php?filtro=sem_correcao" class="filter-btn <?php echo ($filtro_atual == 'sem_correcao') ? 'active' : ''; ?>">
                        <i class="fas fa-exclamation-circle me-1"></i> Sem Correção
                    </a>
                    
                    <a href="pratica_enem.php?filtro=meus_temas" class="filter-btn <?php echo ($filtro_atual == 'meus_temas') ? 'active' : ''; ?>">
                        <i class="fas fa-user me-1"></i> Meus Temas
                    </a>
                </div>
            </div>

            <div class="row g-4">
                <?php
                if ($resultado && $resultado->num_rows > 0):
                    while ($redacao = $resultado->fetch_assoc()):
                        $foto = !empty($redacao['foto_perfil']) ? $redacao['foto_perfil'] : 'https://cdn-icons-png.flaticon.com/512/3135/3135715.png';
                        $tema_limpo = !empty($redacao['tema']) ? $redacao['tema'] : "Tema Livre";
                        $titulo_limpo = !empty($redacao['titulo']) ? $redacao['titulo'] : "Sem título definido";
                        $data_fmt = date('d/m', strtotime($redacao['data_salva']));
                        $role_display = ($redacao['tipo_usuario'] == 'estudante') ? 'Estudante' : 'Membro';
                ?>
                <div class="col-md-6 col-lg-4" data-aos="fade-up">
                    <div class="essay-ticket">
                        <div class="ticket-body">
                            <div class="ticket-tags">
                                <span class="tag-badge tag-open"><i class="fas fa-lock-open me-1"></i> Aberto</span>
                                <span class="tag-badge tag-theme">ENEM</span>
                            </div>
                            
                            <h4 class="ticket-title"><?php echo htmlspecialchars($titulo_limpo); ?></h4>
                            <p class="ticket-theme" title="<?php echo htmlspecialchars($tema_limpo); ?>">
                                <i class="fas fa-quote-left me-1 text-muted small"></i> <?php echo htmlspecialchars($tema_limpo); ?>
                            </p>
                        </div>

                        <a href="ver_redacao.php?id=<?php echo $redacao['id_redacao']; ?>" class="stretched-link"></a>

                        <div class="ticket-footer">
                            <div class="author-info">
                                <img src="<?php echo htmlspecialchars($foto); ?>" alt="Autor" class="author-avatar">
                                <div class="author-text">
                                    <div class="fw-bold text-dark"><?php echo htmlspecialchars($redacao['autor']); ?></div>
                                    <div class="author-role"><?php echo htmlspecialchars($role_display); ?> &bull; <?php echo $data_fmt; ?></div>
                                </div>
                            </div>
                            
                            <div class="btn-collab" title="Contribuir">
                                <i class="fas fa-pen"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <?php 
                    endwhile;
                else:
                ?>
                    <div class="col-12 text-center py-5" data-aos="zoom-in">
                        <div class="mb-3 opacity-25">
                            <i class="fas fa-clipboard-check fa-5x text-secondary"></i>
                        </div>
                        <h4 class="text-secondary fw-bold"><?php echo $msg_vazio_titulo; ?></h4>
                        <p class="text-muted mb-4"><?php echo $msg_vazio_desc; ?></p>
                        
                        <?php if($filtro_atual == 'recentes' || $filtro_atual == 'meus_temas'): ?>
                            <a href="publicar_redacao.php" class="btn btn-outline-success rounded-pill px-4 mt-2">Escrever Redação</a>
                        <?php else: ?>
                            <a href="pratica_enem.php" class="btn btn-outline-secondary rounded-pill px-4 mt-2">Limpar Filtros</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>

    </main>

    <footer>
        <div class="container text-center">
            <p class="mb-2 fw-bold brand-font fs-3">Abismo de Letras</p>
            <p class="small opacity-75 mb-0">&copy; 2025 Projeto TCC - Etec Monsenhor Antonio Magliano.</p>
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