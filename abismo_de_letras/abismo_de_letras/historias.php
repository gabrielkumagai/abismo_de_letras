<?php
include 'conexao.php';
// Garante que a sessão esteja iniciada para o header funcionar
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// --- LÓGICA DE FILTROS ---
$genero_selecionado = isset($_GET['genero']) ? (int)$_GET['genero'] : 0;
$id_autor_selecionado = isset($_GET['autor']) ? (int)$_GET['autor'] : 0;
$termo_busca = isset($_GET['busca']) ? $conn->real_escape_string($_GET['busca']) : "";

$where_conditions = ["h.acesso = 'publico'", "h.status_historia = 'ativo'"];

if ($genero_selecionado > 0) {
    $where_conditions[] = "h.id_genero = {$genero_selecionado}";
}

if (!empty($termo_busca)) {
    $where_conditions[] = "(h.titulo LIKE '%{$termo_busca}%' OR h.sinopse LIKE '%{$termo_busca}%')";
}

// Configuração do Cabeçalho
$page_title = "Biblioteca";
$page_subtitle = "Navegue por universos colaborativos e descubra sua próxima leitura.";
$header_bg = "https://images.unsplash.com/photo-1507842217343-583bb7270b66?q=80&w=2000&auto=format&fit=crop"; 

if ($id_autor_selecionado > 0) {
    $where_conditions[] = "h.id_autor = {$id_autor_selecionado}";
    
    // Busca nome do autor
    $stmt = $conn->prepare("SELECT nome FROM usuarios WHERE id_usuario = ?");
    $stmt->bind_param("i", $id_autor_selecionado);
    $stmt->execute();
    $res_autor = $stmt->get_result()->fetch_assoc();
    $nome_autor = $res_autor['nome'] ?? "Autor";
    $stmt->close();

    $page_title = "Obras de " . htmlspecialchars($nome_autor);
    $page_subtitle = "Portfólio criativo e colaborações.";
    $header_bg = "https://images.unsplash.com/photo-1455390582262-044cdead277a?q=80&w=2000&auto=format&fit=crop"; 
} else {
    // Por padrão, mostra apenas originais para não poluir a lista
    $where_conditions[] = "h.id_historia_original IS NULL";
}

$where_clause = "WHERE " . implode(" AND ", $where_conditions);

// Busca Gêneros
$sql_generos = "SELECT id_genero, nome FROM generos ORDER BY nome";
$resultado_generos = $conn->query($sql_generos);

// Busca Histórias
$sql = "SELECT h.id_historia, h.titulo, h.sinopse, h.data_publicacao, h.capa_imagem, 
               u.nome AS autor, u.id_usuario AS id_autor,
               g.nome AS genero_nome 
        FROM historias h 
        JOIN usuarios u ON h.id_autor = u.id_usuario 
        LEFT JOIN generos g ON h.id_genero = g.id_genero
        {$where_clause}
        ORDER BY h.data_publicacao DESC";
        
