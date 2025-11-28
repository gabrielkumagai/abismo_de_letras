<?php
session_start();
// Lógica de sessão (se necessário futuramente)
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Abismo de Letras - Home</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Playfair+Display:wght@400;700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        :root {
            --primary-green: #7d8c66;
            --dark-green: #4a5d3f;
            --cream: #f9f7f2;
            --text-dark: #333;
            --gold: #d4af37;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Lato', sans-serif;
            background-color: var(--cream);
            color: var(--text-dark);
            overflow-x: hidden;
        }

        /* Tipografia */
        h1, h2, h3 {
            font-family: 'Playfair Display', serif;
            color: var(--dark-green);
        }

        .brand-font {
            font-family: 'Great Vibes', cursive;
        }

        /* --- Navbar Personalizada --- */
        .navbar {
            transition: all 0.4s ease;
            padding: 1.2rem 0;
        }
        
        /* Estado inicial da Navbar (Transparente sobre imagem escura) */
        .navbar-transparent .nav-link, 
        .navbar-transparent .navbar-brand,
        .navbar-transparent .btn-outline-custom {
            color: #fff !important;
            border-color: #fff;
        }

        .navbar-transparent .navbar-toggler-icon {
            filter: invert(1); /* Ícone branco */
        }

        /* Estado rolado da Navbar (Fundo branco) */
        .navbar-scrolled {
            background-color: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            padding: 0.8rem 0;
        }

        .navbar-scrolled .nav-link, 
        .navbar-scrolled .navbar-brand {
            color: var(--dark-green) !important;
        }

        .navbar-scrolled .btn-outline-custom {
            color: var(--dark-green) !important;
            border-color: var(--dark-green) !important;
        }

        .navbar-brand {
            font-size: 2rem;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }

        .nav-link {
            font-weight: 600;
            margin: 0 10px;
            position: relative;
        }

        .nav-link::after {
            content: '';
            display: block;
            width: 0;
            height: 2px;
            background: var(--gold);
            transition: width .3s;
            margin-top: 2px;
        }

        .nav-link:hover::after {
            width: 100%;
        }

        /* --- Hero Section --- */
        .hero-section {
            /* Gradiente levemente mais escuro para garantir leitura do branco */
            background: linear-gradient(rgba(45, 56, 38, 0.6), rgba(45, 56, 38, 0.4)), url('https://images.unsplash.com/photo-1519681393784-d120267933ba?q=80&w=2000&auto=format&fit=crop');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
            position: relative;
        }

        .hero-title {
            font-size: 5rem;
            text-shadow: 2px 2px 15px rgba(0,0,0,0.6);
            color: #fff; /* Garante branco no Hero */
            margin-bottom: 0.5rem;
        }

        .hero-subtitle {
            font-size: 1.4rem;
            margin-bottom: 40px;
            font-weight: 300;
            max-width: 700px;
            text-shadow: 1px 1px 5px rgba(0,0,0,0.5);
        }

        /* Seta animada de scroll */
        .scroll-down {
            position: absolute;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            animation: bounce 2s infinite;
            color: rgba(255,255,255,0.7);
            font-size: 2rem;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {transform: translateX(-50%) translateY(0);}
            40% {transform: translateX(-50%) translateY(-10px);}
            60% {transform: translateX(-50%) translateY(-5px);}
        }

        /* --- Seções Gerais --- */
        .content-section {
            padding: 100px 0;
        }

        .bg-green {
            background-color: var(--primary-green);
            color: white;
        }
        
        .bg-green h2, .bg-green p, .bg-green i {
            color: white;
        }

        .img-fluid-custom {
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            transition: transform 0.4s ease;
        }

        .img-fluid-custom:hover {
            transform: scale(1.03) rotate(1deg);
        }

        /* --- Cards e Boxes --- */
        .card-enem {
            transition: all 0.3s ease;
            border-radius: 15px;
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .card-enem:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }

        .cta-box {
            background-color: var(--dark-green);
            color: white;
            padding: 80px 40px;
            border-radius: 30px;
            text-align: center;
            margin: 60px auto;
            box-shadow: 0 20px 40px rgba(0,0,0,0.25);
            background-image: url('https://www.transparenttextures.com/patterns/cubes.png'); /* Textura sutil */
        }
        
        .cta-box h2 { color: white; }

        /* --- Botões --- */
        .btn-custom {
            padding: 12px 40px;
            border-radius: 50px;
            font-family: 'Playfair Display', serif;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s;
            margin: 10px;
            text-decoration: none;
            display: inline-block;
        }

        .btn-hero {
            background-color: transparent;
            border: 2px solid white;
            color: white;
        }
        .btn-hero:hover {
            background-color: white;
            color: var(--dark-green);
        }

        /* --- Footer --- */
        footer {
            background-color: #c97c55; /* Laranja terroso */
            color: white;
            padding: 60px 0 30px 0;
        }
        
        .social-link {
            color: white;
            font-size: 1.5rem;
            margin-right: 20px;
            transition: color 0.3s;
        }
        .social-link:hover { color: var(--gold); }

        /* --- Responsividade --- */
        @media (max-width: 768px) {
            .hero-title { font-size: 3rem; }
            .hero-subtitle { font-size: 1.1rem; padding: 0 20px; }
            .content-section { padding: 60px 0; }
            .navbar-brand { font-size: 1.5rem; }
            .cta-box { padding: 40px 20px; }
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg fixed-top navbar-transparent" id="mainNav">
        <div class="container">
            <a class="navbar-brand brand-font" href="#">
                <i class="fas fa-feather-alt me-2"></i>Abismo de Letras
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav align-items-center">
                    <li class="nav-item"><a class="nav-link" href="#quem-somos">Quem somos?</a></li>
                    <li class="nav-item"><a class="nav-link" href="#como-funciona">Como funciona?</a></li>
                    <li class="nav-item"><a class="nav-link" href="#enem">Ajuda ENEM</a></li>
                    <li class="nav-item ms-3">
                        <a href="login.php" class="btn btn-outline-custom btn-sm rounded-pill px-4 py-2 border-2 fw-bold">Entrar</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <header class="hero-section">
        <div class="container" data-aos="zoom-in" data-aos-duration="1200">
            <h1 class="hero-title brand-font">Abismo de Letras</h1>
            <p class="hero-subtitle">Onde cada história conta e o aprendizado não tem limites. Uma comunidade para ler, escrever e evoluir.</p>
            <a href="#quem-somos" class="btn btn-custom btn-hero mt-3">Conheça a Plataforma</a>
        </div>
        <a href="#quem-somos" class="scroll-down"><i class="fas fa-chevron-down"></i></a>
    </header>

    <section id="quem-somos" class="content-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4 mb-lg-0" data-aos="fade-right">
                    <h2>Quem Somos?</h2>
                    <hr style="width: 70px; border: 3px solid var(--primary-green); opacity: 1; border-radius: 5px;">
                    <p>Aqui, acreditamos que as histórias têm o poder de nos conectar, inspirar e transformar. Nosso site é um espaço onde a paixão pela leitura e pela educação se encontra com a criatividade.</p>
                    <p>Oferecemos um ambiente acolhedor, onde você pode explorar narrativas já existentes, criar as suas próprias histórias ou simplesmente interagir com outros que compartilham o mesmo amor pelas palavras.</p>
                    <p>Mais do que apenas um lugar para ler ou escrever, nossa plataforma é um espaço colaborativo, onde cada um pode contribuir e aprender com os outros.</p>
                </div>
                <div class="col-lg-6" data-aos="fade-left">
                    <img src="https://images.unsplash.com/photo-1512820790803-83ca734da794?q=80&w=1000&auto=format&fit=crop" class="img-fluid img-fluid-custom" alt="Livro aberto com luzes aconchegantes">
                </div>
            </div>
        </div>
    </section>

    <section id="como-funciona" class="content-section bg-green">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 order-lg-2 mb-4 mb-lg-0" data-aos="fade-left">
                    <h2>Como Funciona?</h2>
                    <hr style="width: 70px; border: 3px solid white; opacity: 1; border-radius: 5px;">
                    <p>Nosso site é um espaço onde a criatividade e o aprendizado se encontram de maneira colaborativa.</p>
                    <ul class="list-unstyled mt-4">
                        <li class="mb-4 d-flex align-items-start">
                            <i class="fas fa-book-open fa-2x me-3"></i>
                            <div>
                                <strong>Amante de histórias?</strong><br>
                                Explore e interaja com narrativas criadas por outros usuários.
                            </div>
                        </li>
                        <li class="mb-4 d-flex align-items-start">
                            <i class="fas fa-pen-fancy fa-2x me-3"></i>
                            <div>
                                <strong>Gosta de escrever?</strong><br>
                                Adicione suas ideias, sugira mudanças ou crie suas próprias narrativas.
                            </div>
                        </li>
                        <li class="mb-4 d-flex align-items-start">
                            <i class="fas fa-users fa-2x me-3"></i>
                            <div>
                                <strong>Comunidade Viva</strong><br>
                                Convide outros a contribuir, expandir ou dar sugestões em suas obras.
                            </div>
                        </li>
                    </ul>
                </div>
                <div class="col-lg-6 order-lg-1 text-center" data-aos="fade-right">
                    <img src="https://images.unsplash.com/photo-1457369804613-52c61a468e7d?q=80&w=1000&auto=format&fit=crop" class="img-fluid img-fluid-custom" alt="Conceito de magia dos livros">
                </div>
            </div>
        </div>
    </section>

    <section id="participar" class="content-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4 mb-lg-0" data-aos="fade-up">
                    <h2>Como Participar?</h2>
                    <hr style="width: 70px; border: 3px solid var(--primary-green); opacity: 1; border-radius: 5px;">
                    <p class="lead">Participar de uma história colaborativa ou criar a sua própria no nosso site é simples e rápido!</p>
                    <p>Para começar, basta criar uma conta. O processo de cadastro é fácil: você precisará preencher alguns campos essenciais. Após concluir o cadastro, você terá acesso total ao nosso site.</p>
                    <a href="cadastro.php" class="btn btn-outline-success rounded-pill mt-4 px-5 py-2 fw-bold">Criar Conta Gratuita</a>
                </div>
                <div class="col-lg-6" data-aos="zoom-in">
                     <img src="https://images.unsplash.com/photo-1471970471555-19d4b113e9ed?q=80&w=1000&auto=format&fit=crop" class="img-fluid img-fluid-custom" alt="Pessoa lendo confortavelmente">
                </div>
            </div>
        </div>
    </section>

    <section id="enem" class="content-section" style="background-color: #f0f2ef;">
        <div class="container">
            <div class="row justify-content-center mb-5">
                <div class="col-lg-8 text-center" data-aos="fade-down">
                    <span class="badge bg-warning text-dark mb-3 px-3 py-2 rounded-pill shadow-sm">Novo Recurso</span>
                    <h2>Ajuda para o ENEM?</h2>
                    <p class="lead text-muted">Pensando em oferecer mais suporte para os estudantes, criamos uma seção exclusiva para redações.</p>
                </div>
            </div>
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4 mb-lg-0" data-aos="fade-right">
                    <img src="https://images.unsplash.com/photo-1434030216411-0b793f4b4173?q=80&w=1000&auto=format&fit=crop" class="img-fluid img-fluid-custom" alt="Materiais de estudo">
                </div>
                <div class="col-lg-6" data-aos="fade-left">
                    <div class="card card-enem mb-3 p-3">
                        <div class="d-flex align-items-center">
                            <div class="bg-success text-white rounded-circle p-3 me-3"><i class="fas fa-graduation-cap fa-lg"></i></div>
                            <div>
                                <h5 class="mb-1 fw-bold">Evolução Conjunta</h5>
                                <p class="mb-0 small text-muted">Pratique a escrita colaborativa e receba feedback real.</p>
                            </div>
                        </div>
                    </div>
                    <div class="card card-enem mb-3 p-3">
                        <div class="d-flex align-items-center">
                            <div class="bg-warning text-dark rounded-circle p-3 me-3"><i class="fas fa-exchange-alt fa-lg"></i></div>
                            <div>
                                <h5 class="mb-1 fw-bold">Troca de Ideias</h5>
                                <p class="mb-0 small text-muted">Entenda diferentes abordagens e enriqueça seu repertório.</p>
                            </div>
                        </div>
                    </div>
                    <div class="card card-enem p-3">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary text-white rounded-circle p-3 me-3"><i class="fas fa-rocket fa-lg"></i></div>
                            <div>
                                <h5 class="mb-1 fw-bold">Nossa Missão</h5>
                                <p class="mb-0 small text-muted">Um ambiente para evoluir juntos e chegar preparado.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="container">
        <div class="cta-box" data-aos="flip-up">
            <h2 class="mb-4 brand-font display-5">Pronto para começar sua história?</h2>
            <p class="mb-5 lead">Junte-se à comunidade Abismo de Letras hoje mesmo.</p>
            <div class="d-flex justify-content-center flex-wrap gap-3">
                <a href="login.php" class="btn btn-custom btn-hero">Entrar</a>
                <a href="cadastro.php" class="btn btn-custom" style="background: white; color: var(--dark-green);">Criar Conta</a>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-6 mb-4">
                    <h4 class="brand-font mb-3 fs-2">Abismo de Letras</h4>
                    <p class="mb-1"><i class="fas fa-envelope me-2"></i> abismodelatras@gmail.com</p>
                    <p><i class="fas fa-phone me-2"></i> (14) 99105-2807</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <h5 class="mb-3 text-uppercase fs-6 ls-2">Siga-nos</h5>
                    <div class="mb-4">
                        <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                    </div>
                    <p class="mt-3 small opacity-75">&copy; 2025 Abismo de Letras. Todos os direitos reservados.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    
    <script>
        // Inicializa animações AOS
        AOS.init({
            duration: 1000,
            once: true,
            offset: 100
        });

        // Lógica da Navbar (Muda de Transparente para Branca)
        const nav = document.getElementById('mainNav');
        
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                nav.classList.add('navbar-scrolled');
                nav.classList.remove('navbar-transparent');
                nav.classList.remove('navbar-dark'); // Remove tema escuro se houver
                nav.classList.add('navbar-light'); // Adiciona tema claro
            } else {
                nav.classList.remove('navbar-scrolled');
                nav.classList.add('navbar-transparent');
                nav.classList.add('navbar-dark');
                nav.classList.remove('navbar-light');
            }
        });
    </script>
</body>
</html>