<?php
include 'conexao.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}

$id_usuario = (int)$_SESSION['id_usuario'];
$mensagem_sucesso = "";
$mensagem_erro = "";

// -----------------------------------------------------
// LÓGICA DE ATUALIZAÇÃO (UPDATE)
// -----------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['atualizar_perfil'])) {
    $novo_nome = $conn->real_escape_string($_POST['novo_nome']);
    $sql_update = "UPDATE usuarios SET nome = ?";
    $params = [$novo_nome];
    $types = "s";
    
    $foto_perfil_path = null;

    if (isset($_FILES['nova_foto_perfil']) && $_FILES['nova_foto_perfil']['error'] == 0) {
        $target_dir = "uploads/perfil/";
        if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }

        $file_extension = pathinfo($_FILES['nova_foto_perfil']['name'], PATHINFO_EXTENSION);
        $file_name = uniqid('perfil_') . '.' . $file_extension;
        $target_file = $target_dir . $file_name;

        if (move_uploaded_file($_FILES['nova_foto_perfil']['tmp_name'], $target_file)) {
            $foto_perfil_path = $target_file;
            $sql_update .= ", foto_perfil = ?";
            $params[] = $foto_perfil_path;
            $types .= "s";
        } else {
            $mensagem_erro = "Aviso: Falha ao fazer upload da imagem.";
        }
    }

    $sql_update .= " WHERE id_usuario = ?";
    $params[] = $id_usuario;
    $types .= "i";

    $stmt_update = $conn->prepare($sql_update);
    
    if ($stmt_update) {
        $bind_refs = [];
        $bind_refs[] = $types; 
        foreach ($params as $key => $value) $bind_refs[] = &$params[$key];
        call_user_func_array([$stmt_update, 'bind_param'], $bind_refs);

        if ($stmt_update->execute()) {
            $mensagem_sucesso = "Perfil atualizado com sucesso!";
            $_SESSION['nome'] = $novo_nome;
            if($foto_perfil_path) $_SESSION['foto_perfil'] = $foto_perfil_path; // Atualiza sessão
        } else {
            $mensagem_erro = "Erro ao atualizar: " . $stmt_update->error;
        }
        $stmt_update->close();
    }
}

// -----------------------------------------------------
// BUSCA DADOS
// -----------------------------------------------------
$sql_user = "SELECT nome, email, tipo_usuario, foto_perfil, data_cadastro FROM usuarios WHERE id_usuario = ?";
$stmt = $conn->prepare($sql_user);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$user_data = $stmt->get_result()->fetch_assoc();
$stmt->close();

$nome_usuario = $user_data['nome'];
$tipo_usuario = ucfirst($user_data['tipo_usuario']);
$foto_perfil_src = !empty($user_data['foto_perfil']) ? $user_data['foto_perfil'] : 'https://cdn-icons-png.flaticon.com/512/3135/3135715.png';
$data_membro = date('M Y', strtotime($user_data['data_cadastro']));

// --- ESTATÍSTICAS (Simulação/Placeholder - Substitua pela sua query real) ---
// Na implementação real, você faria COUNT(*) nas tabelas de histórias, comentários, etc.
$stats = [
    'seguidores' => 120, // Exemplo
    'historias' => 5,    // Exemplo
    'palavras' => 15400, // Exemplo
    'comentarios' => 42  // Exemplo
];

