<?php 
session_start();
// A lógica de usuário agora é tratada dentro do header.php, 
// mas mantemos o session_start() aqui por segurança para outras lógicas da página.
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Abismo de Letras – Comunidade Literária</title>
    
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
            --glass-nav: rgba(255, 255, 255, 0.95);
        }

        body {
            font-family: 'Lato', sans-serif;
            background-color: var(--cream);
            color: var(--text-dark);
            overflow-x: hidden;
        }

        h1, h2, h3, h4 { font-family: 'Playfair Display', serif; }
        .brand-font { font-family: 'Great Vibes', cursive; }

        /* --- Navbar Styles (Necessários para o header.php) --- */
        .navbar {
            background: var(--glass-nav);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            padding: 0.7rem 0;
        }

        .nav-link {
            color: var(--dark-green) !important;
            font-weight: 600;
            margin: 0 10px;
            position: relative;
            font-size: 0.95rem;
        }
        
        .nav-link::after {
            content: ''; display: block; width: 0; height: 2px;
            background: var(--gold); transition: width .3s; margin-top: 2px;
        }
        .nav-link:hover::after { width: 100%; }

        /* Perfil Dropdown Styles (Usado pelo header.php) */
        .profile-btn {
            display: flex;
            align-items: center;
            background: white;
            border: 1px solid rgba(0,0,0,0.1);
            padding: 5px 15px 5px 5px;
            border-radius: 50px;
            transition: all 0.3s;
            cursor: pointer;
        }
        .profile-btn:hover {
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            border-color: var(--primary-green);
        }
        .profile-img {
            width: 35px; height: 35px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--gold);
            margin-right: 10px;
        }
        .profile-name {
            font-weight: bold;
            color: var(--dark-green);
            font-size: 0.9rem;
        }

        /* --- Hero Section --- */
        .hero-section {
            background: linear-gradient(rgba(45, 56, 38, 0.6), rgba(45, 56, 38, 0.4)), url('https://images.unsplash.com/photo-1519682337058-a94d519337bc?q=80&w=2000&auto=format&fit=crop');
            background-size: cover;
            background-attachment: fixed;
            height: 75vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
            position: relative;
        }
        
        .hero-title { font-size: 4.5rem; text-shadow: 0 4px 20px rgba(0,0,0,0.5); }
        .hero-subtitle { font-size: 1.4rem; font-weight: 300; letter-spacing: 1px; max-width: 800px; margin: 0 auto; }

        /* --- Stats Bar --- */
        .stats-bar {
            background: white;
            padding: 40px 0;
            margin-top: -50px;
            position: relative;
            z-index: 10;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            max-width: 1000px;
            margin-left: auto; margin-right: auto;
        }
        .stat-item h3 { font-size: 2.5rem; color: var(--gold); margin: 0; font-weight: 700; }
        .stat-item p { color: #777; font-weight: 600; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 1px; margin: 0; }

        /* --- Seção de Funcionalidades --- */
        .main-cards { padding: 80px 0; }
        
        .feature-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            border: none;
            box-shadow: 0 15px 35px rgba(0,0,0,0.05);
            transition: transform 0.3s;
            height: 100%;
        }
        .feature-card:hover { transform: translateY(-10px); }
        
        .card-icon-wrapper {
            height: 180px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--dark-green);
            color: white;
            font-size: 4rem;
            position: relative;
        }
        .card-icon-wrapper::after {
            content: ''; position: absolute; bottom: 0; left: 0; width: 100%; height: 50px;
            background: linear-gradient(to top, white, transparent);
        }

        .btn-action {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            background-color: var(--primary-green);
            color: white;
            font-weight: bold;
            border: none;
            transition: 0.3s;
        }
        .btn-action:hover { background-color: var(--dark-green); color: white; }

        /* --- Destaques da Semana --- */
        .showcase-section { padding: 60px 0 100px 0; }
        
        .book-card {
            background: transparent;
            perspective: 1000px;
        }
        .book-cover {
            border-radius: 5px 15px 15px 5px;
            box-shadow: 5px 5px 15px rgba(0,0,0,0.2);
            transition: transform 0.4s;
            position: relative;
            overflow: hidden;
        }
        .book-cover:hover { transform: rotateY(-10deg) scale(1.05); box-shadow: 10px 10px 25px rgba(0,0,0,0.3); }
        
        .book-info h5 { margin-top: 15px; font-weight: bold; color: var(--dark-green); }
        .book-tag { font-size: 0.75rem; background: var(--gold); color: white; padding: 3px 10px; border-radius: 20px; }

        /* Footer */
        footer { background: #222; color: #aaa; padding: 60px 0 20px; }
        .footer-heading { color: white; font-family: 'Great Vibes'; font-size: 2rem; margin-bottom: 20px; }
    </style>
</head>
<body>

    <?php include 'header.php'; ?>

    <header class="hero-section">
        <div class="container" data-aos="zoom-in" data-aos-duration="1000">
            <h1 class="hero-title brand-font">Abismo de Letras</h1>
            <p class="hero-subtitle mb-4">"Mitigar o ofuscamento de escritores independentes e conectar a paixão pela escrita com o aprendizado colaborativo."</p>
            <div class="d-flex justify-content-center gap-3">
                <a href="#destaques" class="btn btn-outline-light rounded-pill px-4 py-2 fw-bold">Explorar Biblioteca</a>
                <a href="publicar.php" class="btn btn-light text-success rounded-pill px-4 py-2 fw-bold">Começar a Escrever</a>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="stats-bar d-flex justify-content-around align-items-center flex-wrap" data-aos="fade-up">
            <div class="stat-item text-center p-3">
                <i class="fas fa-book-open fa-2x mb-2 text-success opacity-50"></i>
                <h3>+120</h3>
                <p>Histórias Criadas</p>
            </div>
            <div class="stat-item text-center p-3 border-start border-end">
                <i class="fas fa-users fa-2x mb-2 text-success opacity-50"></i>
                <h3>+450</h3>
                <p>Escritores Ativos</p>
            </div>
            <div class="stat-item text-center p-3">
                <i class="fas fa-check-double fa-2x mb-2 text-success opacity-50"></i>
                <h3>+80</h3>
                <p>Redações Corrigidas</p>
            </div>
        </div>
    </div>

    <main class="container main-cards">
        <div class="row g-4">
            <div class="col-md-6" data-aos="fade-right">
                <div class="feature-card h-100">
                    <div class="card-icon-wrapper">
                        <i class="fas fa-pen-fancy"></i>
                    </div>
                    <div class="p-4 pt-0 text-center">
                        <h3 class="mb-3">Estúdio de Criação</h3>
                        <p class="text-muted mb-4">Crie universos, colabore em histórias de outros autores ou publique seus contos autorais. Sua voz importa aqui.</p>
                        <a href="publicar.php" class="btn btn-action">Criar Nova História</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6" data-aos="fade-left">
                <div class="feature-card h-100">
                    <div class="card-icon-wrapper" style="background-color: var(--gold);">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div class="p-4 pt-0 text-center">
                        <h3 class="mb-3">Módulo ENEM</h3>
                        <p class="text-muted mb-4">Prepare-se com nossa curadoria de obras literárias obrigatórias e ferramentas de treino para redação nota 1000.</p>
                        <a href="enem.php" class="btn btn-action" style="background-color: var(--gold);">Estudar Agora</a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <section id="destaques" class="showcase-section bg-white">
        <div class="container">
            <div class="d-flex justify-content-between align-items-end mb-5">
                <div>
                    <span class="text-success fw-bold text-uppercase small">Nossa Comunidade</span>
                    <h2 class="fw-bold">Destaques da Semana</h2>
                </div>
                <a href="historias.php" class="text-decoration-none fw-bold text-success">Ver todos <i class="fas fa-arrow-right"></i></a>
            </div>

            <div class="row g-4">
                <div class="col-6 col-md-3" data-aos="zoom-in" data-aos-delay="100">
                    <div class="book-card text-center">
                        <div class="book-cover mb-3">
                            <img src="https://images.unsplash.com/photo-1544947950-fa07a98d237f?q=80&w=600&auto=format&fit=crop" class="img-fluid" alt="Capa">
                        </div>
                        <span class="book-tag">Romance</span>
                        <h5 class="fs-6 mt-2">O Eco do Silêncio</h5>
                        <p class="small text-muted">por Ana Souza</p>
                    </div>
                </div>

                <div class="col-6 col-md-3" data-aos="zoom-in" data-aos-delay="200">
                    <div class="book-card text-center">
                        <div class="book-cover mb-3">
                            <img src="https://images.unsplash.com/photo-1532012197267-da84d127e765?q=80&w=600&auto=format&fit=crop" class="img-fluid" alt="Capa">
                        </div>
                        <span class="book-tag bg-info text-white">Fantasia</span>
                        <h5 class="fs-6 mt-2">Reinos Esquecidos</h5>
                        <p class="small text-muted">por Carlos M.</p>
                    </div>
                </div>

                <div class="col-6 col-md-3" data-aos="zoom-in" data-aos-delay="300">
                    <div class="book-card text-center">
                        <div class="book-cover mb-3">
                            <img src="https://images.unsplash.com/photo-1512820790803-83ca734da794?q=80&w=600&auto=format&fit=crop" class="img-fluid" alt="Capa">
                        </div>
                        <span class="book-tag bg-danger text-white">Suspense</span>
                        <h5 class="fs-6 mt-2">A Última Página</h5>
                        <p class="small text-muted">por Beatriz L.</p>
                    </div>
                </div>

                <div class="col-6 col-md-3" data-aos="zoom-in" data-aos-delay="400">
                    <div class="book-card text-center">
                        <div class="book-cover mb-3">
                            <img src="https://images.unsplash.com/photo-1589829085413-56de8ae18c73?q=80&w=600&auto=format&fit=crop" class="img-fluid" alt="Capa">
                        </div>
                        <span class="book-tag bg-warning text-dark">Clássico</span>
                        <h5 class="fs-6 mt-2">Memórias Póstumas</h5>
                        <p class="small text-muted">Machado de Assis</p>
                    </div>
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
        AOS.init({ duration: 1000, once: true });
    </script>
</body>
</html>