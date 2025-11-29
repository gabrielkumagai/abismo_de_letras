<?php
include 'conexao.php';
// Inicia sessão se não estiver iniciada
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// --- LÓGICA DE FILTROS & ORDENAÇÃO ---
$genero_selecionado = isset($_GET['genero']) ? (int)$_GET['genero'] : 0;
$termo_busca = isset($_GET['busca']) ? $conn->real_escape_string($_GET['busca']) : "";
$ordem = isset($_GET['ordem']) ? $_GET['ordem'] : 'recentes';

// Construção da Query
$where_conditions = ["h.acesso = 'publico'", "h.status_historia = 'ativo'", "h.id_historia_original IS NULL"];

if ($genero_selecionado > 0) $where_conditions[] = "h.id_genero = {$genero_selecionado}";
if (!empty($termo_busca)) $where_conditions[] = "(h.titulo LIKE '%{$termo_busca}%' OR h.sinopse LIKE '%{$termo_busca}%')";

$where_clause = "WHERE " . implode(" AND ", $where_conditions);

// Lógica de Ordenação
$order_sql = "h.data_publicacao DESC"; // Padrão
switch ($ordem) {
    case 'antigas': $order_sql = "h.data_publicacao ASC"; break;
    case 'az': $order_sql = "h.titulo ASC"; break;
    case 'za': $order_sql = "h.titulo DESC"; break;
}

// 1. Busca Acervo Geral
$sql = "SELECT h.*, u.nome AS autor, u.id_usuario AS id_autor, g.nome AS genero_nome 
        FROM historias h 
        JOIN usuarios u ON h.id_autor = u.id_usuario 
        LEFT JOIN generos g ON h.id_genero = g.id_genero
        {$where_clause} ORDER BY {$order_sql}";
$resultado = $conn->query($sql);

// 2. Busca "Destaques" (Sugestões aleatórias para o topo)
$sql_destaques = "SELECT h.*, u.nome AS autor, g.nome AS genero_nome 
                  FROM historias h JOIN usuarios u ON h.id_autor = u.id_usuario 
                  LEFT JOIN generos g ON h.id_genero = g.id_genero
                  WHERE h.acesso='publico' AND h.status_historia='ativo' AND h.id_historia_original IS NULL
                  ORDER BY RAND() LIMIT 6";
$res_destaques = $conn->query($sql_destaques);

