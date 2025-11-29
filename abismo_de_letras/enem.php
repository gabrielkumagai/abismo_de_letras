<?php
include 'conexao.php';
// Garante sessão iniciada
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Verificação de segurança
if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laboratório de Redação - Abismo de Letras</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Playfair+Display:wght@400;600;700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
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

        h1, h2, h3, h4 { font-family: 'Playfair Display', serif; }
        .brand-font { font-family: 'Great Vibes', cursive; }

        /* --- HERO SECTION --- */
        .enem-hero {
            background: linear-gradient(rgba(44, 62, 80, 0.85), rgba(74, 93, 63, 0.75)), url('https://images.unsplash.com/photo-1456513080510-7bf3a84b82f8?q=80&w=2000&auto=format&fit=crop');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            padding: 150px 0 100px;
            color: white;
            text-align: center;
            position: relative;
            margin-bottom: 60px;
        }

        .enem-hero::after {
            content: ''; position: absolute; bottom: 0; left: 0; width: 100%; height: 50px;
            background: linear-gradient(to top, var(--cream), transparent);
        }

        .hero-badge {
            background: rgba(212, 175, 55, 0.2);
            border: 1px solid var(--gold);
            color: var(--gold);
            padding: 8px 25px;
            border-radius: 50px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-size: 0.8rem;
            display: inline-block;
            margin-bottom: 20px;
            backdrop-filter: blur(5px);
        }

        /* --- CARDS DE MÓDULOS (Split Premium) --- */
        .module-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0,0,0,0.06);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            height: 100%;
            border: 1px solid rgba(0,0,0,0.02);
            position: relative;
            display: flex;
            flex-direction: column;
        }

        .module-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 25px 50px rgba(0,0,0,0.12);
            border-color: rgba(125, 140, 102, 0.3);
        }

        .card-img-wrapper {
            height: 200px;
            overflow: hidden;
            position: relative;
        }

        .card-img-top {
            width: 100%; height: 100%;
            object-fit: cover;
            transition: transform 0.6s;
        }
        .module-card:hover .card-img-top { transform: scale(1.1); }

        .card-icon-overlay {
            position: absolute;
            bottom: -30px;
            right: 30px;
            width: 60px; height: 60px;
            background: var(--primary-green);
            color: white;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            border: 4px solid white;
            z-index: 2;
        }

        .card-body-custom { 
            padding: 40px 30px 30px; 
            flex-grow: 1; 
            display: flex; 
            flex-direction: column; 
        }

        .module-list {
            list-style: none; padding: 0; margin: 20px 0;
            flex-grow: 1;
        }
        .module-list li {
            margin-bottom: 12px;
            color: #666;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
        }
        .module-list i { margin-right: 10px; }

        .btn-module {
            display: block; width: 100%;
            padding: 14px; border-radius: 50px;
            font-weight: bold; text-decoration: none;
            text-align: center; transition: 0.3s;
            text-transform: uppercase; letter-spacing: 1px; font-size: 0.9rem;
            margin-top: auto;
        }

        .btn-teoria {
            background: white; border: 2px solid var(--dark-green); color: var(--dark-green);
        }
        .btn-teoria:hover { background: var(--dark-green); color: white; }

        .btn-pratica {
            background: linear-gradient(135deg, var(--dark-green), var(--primary-green));
            color: white; border: none;
        }
        .btn-pratica:hover {
            background: linear-gradient(135deg, var(--primary-green), var(--gold));
            color: white; transform: scale(1.02); box-shadow: 0 10px 20px rgba(0,0,0,0.15);
        }

        /* --- FAIXA DE COMPETÊNCIAS --- */
        .competencies-section {
            padding: 80px 0;
            background: white;
            position: relative;
        }
        .competencies-section::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0; height: 1px;
            background: linear-gradient(90deg, transparent, rgba(0,0,0,0.1), transparent);
        }

        .comp-card {
            text-align: center;
            padding: 25px 15px;
            border-radius: 15px;
            transition: 0.3s;
            background: #fdfdfd;
            border: 1px solid #f0f0f0;
            height: 100%;
        }
        .comp-card:hover {
            background: white;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
            transform: translateY(-5px);
            border-color: var(--gold);
        }
        .comp-num {
            font-size: 3rem; font-family: 'Playfair Display';
            color: rgba(212, 175, 55, 0.3); font-weight: 900;
            line-height: 1; margin-bottom: -20px; display: block;
        }
        .comp-icon { font-size: 1.8rem; color: var(--dark-green); margin-bottom: 15px; position: relative; }
        .comp-title { font-weight: bold; color: var(--text-dark); margin-bottom: 8px; }
        .comp-desc { font-size: 0.85rem; color: #777; line-height: 1.5; }

        /* --- ARSENAL (Ferramentas Rápidas) --- */
        .arsenal-section { padding: 60px 0 80px; }
        .tool-card {
            background: linear-gradient(145deg, #ffffff, #f5f5f5);
            border-radius: 15px; padding: 20px;
            display: flex; align-items: center;
            border: 1px solid rgba(0,0,0,0.05);
            transition: 0.3s; text-decoration: none; color: var(--text-dark);
        }
        .tool-card:hover {
            transform: translateX(5px);
            border-left: 5px solid var(--primary-green);
            background: white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        .tool-icon {
            width: 45px; height: 45px; background: rgba(125, 140, 102, 0.15);
            color: var(--dark-green); border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            margin-right: 15px; font-size: 1.2rem;
        }

        /* Footer */
                        footer { background: #222; color: #aaa; padding: 60px 0 20px; }
        .footer-heading { color: white; font-family: 'Great Vibes'; font-size: 2rem; margin-bottom: 20px; }
    </style>
</head>
<body>

    <?php include 'header.php'; ?>

    <section class="enem-hero">
        <div class="container" data-aos="fade-up">
            <span class="hero-badge"><i class="fas fa-medal me-2"></i> Rumo à Nota 1000</span>
            <h1 class="display-3 fw-bold mb-3 text-white">Laboratório de Redação</h1>
            <p class="lead text-white-50 mx-auto" style="max-width: 700px; font-weight: 300;">
                Domine a estrutura dissertativa-argumentativa. Analise a teoria dos campeões ou coloque suas ideias em prática com nossa comunidade.
            </p>
        </div>
    </section>

    <main class="container" style="margin-top: -80px; position: relative; z-index: 10;">
        <div class="row g-5 justify-content-center">
            
            <div class="col-md-6 col-lg-5" data-aos="fade-up" data-aos-delay="100">
                <div class="module-card">
                    <div class="card-img-wrapper">
                        <img src="https://images.unsplash.com/photo-1457369804613-52c61a468e7d?q=80&w=800&auto=format&fit=crop" class="card-img-top" alt="Estudos">
                        <div class="card-icon-overlay"><i class="fas fa-book-open"></i></div>
                    </div>
                    <div class="card-body-custom">
                        <h3 class="mb-2">Acervo de Excelência</h3>
                        <p class="text-muted small text-uppercase fw-bold mb-3">Modelos de Redação Nota 1000</p>
                        <p class="text-secondary">
                            Não comece do zero. Estude a estrutura, os conectivos e o repertório sociocultural de quem já chegou lá.
                        </p>
                        <ul class="module-list">
                            <li><i class="fas fa-check-circle text-success"></i> Análises comentadas por competência</li>
                            <li><i class="fas fa-check-circle text-success"></i> Estruturas coringas de introdução</li>
                            <li><i class="fas fa-check-circle text-success"></i> Banco de citações por eixo temático</li>
                        </ul>
                        <a href="modelos_enem.php" class="btn-module btn-teoria">Acessar Acervo</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-5" data-aos="fade-up" data-aos-delay="200">
                <div class="module-card">
                    <div class="card-img-wrapper">
                        <img src="https://images.unsplash.com/photo-1517048676732-d65bc937f952?q=80&w=800&auto=format&fit=crop" class="card-img-top" alt="Colaboração">
                        <div class="card-icon-overlay" style="background: var(--gold);"><i class="fas fa-pen-nib"></i></div>
                    </div>
                    <div class="card-body-custom">
                        <h3 class="mb-2">Oficina Colaborativa</h3>
                        <p class="text-muted small text-uppercase fw-bold mb-3">Prática & Correção Mútua</p>
                        <p class="text-secondary">
                            A teoria é vital, mas a prática leva à perfeição. Escreva seus rascunhos e receba feedback real da comunidade.
                        </p>
                        <ul class="module-list">
                            <li><i class="fas fa-check-circle text-warning"></i> Envie seus temas semanais</li>
                            <li><i class="fas fa-check-circle text-warning"></i> Receba correções de outros alunos</li>
                            <li><i class="fas fa-check-circle text-warning"></i> Sistema de versões e evolução</li>
                        </ul>
                        <a href="pratica_enem.php" class="btn-module btn-pratica">Escrever Agora</a>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <section class="competencies-section mt-5">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h4 class="fw-bold text-dark">Os 5 Pilares da Avaliação</h4>
                <p class="text-muted small text-uppercase letter-spacing-2">O que o INEP avalia no seu texto</p>
            </div>
            
            <div class="row g-4 justify-content-center">
                <div class="col-6 col-md-2" data-aos="zoom-in" data-aos-delay="100">
                    <div class="comp-card">
                        <span class="comp-num">I</span>
                        <div class="comp-icon"><i class="fas fa-spell-check"></i></div>
                        <div class="comp-title">Norma Culta</div>
                        <div class="comp-desc">Domínio da escrita formal da língua.</div>
                    </div>
                </div>
                <div class="col-6 col-md-2" data-aos="zoom-in" data-aos-delay="200">
                    <div class="comp-card">
                        <span class="comp-num">II</span>
                        <div class="comp-icon"><i class="fas fa-brain"></i></div>
                        <div class="comp-title">Tema & Tipo</div>
                        <div class="comp-desc">Compreensão e estrutura dissertativa.</div>
                    </div>
                </div>
                <div class="col-6 col-md-2" data-aos="zoom-in" data-aos-delay="300">
                    <div class="comp-card">
                        <span class="comp-num">III</span>
                        <div class="comp-icon"><i class="fas fa-gavel"></i></div>
                        <div class="comp-title">Argumentação</div>
                        <div class="comp-desc">Seleção e organização de ideias.</div>
                    </div>
                </div>
                <div class="col-6 col-md-2" data-aos="zoom-in" data-aos-delay="400">
                    <div class="comp-card">
                        <span class="comp-num">IV</span>
                        <div class="comp-icon"><i class="fas fa-link"></i></div>
                        <div class="comp-title">Coesão</div>
                        <div class="comp-desc">Uso correto de conectivos.</div>
                    </div>
                </div>
                <div class="col-6 col-md-2" data-aos="zoom-in" data-aos-delay="500">
                    <div class="comp-card">
                        <span class="comp-num">V</span>
                        <div class="comp-icon"><i class="fas fa-hands-helping"></i></div>
                        <div class="comp-title">Intervenção</div>
                        <div class="comp-desc">Proposta de solução para o problema.</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="arsenal-section">
        <div class="container">
            <h4 class="mb-4 fw-bold text-dark border-start border-4 border-success ps-3">Arsenal do Estudante</h4>
            <div class="row g-3">
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                    <a href="#" class="tool-card">
                        <div class="tool-icon"><i class="fas fa-quote-right"></i></div>
                        <div>
                            <h6 class="mb-1 fw-bold">Banco de Citações</h6>
                            <small class="text-muted">Frases de filósofos por eixo.</small>
                        </div>
                    </a>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                    <a href="#" class="tool-card">
                        <div class="tool-icon"><i class="fas fa-project-diagram"></i></div>
                        <div>
                            <h6 class="mb-1 fw-bold">Lista de Conectivos</h6>
                            <small class="text-muted">Para iniciar e ligar parágrafos.</small>
                        </div>
                    </a>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                    <a href="#" class="tool-card">
                        <div class="tool-icon"><i class="fas fa-history"></i></div>
                        <div>
                            <h6 class="mb-1 fw-bold">Alusões Históricas</h6>
                            <small class="text-muted">Contextos para enriquecer o texto.</small>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </section>

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
<?php if(isset($conn)) $conn->close(); ?>