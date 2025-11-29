<?php
include 'conexao.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$id_livro = isset($_GET['livro_id']) ? (int)$_GET['livro_id'] : 0;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Literatura Brasileira ENEM - Abismo de Letras</title>
    
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
            background-color: var(--cream);
            color: var(--text-dark);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        h1, h2, h3, h4 { font-family: 'Playfair Display', serif; }
        .brand-font { font-family: 'Great Vibes', cursive; }

        /* --- HERO SECTION --- */
        .lit-hero {
            background: linear-gradient(rgba(45, 56, 38, 0.9), rgba(45, 56, 38, 0.8)), url('https://images.unsplash.com/photo-1463320726281-696a413703b6?q=80&w=2000&auto=format&fit=crop');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            padding: 140px 0 100px;
            color: white;
            text-align: center;
            margin-bottom: 50px;
        }

        .hero-badge {
            border: 1px solid var(--gold); color: var(--gold);
            padding: 5px 20px; border-radius: 50px; text-transform: uppercase;
            letter-spacing: 2px; font-size: 0.8rem; margin-bottom: 20px; display: inline-block;
        }

        /* --- DETALHES DO LIVRO --- */
        .book-detail-container {
            background: white; border-radius: 20px; padding: 40px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.05); margin-bottom: 50px;
        }

        .book-cover-mockup {
            width: 100%; max-width: 300px; margin: 0 auto; display: block;
            box-shadow: -10px 10px 20px rgba(0,0,0,0.2); border-radius: 5px 15px 15px 5px;
            transform: rotateY(-15deg); transition: transform 0.5s;
        }
        .book-cover-mockup:hover { transform: rotateY(0deg) scale(1.02); }

        .meta-info {
            border-left: 4px solid var(--gold); padding-left: 20px; margin: 20px 0;
        }

        .enem-box {
            background: rgba(212, 175, 55, 0.1); border: 1px solid var(--gold);
            padding: 25px; border-radius: 15px; margin-top: 30px;
        }

        /* --- LISTA DE LIVROS (GRID) --- */
        .school-section { margin-bottom: 60px; }
        .school-title {
            font-size: 1.8rem; color: var(--dark-green); border-bottom: 2px solid #ddd;
            padding-bottom: 10px; margin-bottom: 30px; display: flex; align-items: center;
        }
        .school-badge {
            background: var(--dark-green); color: white; padding: 5px 15px;
            border-radius: 20px; font-size: 0.9rem; margin-left: 15px;
        }

        .lit-card {
            background: white; border: none; border-radius: 15px;
            overflow: hidden; transition: 0.3s; height: 100%;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            display: flex; flex-direction: column;
        }
        .lit-card:hover {
            transform: translateY(-8px); box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }

        .lit-card-body { padding: 25px; flex-grow: 1; display: flex; flex-direction: column; }
        
        .lit-icon {
            font-size: 2.5rem; color: var(--primary-green); margin-bottom: 15px; opacity: 0.8;
        }

        .btn-details {
            margin-top: auto; width: 100%; border-radius: 50px;
            border: 1px solid var(--primary-green); color: var(--primary-green);
            background: transparent; font-weight: bold; padding: 8px; transition: 0.3s;
        }
        .btn-details:hover { background: var(--primary-green); color: white; }

        footer { margin-top: auto; background: #222; color: #aaa; padding: 50px 0; }
    </style>
</head>
<body>

    <?php include 'header.php'; ?>

    <section class="lit-hero">
        <div class="container" data-aos="fade-down">
            <span class="hero-badge"><i class="fas fa-landmark me-2"></i> Patrimônio Cultural</span>
            <h1 class="display-3 fw-bold mb-3">Clássicos do Brasil</h1>
            <p class="lead text-white-50 mx-auto" style="max-width: 700px;">
                Explore as obras literárias essenciais para o ENEM. Análises profundas, contextos históricos e relevância para a prova.
            </p>
        </div>
    </section>

    <main class="container">

        <?php if ($id_livro > 0): ?>
            <?php
            $sql_detalhe = "SELECT * FROM livros_enem WHERE id_livro = ?";
            $stmt_detalhe = $conn->prepare($sql_detalhe);
            $stmt_detalhe->bind_param("i", $id_livro);
            $stmt_detalhe->execute();
            $livro = $stmt_detalhe->get_result()->fetch_assoc();
            $stmt_detalhe->close();

            if ($livro):
                // Placeholder para capa se não tiver no banco (supondo que não tem coluna de imagem ainda)
                $capa_placeholder = "https://placehold.co/400x600/3e2723/ffffff?text=" . urlencode($livro['titulo']);
            ?>
                <div class="book-detail-container" data-aos="fade-up">
                    <div class="row g-5">
                        <div class="col-md-4 text-center">
                            <img src="<?php echo $capa_placeholder; ?>" alt="Capa" class="book-cover-mockup">
                            <div class="mt-4">
                                <h5 class="text-muted text-uppercase small ls-2">Escola Literária</h5>
                                <span class="badge bg-success rounded-pill px-3 py-2 fs-6">
                                    <?php echo htmlspecialchars($livro['escola_literaria']); ?>
                                </span>
                            </div>
                        </div>

                        <div class="col-md-8">
                            <h2 class="display-5 fw-bold text-dark mb-1"><?php echo htmlspecialchars($livro['titulo']); ?></h2>
                            <h4 class="text-muted mb-4 fst-italic">de <?php echo htmlspecialchars($livro['autor']); ?></h4>
                            
                            <div class="meta-info">
                                <p class="mb-0"><strong>Contexto:</strong> Uma obra fundamental para compreender a identidade brasileira e os movimentos sociais de sua época.</p>
                            </div>

                            <h5 class="fw-bold mt-4"><i class="fas fa-align-left me-2 text-warning"></i> Sinopse & Análise</h5>
                            <p class="text-secondary" style="line-height: 1.8; text-align: justify;">
                                <?php echo nl2br(htmlspecialchars($livro['sinopse'])); ?>
                            </p>

                            <div class="enem-box">
                                <h5 class="fw-bold text-dark mb-3"><i class="fas fa-graduation-cap me-2 text-success"></i> Por que cai no ENEM?</h5>
                                <p class="mb-0 text-dark opacity-75">
                                    <?php echo nl2br(htmlspecialchars($livro['relevancia_enem'])); ?>
                                </p>
                            </div>

                            <div class="mt-5">
                                <a href="literatura_enem.php" class="btn btn-outline-dark rounded-pill px-4">
                                    <i class="fas fa-arrow-left me-2"></i> Voltar à Galeria
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <h3 class="text-muted">Obra não encontrada.</h3>
                    <a href="literatura_enem.php" class="btn btn-primary mt-3">Voltar</a>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <?php
            // Busca e Agrupa por Escola Literária
            $sql_lista = "SELECT * FROM livros_enem ORDER BY escola_literaria, titulo";
            $resultado_lista = $conn->query($sql_lista);

            if ($resultado_lista->num_rows > 0):
                $escola_atual = "";
                $livros = [];
                // Organiza em array para facilitar o loop visual
                while($row = $resultado_lista->fetch_assoc()) {
                    $livros[$row['escola_literaria']][] = $row;
                }

                // Loop pelas Escolas
                foreach ($livros as $escola => $obras):
            ?>
                <section class="school-section" data-aos="fade-up">
                    <div class="school-title">
                        <i class="fas fa-bookmark me-3 text-warning"></i> <?php echo htmlspecialchars($escola); ?>
                        <span class="school-badge"><?php echo count($obras); ?> obras</span>
                    </div>

                    <div class="row g-4">
                        <?php foreach ($obras as $obra): ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="lit-card">
                                    <div class="lit-card-body">
                                        <div class="text-center">
                                            <div class="lit-icon"><i class="fas fa-book"></i></div>
                                            <h4 class="fw-bold mb-1 text-dark"><?php echo htmlspecialchars($obra['titulo']); ?></h4>
                                            <p class="text-muted small mb-3"><?php echo htmlspecialchars($obra['autor']); ?></p>
                                        </div>
                                        <p class="small text-secondary text-center mb-4" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                                            <?php echo substr(htmlspecialchars($obra['sinopse']), 0, 120) . '...'; ?>
                                        </p>
                                        <a href="literatura_enem.php?livro_id=<?php echo $obra['id_livro']; ?>" class="btn-details">
                                            Análise Completa
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php 
                endforeach;
            else:
            ?>
                <div class="text-center py-5">
                    <i class="fas fa-book-dead fa-4x text-muted mb-3 opacity-50"></i>
                    <h3 class="text-muted">Nenhuma obra literária cadastrada.</h3>
                    <p>O acervo está sendo construído.</p>
                </div>
            <?php endif; ?>

        <?php endif; ?>

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