// 3. Busca Gêneros
$sql_generos = "SELECT * FROM generos ORDER BY nome";
$res_generos = $conn->query($sql_generos);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biblioteca Digital - Abismo de Letras</title>
    
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
            --card-shadow: 0 10px 20px rgba(0,0,0,0.08);
            --glass-nav: rgba(255, 255, 255, 0.95);
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

        /* --- CORREÇÃO DE ESTILOS DO NAVBAR (Para o header.php funcionar) --- */
        .navbar {
            background: var(--glass-nav);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            padding: 0.8rem 0;
            transition: all 0.3s ease;
        }
        .nav-link {
            color: var(--dark-green) !important;
            font-weight: 600;
            margin: 0 10px;
            position: relative;
        }
        .nav-link::after {
            content: ''; position: absolute; width: 0; height: 2px; bottom: 0; left: 0;
            background-color: var(--gold); transition: width 0.3s;
        }
        .nav-link:hover::after, .nav-link.active::after { width: 100%; }

        /* Estilos do Botão de Perfil */
        .profile-btn {
            display: flex; align-items: center; background: white;
            border: 1px solid rgba(0,0,0,0.1); padding: 5px 15px 5px 5px;
            border-radius: 50px; transition: all 0.3s; cursor: pointer;
        }
        .profile-btn:hover {
            box-shadow: 0 4px 10px rgba(0,0,0,0.1); border-color: var(--primary-green);
        }
        .profile-img {
            width: 35px; height: 35px; border-radius: 50%; object-fit: cover;
            border: 2px solid var(--gold); margin-right: 10px;
        }
        .profile-name { font-weight: bold; color: var(--dark-green); font-size: 0.9rem; }

        /* --- HERO SECTION (Curadoria) --- */
        .library-hero {
            position: relative;
            background: linear-gradient(135deg, #2c3e50, #4a5d3f);
            color: white;
            padding: 140px 0 80px;
            overflow: hidden;
            margin-bottom: 50px;
        }
        
        .library-hero::before {
            content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background: url('https://www.transparenttextures.com/patterns/cubes.png');
            opacity: 0.05;
        }

        .hero-book-cover {
            box-shadow: -20px 20px 40px rgba(0,0,0,0.4);
            border-radius: 5px 15px 15px 5px;
            transform: perspective(1000px) rotateY(-20deg);
            transition: transform 0.5s;
            max-width: 260px;
        }
        .hero-book-cover:hover { transform: perspective(1000px) rotateY(0deg) scale(1.05); }

        .btn-gold {
            background: var(--gold); color: white; border: none;
            padding: 12px 35px; border-radius: 50px; font-weight: bold;
            text-transform: uppercase; letter-spacing: 1px; transition: 0.3s;
        }
        .btn-gold:hover { background: white; color: var(--gold); }

        /* --- NAVEGAÇÃO DE CATEGORIAS (Scroll Horizontal) --- */
        .category-scroll {
            display: flex; gap: 15px; overflow-x: auto; padding: 10px 5px 20px 5px;
            scrollbar-width: thin; scrollbar-color: var(--primary-green) #eee;
        }
        .cat-pill {
            background: white; border: 1px solid rgba(0,0,0,0.1);
            padding: 10px 25px; border-radius: 30px; white-space: nowrap;
            color: var(--text-dark); text-decoration: none; font-weight: 600;
            transition: all 0.3s;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .cat-pill:hover, .cat-pill.active {
            background: var(--primary-green); color: white; border-color: var(--primary-green);
            transform: translateY(-3px); box-shadow: 0 5px 15px rgba(125, 140, 102, 0.4);
        }

        /* --- SEÇÃO "EM ALTA" --- */
        .trending-section { margin-bottom: 60px; }
        .section-title {
            border-left: 5px solid var(--gold);
            padding-left: 15px; margin-bottom: 30px; font-weight: 700; font-size: 1.5rem;
        }

        /* --- CARDS DE LIVRO (Estilo Netflix/Moderno) --- */
        .book-card {
            background: transparent; margin-bottom: 30px; perspective: 1000px;
        }
        .book-inner {
            background: white; border-radius: 12px; overflow: hidden;
            box-shadow: var(--card-shadow); transition: all 0.3s ease;
            height: 100%; display: flex; flex-direction: column;
            border: 1px solid rgba(0,0,0,0.02);
            position: relative;
        }
        .book-inner:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }

        .cover-container {
            position: relative; width: 100%; padding-top: 145%; /* Ratio 2:3 */
            background: #eee; overflow: hidden;
        }
        .cover-img {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            object-fit: cover; transition: transform 0.5s ease;
        }
        .book-inner:hover .cover-img { transform: scale(1.08); }

        /* Ações Flutuantes na Capa */
        .card-actions {
            position: absolute; top: 10px; right: 10px; opacity: 0;
            transition: opacity 0.3s; display: flex; flex-direction: column; gap: 8px;
        }
        .book-inner:hover .card-actions { opacity: 1; }
        
        .action-btn {
            width: 40px; height: 40px; border-radius: 50%;
            background: rgba(255,255,255,0.95); color: var(--dark-green);
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; transition: 0.2s; border: none; box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        .action-btn:hover { background: var(--gold); color: white; transform: scale(1.1); }

        .genre-tag {
            position: absolute; bottom: 10px; left: 10px;
            background: rgba(44, 62, 80, 0.85); color: white;
            font-size: 0.7rem; padding: 4px 10px; border-radius: 4px;
            backdrop-filter: blur(4px); font-weight: bold;
        }

        /* Detalhes do Livro */
        .book-details { padding: 18px; flex-grow: 1; display: flex; flex-direction: column; }
        .b-title {
            font-family: 'Playfair Display', serif; font-size: 1.15rem; font-weight: 700;
            margin-bottom: 5px; line-height: 1.3; color: var(--text-dark);
            display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
        }
        .b-author { font-size: 0.85rem; color: #888; margin-bottom: 12px; }
        
        .b-footer {
            margin-top: auto; padding-top: 12px; border-top: 1px solid #f0f0f0;
            display: flex; justify-content: space-between; align-items: center;
        }
        .read-link {
            font-size: 0.8rem; font-weight: bold; color: var(--primary-green);
            text-decoration: none; text-transform: uppercase; letter-spacing: 0.5px;
        }
        .read-link:hover { color: var(--gold); letter-spacing: 1px; }

        /* --- BARRA DE FILTROS --- */
        .filter-bar {
            background: white; padding: 10px 20px; border-radius: 50px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05); margin-bottom: 40px;
            display: flex; align-items: center; gap: 15px; border: 1px solid #eee;
        }
        .search-input { border: none; outline: none; flex-grow: 1; padding: 5px; color: #555; }
        .sort-select { 
            border: none; background: #f8f8f8; padding: 8px 15px; 
            border-radius: 20px; font-size: 0.9rem; color: #555; 
            outline: none; cursor: pointer; transition: 0.2s;
        }
        .sort-select:hover { background: #eee; }

        /* Footer */
                footer { background: #222; color: #aaa; padding: 60px 0 20px; }
        .footer-heading { color: white; font-family: 'Great Vibes'; font-size: 2rem; margin-bottom: 20px; }
    </style>
</head>
<body>

    <?php include 'header.php'; ?>

    <section class="library-hero">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-5 mb-lg-0" data-aos="fade-right">
                    <span class="badge bg-warning text-dark mb-3 px-3 py-2 rounded-pill shadow-sm">
                        <i class="fas fa-star me-1"></i> Recomendação da Semana
                    </span>
                    <h1 class="display-4 fw-bold mb-3">O Eco das Montanhas</h1>
                    <p class="lead mb-4 text-white-50">Uma jornada épica através de reinos esquecidos, onde cada silêncio conta uma história antiga e poderosa.</p>
                    <div class="d-flex gap-3">
                        <a href="#" class="btn btn-gold shadow-lg">Ler Agora</a>
                        <a href="#acervo" class="btn btn-outline-light rounded-pill px-4 fw-bold">Ver Acervo Completo</a>
                    </div>
                </div>
                <div class="col-lg-6 text-center" data-aos="zoom-in" data-aos-delay="200">
                    <img src="https://images.unsplash.com/photo-1543002588-bfa74002ed7e?q=80&w=400&auto=format&fit=crop" class="hero-book-cover" alt="Livro Destaque">
                </div>
            </div>
        </div>
    </section>

    <div class="container">
        
        <div class="category-scroll mb-5" data-aos="fade-up">
            <a href="biblioteca.php" class="cat-pill <?php echo ($genero_selecionado == 0) ? 'active' : ''; ?>">
                <i class="fas fa-th-large me-2"></i>Todos
            </a>
            <?php 
            if ($res_generos) {
                $res_generos->data_seek(0);
                while ($gen = $res_generos->fetch_assoc()): ?>
                    <a href="biblioteca.php?genero=<?php echo $gen['id_genero']; ?>" 
                       class="cat-pill <?php echo ($genero_selecionado == $gen['id_genero']) ? 'active' : ''; ?>">
                       <?php echo htmlspecialchars($gen['nome']); ?>
                    </a>
                <?php endwhile; 
            } ?>
        </div>

        <?php if($genero_selecionado == 0 && empty($termo_busca) && $res_destaques && $res_destaques->num_rows > 0): ?>
        <section class="trending-section" data-aos="fade-up">
            <h3 class="section-title">Em Alta na Comunidade</h3>
            <div class="row flex-nowrap overflow-auto pb-4 px-2" style="scrollbar-width: none; -ms-overflow-style: none;">
                <?php while($dest = $res_destaques->fetch_assoc()): 
                     $capa = !empty($dest['capa_imagem']) ? $dest['capa_imagem'] : 'https://placehold.co/300x450/e0e0e0/888888?text=Capa';
                ?>
                <div class="col-8 col-sm-5 col-md-3">
                    <div class="book-card h-100 mb-0">
                        <div class="book-inner">
                            <div class="cover-container">
                                <span class="genre-tag"><?php echo htmlspecialchars($dest['genero_nome'] ?? 'Geral'); ?></span>
                                <img src="<?php echo htmlspecialchars($capa); ?>" class="cover-img" alt="Capa">
                                <a href="ver_historia.php?id=<?php echo $dest['id_historia']; ?>" class="stretched-link"></a>
                            </div>
                            <div class="book-details">
                                <h5 class="b-title fs-6"><?php echo htmlspecialchars($dest['titulo']); ?></h5>
                                <span class="b-author small text-muted">de <?php echo htmlspecialchars($dest['autor']); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </section>
        <?php endif; ?>

        <section id="acervo" class="main-library">
            <div class="d-flex justify-content-between align-items-end mb-3">
                <h3 class="section-title mb-0">Acervo Completo</h3>
                <span class="badge bg-secondary rounded-pill"><?php echo $resultado ? $resultado->num_rows : 0; ?> obras</span>
            </div>

            <form method="GET" action="biblioteca.php" class="filter-bar" data-aos="fade-up">
                <i class="fas fa-search text-muted"></i>
                <input type="text" name="busca" class="search-input" placeholder="Pesquisar por título, autor ou palavra-chave..." value="<?php echo htmlspecialchars($termo_busca); ?>">
                
                <?php if($genero_selecionado > 0): ?>
                    <input type="hidden" name="genero" value="<?php echo $genero_selecionado; ?>">
                <?php endif; ?>

                <div class="border-start ps-3 d-none d-md-block">
                    <select name="ordem" class="sort-select" onchange="this.form.submit()">
                        <option value="recentes" <?php echo ($ordem == 'recentes') ? 'selected' : ''; ?>>Mais Recentes</option>
                        <option value="antigas" <?php echo ($ordem == 'antigas') ? 'selected' : ''; ?>>Mais Antigas</option>
                        <option value="az" <?php echo ($ordem == 'az') ? 'selected' : ''; ?>>Ordem A-Z</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-sm btn-success rounded-circle shadow-sm" style="width: 40px; height: 40px; background-color: var(--primary-green); border: none;">
                    <i class="fas fa-arrow-right"></i>
                </button>
            </form>

            <div class="row g-4">
                <?php if ($resultado && $resultado->num_rows > 0): ?>
                    <?php while ($hist = $resultado->fetch_assoc()): 
                        $capa = !empty($hist['capa_imagem']) ? $hist['capa_imagem'] : 'https://placehold.co/400x600/e0e0e0/888888?text=Sem+Capa';
                    ?>
                    <div class="col-6 col-md-4 col-lg-3" data-aos="fade-up">
                        <div class="book-card h-100">
                            <div class="book-inner">
                                <div class="cover-container">
                                    <img src="<?php echo htmlspecialchars($capa); ?>" class="cover-img" alt="Capa">
                                    <div class="card-actions">
                                        <a href="ver_historia.php?id=<?php echo $hist['id_historia']; ?>" class="action-btn" title="Ler Agora">
                                            <i class="fas fa-book-open"></i>
                                        </a>
                                    </div>
                                    <span class="genre-tag"><?php echo htmlspecialchars($hist['genero_nome'] ?? 'Geral'); ?></span>
                                </div>
                                <div class="book-details">
                                    <h3 class="b-title" title="<?php echo htmlspecialchars($hist['titulo']); ?>">
                                        <?php echo htmlspecialchars($hist['titulo']); ?>
                                    </h3>
                                    <div class="b-author">por <?php echo htmlspecialchars($hist['autor']); ?></div>
                                    
                                    <div class="b-footer">
                                        <span class="text-muted small">
                                            <i class="far fa-calendar-alt me-1"></i> <?php echo date('d/m/y', strtotime($hist['data_publicacao'])); ?>
                                        </span>
                                        <a href="ver_historia.php?id=<?php echo $hist['id_historia']; ?>" class="read-link">
                                            Ler <i class="fas fa-chevron-right small"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5" data-aos="zoom-in">
                        <div class="mb-3 text-muted opacity-25">
                            <i class="fas fa-search fa-4x"></i>
                        </div>
                        <h4 class="text-muted">Nenhuma obra encontrada.</h4>
                        <p class="text-muted">Tente buscar por outro termo ou remova os filtros.</p>
                        <a href="biblioteca.php" class="btn btn-outline-success rounded-pill px-4">Limpar Filtros</a>
                    </div>
                <?php endif; ?>
            </div>
        </section>

    </div>

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