// --- BADGES (Simulação) ---
// $resultado_badges = $conn->query("SELECT ..."); 
// Simulando array vazio para não quebrar se não tiver badges
$badges = []; 
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de <?php echo htmlspecialchars($nome_usuario); ?> - Abismo de Letras</title>
    
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
        }

        body {
            font-family: 'Lato', sans-serif;
            background-color: var(--cream);
            color: var(--text-dark);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Navbar & Header Styles (Para garantir compatibilidade com header.php) */
        .navbar { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .brand-font { font-family: 'Great Vibes', cursive; }
        .profile-btn { display: flex; align-items: center; background: white; border: 1px solid rgba(0,0,0,0.1); padding: 5px 15px 5px 5px; border-radius: 50px; transition: all 0.3s; cursor: pointer; }
        .profile-btn:hover { box-shadow: 0 4px 10px rgba(0,0,0,0.1); border-color: var(--primary-green); }
        .profile-img { width: 35px; height: 35px; border-radius: 50%; object-fit: cover; border: 2px solid var(--gold); margin-right: 10px; }
        .profile-name { font-weight: bold; color: var(--dark-green); font-size: 0.9rem; }

        /* --- PERFIL LAYOUT --- */
        .profile-container {
            padding-top: 100px;
            padding-bottom: 60px;
        }

        /* Sidebar Card */
        .profile-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            border: none;
            margin-bottom: 30px;
        }

        .profile-cover {
            height: 120px;
            background: linear-gradient(135deg, var(--primary-green), var(--dark-green));
            position: relative;
        }
        
        .profile-avatar-container {
            position: relative;
            margin-top: -60px;
            text-align: center;
        }

        .big-avatar {
            width: 120px; height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
            background: white;
        }

        .profile-info { padding: 20px; text-align: center; }
        .user-name { font-family: 'Playfair Display', serif; font-weight: 700; margin-bottom: 5px; color: var(--dark-green); }
        .user-role { 
            display: inline-block; padding: 4px 12px; 
            border-radius: 20px; font-size: 0.8rem; font-weight: bold; 
            background: rgba(212, 175, 55, 0.15); color: #b08d1e;
            margin-bottom: 15px;
        }

        .stats-row {
            display: flex; justify-content: space-around;
            padding: 15px 0; border-top: 1px solid #eee; border-bottom: 1px solid #eee;
            margin-bottom: 20px;
        }
        .stat-box { text-align: center; }
        .stat-num { font-weight: 800; font-size: 1.2rem; color: var(--text-dark); display: block; }
        .stat-label { font-size: 0.75rem; color: #888; text-transform: uppercase; letter-spacing: 1px; }

        .btn-edit {
            width: 100%; border-radius: 50px; padding: 10px;
            font-weight: 600; border: 2px solid var(--primary-green);
            color: var(--primary-green); background: transparent; transition: 0.3s;
        }
        .btn-edit:hover { background: var(--primary-green); color: white; }

        /* Main Content */
        .content-card {
            background: white; border-radius: 20px; padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05); margin-bottom: 30px;
        }
        .card-title { font-family: 'Playfair Display', serif; font-weight: 700; color: var(--dark-green); margin-bottom: 25px; border-bottom: 2px solid rgba(0,0,0,0.05); padding-bottom: 10px; }

        /* Stat Cards Grid */
        .stat-highlight {
            background: #f8f9fa; border-radius: 15px; padding: 20px;
            display: flex; align-items: center; transition: 0.3s;
            border: 1px solid transparent;
        }
        .stat-highlight:hover {
            transform: translateY(-5px); background: white;
            box-shadow: 0 10px 20px rgba(0,0,0,0.08); border-color: var(--gold);
        }
        .stat-icon {
            width: 50px; height: 50px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem; margin-right: 15px; flex-shrink: 0;
        }
        
        .icon-blue { background: rgba(52, 152, 219, 0.1); color: #3498db; }
        .icon-green { background: rgba(46, 204, 113, 0.1); color: #2ecc71; }
        .icon-purple { background: rgba(155, 89, 182, 0.1); color: #9b59b6; }

        /* Badges Area */
        .badge-grid {
            display: grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 15px;
        }
        .badge-item {
            background: #fff; border: 1px solid #eee; border-radius: 15px;
            padding: 15px; text-align: center; transition: 0.3s;
        }
        .badge-item:hover { border-color: var(--gold); box-shadow: 0 5px 15px rgba(212, 175, 55, 0.2); }
        .badge-img { width: 50px; height: 50px; margin-bottom: 10px; opacity: 0.8; }
        .badge-item.locked { filter: grayscale(100%); opacity: 0.5; }

        /* Modal Styles */
        .modal-content { border-radius: 20px; border: none; overflow: hidden; }
        .modal-header { background: var(--primary-green); color: white; border: none; }
        .btn-close { filter: invert(1); }
        .form-control { border-radius: 10px; padding: 12px; }
        .img-preview-edit { width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 3px solid #ddd; margin-bottom: 15px; }

                        footer { background: #222; color: #aaa; padding: 60px 0 20px; }
        .footer-heading { color: white; font-family: 'Great Vibes'; font-size: 2rem; margin-bottom: 20px; }
    </style>
</head>
<body>

    <?php include 'header.php'; ?>

    <main class="container profile-container">
        
        <?php if (!empty($mensagem_sucesso)): ?>
            <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?php echo $mensagem_sucesso; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($mensagem_erro)): ?>
            <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> <?php echo $mensagem_erro; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            
            <div class="col-lg-4" data-aos="fade-right">
                <div class="profile-card">
                    <div class="profile-cover"></div>
                    <div class="profile-avatar-container">
                        <img src="<?php echo htmlspecialchars($foto_perfil_src); ?>" alt="Foto de Perfil" class="big-avatar">
                    </div>
                    <div class="profile-info">
                        <h3 class="user-name"><?php echo htmlspecialchars($nome_usuario); ?></h3>
                        <span class="user-role">
                            <i class="fas fa-feather-alt me-1"></i> <?php echo $tipo_usuario; ?>
                        </span>
                        <p class="text-muted small mb-3"><i class="far fa-calendar-alt me-1"></i> Membro desde <?php echo $data_membro; ?></p>

                        <div class="stats-row">
                            <div class="stat-box">
                                <span class="stat-num"><?php echo $stats['seguidores']; ?></span>
                                <span class="stat-label">Seguidores</span>
                            </div>
                            <div class="stat-box">
                                <span class="stat-num"><?php echo $stats['historias']; ?></span>
                                <span class="stat-label">Obras</span>
                            </div>
                        </div>

                        <button class="btn btn-edit" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                            <i class="fas fa-cog me-1"></i> Editar Perfil
                        </button>
                    </div>
                </div>

                <div class="content-card" data-aos="fade-up" data-aos-delay="100">
                    <h5 class="card-title fs-6"><i class="fas fa-bullhorn me-2 text-warning"></i> Sobre a Comunidade</h5>
                    <p class="small text-muted mb-0">Participe dos desafios semanais para ganhar novas badges e aumentar sua visibilidade!</p>
                </div>
            </div>

            <div class="col-lg-8" data-aos="fade-left">
                
                <div class="content-card">
                    <h4 class="card-title">Estatísticas de Escrita</h4>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="stat-highlight">
                                <div class="stat-icon icon-blue"><i class="fas fa-keyboard"></i></div>
                                <div>
                                    <h4 class="mb-0 fw-bold"><?php echo number_format($stats['palavras'], 0, ',', '.'); ?></h4>
                                    <small class="text-muted">Palavras Escritas</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="stat-highlight">
                                <div class="stat-icon icon-green"><i class="fas fa-book-open"></i></div>
                                <div>
                                    <h4 class="mb-0 fw-bold"><?php echo $stats['historias']; ?></h4>
                                    <small class="text-muted">Histórias Publicadas</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="stat-highlight">
                                <div class="stat-icon icon-purple"><i class="fas fa-comments"></i></div>
                                <div>
                                    <h4 class="mb-0 fw-bold"><?php echo $stats['comentarios']; ?></h4>
                                    <small class="text-muted">Interações Feitas</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="content-card">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="card-title mb-0">Galeria de Conquistas</h4>
                        <span class="badge bg-light text-dark border">Ver todas</span>
                    </div>
                    
                    <div class="badge-grid">
                        <?php if (!empty($badges)): ?>
                            <?php foreach($badges as $badge): ?>
                                <?php endforeach; ?>
                        <?php else: ?>
                            <div class="badge-item" title="Primeiros Passos">
                                <i class="fas fa-shoe-prints fa-2x text-primary mb-2"></i>
                                <p class="small mb-0 fw-bold">Início</p>
                            </div>
                            <div class="badge-item" title="Primeira História">
                                <i class="fas fa-pen-nib fa-2x text-success mb-2"></i>
                                <p class="small mb-0 fw-bold">Autor</p>
                            </div>
                            <div class="badge-item locked" title="Bloqueado: 10 Mil Palavras">
                                <i class="fas fa-lock fa-2x mb-2"></i>
                                <p class="small mb-0">Mestre</p>
                            </div>
                            <div class="badge-item locked">
                                <i class="fas fa-lock fa-2x mb-2"></i>
                                <p class="small mb-0">Crítico</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (empty($badges)): ?>
                        <div class="mt-4 text-center p-3 bg-light rounded">
                            <small class="text-muted">Continue escrevendo para desbloquear novas insígnias!</small>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
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

    <div class="modal fade" id="editProfileModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="fas fa-user-edit me-2"></i>Editar Perfil</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form method="POST" action="perfil.php" enctype="multipart/form-data">
                        <div class="text-center mb-4">
                            <img id="preview_img" src="<?php echo htmlspecialchars($foto_perfil_src); ?>" class="img-preview-edit">
                            <div>
                                <label for="nova_foto_perfil" class="btn btn-sm btn-outline-secondary rounded-pill">
                                    <i class="fas fa-camera me-1"></i> Alterar Foto
                                </label>
                                <input type="file" id="nova_foto_perfil" name="nova_foto_perfil" accept="image/*" class="d-none" onchange="previewImage(event)">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="novo_nome" class="form-label fw-bold small text-muted">Nome de Exibição</label>
                            <input type="text" class="form-control" id="novo_nome" name="novo_nome" value="<?php echo htmlspecialchars($nome_usuario); ?>" required>
                        </div>

                        <input type="hidden" name="atualizar_perfil" value="1">
                        
                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-success fw-bold py-2" style="background-color: var(--primary-green); border:none;">Salvar Alterações</button>
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

        // Script para Preview da Imagem no Modal
        function previewImage(event) {
            var reader = new FileReader();
            reader.onload = function(){
                var output = document.getElementById('preview_img');
                output.src = reader.result;
            };
            if(event.target.files[0]){
                reader.readAsDataURL(event.target.files[0]);
            }
        }
    </script>
</body>
</html>
<?php if(isset($conn)) $conn->close(); ?>