$resultado = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Abismo de Letras</title>
    
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

        /* =========================================
           ESTILOS DO HEADER/NAVBAR (Correção dos Hovers)
           ========================================= */
        .navbar {
            background: var(--glass-nav);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            padding: 0.8rem 0;
            transition: all 0.3s ease;
        }
        .brand-font { font-family: 'Great Vibes', cursive; font-size: 1.8rem; }
        
        /* Links da Navegação com Animação Dourada */
        .nav-link {
            color: var(--dark-green) !important;
            font-weight: 600;
            font-size: 0.95rem;
            margin: 0 10px;
            position: relative;
            transition: color 0.3s;
        }
        
        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 0;
            background-color: var(--gold);
            transition: width 0.3s ease-in-out;
        }
        
        .nav-link:hover::after,
        .nav-link.active::after {
            width: 100%;
        }

        /* Botão de Perfil (Dropdown) */
        .profile-btn {
            display: flex;
            align-items: center;
            background: white;
            border: 1px solid rgba(0,0,0,0.1);
            padding: 5px 15px 5px 5px;
            border-radius: 50px;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .profile-btn:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border-color: var(--primary-green);
            transform: translateY(-2px);
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

        /* =========================================
           ESTILOS DA PÁGINA HISTÓRIAS
           ========================================= */
        
        /* Cabeçalho da Página */
        .page-header {
            background: linear-gradient(rgba(45, 56, 38, 0.85), rgba(45, 56, 38, 0.7)), url('<?php echo $header_bg; ?>');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            padding: 140px 0 100px 0; /* Mais espaçamento */
            color: white;
            text-align: center;
            position: relative;
            margin-bottom: 0;
        }
        .page-title { 
            font-family: 'Playfair Display', serif; 
            font-size: 3.5rem; 
            margin-bottom: 15px; 
            text-shadow: 0 4px 15px rgba(0,0,0,0.4); 
        }
        .page-subtitle { 
            font-size: 1.2rem; 
            font-weight: 300; 
            opacity: 0.95; 
            max-width: 700px; 
            margin: 0 auto; 
            letter-spacing: 0.5px; 
        }

        /* Barra de Filtros Flutuante */
        .filter-container {
            margin-top: -60px; /* Flutua sobre o header */
            position: relative;
            z-index: 10;
            padding: 0 15px;
        }
        .filter-card {
            background: rgba(255, 255, 255, 0.98);
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.1);
            border: 1px solid rgba(0,0,0,0.02);
            backdrop-filter: blur(10px);
        }

        /* Inputs Customizados */
        .form-control-custom, .form-select-custom {
            border-radius: 50px;
            padding: 12px 25px;
            border: 1px solid #e0e0e0;
            font-size: 0.95rem;
            transition: all 0.3s;
            box-shadow: inset 0 2px 5px rgba(0,0,0,0.02);
        }
        .form-control-custom:focus, .form-select-custom:focus {
            box-shadow: 0 0 0 4px rgba(125, 140, 102, 0.2);
            border-color: var(--primary-green);
            outline: none;
        }
        .btn-filter {
            background: linear-gradient(135deg, var(--primary-green), var(--dark-green));
            color: white;
            border-radius: 50px;
            padding: 12px 30px;
            border: none;
            font-weight: bold;
            letter-spacing: 1px;
            transition: all 0.3s;
        }
        .btn-filter:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(74, 93, 63, 0.4);
            filter: brightness(1.1);
        }

        /* Grid de Livros */
        .book-grid { padding: 80px 0; }
        
        .book-card { 
            height: 100%; 
            margin-bottom: 40px; 
            perspective: 1000px; /* Para efeitos 3D se desejar */
        }

        .book-card-inner {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            height: 100%;
            display: flex;
            flex-direction: column;
            border: 1px solid rgba(0,0,0,0.03);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); /* Movimento mais fluido */
        }

        .book-card:hover .book-card-inner {
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
            transform: translateY(-10px);
            border-color: rgba(125, 140, 102, 0.3);
        }

        /* Capa do Livro */
        .book-cover-wrapper {
            position: relative;
            width: 100%;
            padding-top: 150%; /* Aspect Ratio 2:3 perfeito */
            background: #f0f0f0;
            overflow: hidden;
        }

        .book-cover {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            object-fit: cover; transition: transform 0.6s ease;
        }
        
        .book-card:hover .book-cover { transform: scale(1.08); }

        /* Badges */
        .badge-genre {
            position: absolute; top: 15px; right: 15px;
            background: rgba(212, 175, 55, 0.85); /* Dourado translúcido */
            color: white; font-size: 0.7rem; text-transform: uppercase;
            padding: 5px 12px; border-radius: 30px; font-weight: 700;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2); 
            backdrop-filter: blur(4px);
        }

        .badge-collab {
            position: absolute; top: 15px; left: 15px;
            background: rgba(44, 62, 80, 0.7); 
            color: white; font-size: 0.7rem; 
            padding: 5px 10px; border-radius: 8px;
            backdrop-filter: blur(4px);
        }

        /* Conteúdo do Livro */
        .book-content { 
            padding: 25px; 
            display: flex; 
            flex-direction: column; 
            flex-grow: 1; 
        }
        
        .book-title {
            font-family: 'Playfair Display', serif; 
            font-size: 1.3rem; 
            font-weight: 700;
            color: var(--text-dark); 
            margin-bottom: 8px; 
            line-height: 1.3;
            /* Limita título a 2 linhas */
            display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
        }
        
        .book-author { font-size: 0.85rem; color: #999; margin-bottom: 15px; font-weight: 500;}
        .book-author a { color: #888; text-decoration: none; transition: 0.2s; }
        .book-author a:hover { color: var(--gold); }
        
        .book-synopsis {
            font-size: 0.9rem; color: #666; line-height: 1.6; margin-bottom: 25px;
            /* Limita sinopse a 3 linhas */
            display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;
        }
        
        .book-footer {
            margin-top: auto; border-top: 1px solid #f0f0f0; padding-top: 20px;
            display: flex; justify-content: space-between; align-items: center;
        }
        
        .btn-read {
            font-size: 0.8rem; font-weight: bold; color: var(--primary-green);
            text-decoration: none; text-transform: uppercase; letter-spacing: 1px;
            border: 1px solid var(--primary-green);
            padding: 8px 20px; border-radius: 50px;
            transition: all 0.3s;
        }
        .btn-read:hover { 
            background-color: var(--primary-green); 
            color: white; 
            box-shadow: 0 4px 10px rgba(125, 140, 102, 0.3);
        }

        .btn-clear-filter {
            background: rgba(255,255,255,0.2);
            color: white; border: 1px solid rgba(255,255,255,0.4);
            text-decoration: none; padding: 8px 20px; border-radius: 50px;
            font-size: 0.9rem; transition: 0.3s;
        }
        .btn-clear-filter:hover { background: white; color: var(--dark-green); }

                footer { background: #222; color: #aaa; padding: 60px 0 20px; }
        .footer-heading { color: white; font-family: 'Great Vibes'; font-size: 2rem; margin-bottom: 20px; }
    </style>
</head>
<body>

    <?php include 'header.php'; ?>

    <header class="page-header">
        <div class="container" data-aos="zoom-in" data-aos-duration="1000">
            <h1 class="page-title"><?php echo $page_title; ?></h1>
            <p class="page-subtitle"><?php echo $page_subtitle; ?></p>
            
            <?php if ($id_autor_selecionado > 0): ?>
                <div class="mt-4">
                    <a href="biblioteca.php" class="btn-clear-filter">
                        <i class="fas fa-times me-2"></i> Ver Biblioteca Completa
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </header>

    <div class="container filter-container" data-aos="fade-up" data-aos-delay="100">
        <div class="filter-card">
            <form method="GET" action="historias.php" class="row g-3 align-items-center">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 ps-3 rounded-start-pill border"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="busca" class="form-control form-control-custom border-start-0 ps-2" placeholder="Pesquisar por título, sinopse..." value="<?php echo htmlspecialchars($termo_busca); ?>">
                    </div>
                </div>

                <div class="col-md-4">
                    <select class="form-select form-select-custom" name="genero" onchange="this.form.submit()">
                        <option value="0">Todos os Gêneros</option>
                        <?php 
                        $resultado_generos->data_seek(0);
                        while ($genero = $resultado_generos->fetch_assoc()): ?>
                            <option value="<?php echo $genero['id_genero']; ?>" <?php echo ($genero_selecionado == $genero['id_genero']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($genero['nome']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-3 d-grid">
                    <?php if ($id_autor_selecionado > 0): ?>
                        <input type="hidden" name="autor" value="<?php echo $id_autor_selecionado; ?>">
                    <?php endif; ?>
                    <button type="submit" class="btn btn-filter">Filtrar <i class="fas fa-filter ms-2"></i></button>
                </div>
            </form>
        </div>
    </div>

    <main class="container book-grid">
        <div class="row g-4">
            <?php if ($resultado->num_rows > 0): ?>
                <?php while ($historia = $resultado->fetch_assoc()): 
                    $capa = !empty($historia['capa_imagem']) ? htmlspecialchars($historia['capa_imagem']) : 'https://placehold.co/400x600/e0e0e0/888888?text=Sem+Capa';
                    $data_fmt = date('d/m/Y', strtotime($historia['data_publicacao']));
                ?>
                    <div class="col-sm-6 col-lg-3 book-card" data-aos="fade-up">
                        <div class="book-card-inner">
                            <div class="book-cover-wrapper">
                                <span class="badge-genre"><?php echo htmlspecialchars($historia['genero_nome'] ?? 'Geral'); ?></span>
                                <span class="badge-collab"><i class="fas fa-feather-alt"></i> Original</span>
                                <img src="<?php echo $capa; ?>" alt="Capa" class="book-cover">
                            </div>
                            
                            <div class="book-content">
                                <h3 class="book-title" title="<?php echo htmlspecialchars($historia['titulo']); ?>">
                                    <?php echo htmlspecialchars($historia['titulo']); ?>
                                </h3>
                                
                                <div class="book-author">
                                    por <a href="historias.php?autor=<?php echo $historia['id_autor']; ?>">
                                        <?php echo htmlspecialchars($historia['autor']); ?>
                                    </a>
                                </div>
                                
                                <p class="book-synopsis">
                                    <?php echo !empty($historia['sinopse']) ? htmlspecialchars($historia['sinopse']) : "Nenhuma sinopse disponível."; ?>
                                </p>
                                
                                <div class="book-footer">
                                    <small class="text-muted" style="font-size: 0.75rem;">
                                        <i class="far fa-calendar-alt me-1"></i> <?php echo $data_fmt; ?>
                                    </small>
                                    <a href="ver_historia.php?id=<?php echo $historia['id_historia']; ?>" class="btn-read">
                                        Ler
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5" data-aos="zoom-in">
                    <div class="mb-4 text-muted opacity-25">
                        <i class="fas fa-search fa-5x"></i>
                    </div>
                    <h3 class="fw-bold text-secondary">Nenhuma história encontrada</h3>
                    <p class="text-muted mb-4">Tente ajustar seus filtros ou termos de busca.</p>
                    <a href="historias.php" class="btn btn-outline-secondary rounded-pill px-4">Limpar Filtros</a>
                </div>
            <?php endif; ?>
        </div>
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