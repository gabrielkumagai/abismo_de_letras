<?php
include 'conexao.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}

$id_usuario_logado = (int)$_SESSION['id_usuario'];
$id_perfil = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_perfil === 0) { header('Location: historias.php'); exit; }
if ($id_perfil === $id_usuario_logado) { header('Location: perfil.php'); exit; }

// Busca Dados
$sql_user = "SELECT nome, tipo_usuario, foto_perfil, data_cadastro FROM usuarios WHERE id_usuario = ?";
$stmt = $conn->prepare($sql_user);
$stmt->bind_param("i", $id_perfil);
$stmt->execute();
$user_data = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user_data) { echo "Usuário não encontrado."; exit; }

$foto_perfil_src = !empty($user_data['foto_perfil']) ? $user_data['foto_perfil'] : 'https://cdn-icons-png.flaticon.com/512/3135/3135715.png';

// Checa Seguidor
$is_following = false;
$sql_check = "SELECT 1 FROM seguidores WHERE id_seguidor = ? AND id_seguido = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("ii", $id_usuario_logado, $id_perfil);
$stmt_check->execute();
if ($stmt_check->get_result()->num_rows > 0) $is_following = true;
$stmt_check->close();

// Stats (Simulado/Count)
$stats = ['historias' => 0, 'seguidores' => 0];
$res = $conn->query("SELECT COUNT(*) FROM historias WHERE id_autor = $id_perfil");
$stats['historias'] = $res->fetch_row()[0];
$res = $conn->query("SELECT COUNT(*) FROM seguidores WHERE id_seguido = $id_perfil");
$stats['seguidores'] = $res->fetch_row()[0];

// Badges
$sql_badges = "SELECT b.nome, b.icone FROM usuario_badge ub JOIN badges b ON ub.id_badge = b.id_badge WHERE ub.id_usuario = ?";
$stmt_b = $conn->prepare($sql_badges);
$stmt_b->bind_param("i", $id_perfil);
$stmt_b->execute();
$badges = $stmt_b->get_result();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de <?php echo htmlspecialchars($user_data['nome']); ?> - Abismo de Letras</title>
    
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

        /* --- PERFIL LAYOUT --- */
        .profile-container { padding-top: 100px; padding-bottom: 60px; }

        .profile-card {
            background: white; border-radius: 20px; overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08); margin-bottom: 30px;
        }

        .profile-cover {
            height: 120px;
            background: linear-gradient(135deg, var(--dark-green), var(--primary-green));
        }
        
        .profile-avatar-container { margin-top: -60px; text-align: center; }

        .big-avatar {
            width: 120px; height: 120px; border-radius: 50%;
            object-fit: cover; border: 5px solid white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.15); background: white;
        }

        .profile-info { padding: 20px; text-align: center; }
        .user-name { font-family: 'Playfair Display', serif; font-weight: 700; margin-bottom: 5px; color: var(--dark-green); }
        .user-role { 
            display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 0.8rem; 
            font-weight: bold; background: rgba(74, 93, 63, 0.1); color: var(--dark-green); margin-bottom: 15px;
        }

        .stats-row {
            display: flex; justify-content: space-around; padding: 15px 0;
            border-top: 1px solid #eee; border-bottom: 1px solid #eee; margin-bottom: 20px;
        }
        .stat-num { font-weight: 800; font-size: 1.2rem; display: block; }
        .stat-label { font-size: 0.75rem; color: #888; text-transform: uppercase; }

        .btn-follow {
            width: 100%; border-radius: 50px; padding: 10px; font-weight: 600; transition: 0.3s;
        }
        .btn-follow-active { background: var(--gold); color: white; border: none; }
        .btn-follow-active:hover { background: #b08d1e; }
        .btn-unfollow { background: transparent; border: 2px solid #dc3545; color: #dc3545; }
        .btn-unfollow:hover { background: #dc3545; color: white; }

        .content-card {
            background: white; border-radius: 20px; padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05); margin-bottom: 30px;
        }
        .card-title {
            font-family: 'Playfair Display', serif; font-weight: 700; color: var(--dark-green);
            margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid #eee;
        }

        .badge-grid { display: flex; gap: 10px; flex-wrap: wrap; }
        .badge-item {
            background: #f9f9f9; padding: 10px 15px; border-radius: 10px;
            border: 1px solid #eee; display: flex; align-items: center; gap: 10px;
        }
        .badge-icon { font-size: 1.2rem; }

        footer { margin-top: auto; background: #222; color: #aaa; padding: 40px 0; text-align: center; }
    </style>
</head>
<body>

    <?php include 'header.php'; ?>

    <main class="container profile-container">
        <div class="row g-4 justify-content-center">
            
            <div class="col-lg-4" data-aos="fade-up">
                <div class="profile-card">
                    <div class="profile-cover"></div>
                    <div class="profile-avatar-container">
                        <img src="<?php echo htmlspecialchars($foto_perfil_src); ?>" class="big-avatar">
                    </div>
                    <div class="profile-info">
                        <h3 class="user-name"><?php echo htmlspecialchars($user_data['nome']); ?></h3>
                        <span class="user-role"><?php echo ucfirst($user_data['tipo_usuario']); ?></span>
                        
                        <div class="stats-row">
                            <div><span class="stat-num"><?php echo $stats['seguidores']; ?></span><span class="stat-label">Seguidores</span></div>
                            <div><span class="stat-num"><?php echo $stats['historias']; ?></span><span class="stat-label">Obras</span></div>
                        </div>

                        <?php if ($is_following): ?>
                            <a href="seguir.php?id=<?php echo $id_perfil; ?>&action=unfollow" class="btn btn-follow btn-unfollow">Deixar de Seguir</a>
                        <?php else: ?>
                            <a href="seguir.php?id=<?php echo $id_perfil; ?>&action=follow" class="btn btn-follow btn-follow-active"><i class="fas fa-plus me-1"></i> Seguir</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                
                <div class="content-card" data-aos="fade-up" data-aos-delay="100">
                    <h4 class="card-title"><i class="fas fa-trophy me-2 text-warning"></i> Conquistas</h4>
                    <div class="badge-grid">
                        <?php if ($badges->num_rows > 0): ?>
                            <?php while($badge = $badges->fetch_assoc()): ?>
                                <div class="badge-item">
                                    <span class="badge-icon">🏅</span> 
                                    <span class="fw-bold small"><?php echo htmlspecialchars($badge['nome']); ?></span>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p class="text-muted small">Este autor ainda está começando sua jornada de conquistas.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="content-card" data-aos="fade-up" data-aos-delay="200">
                    <h4 class="card-title"><i class="fas fa-book-open me-2 text-success"></i> Portfólio Literário</h4>
                    <p class="text-muted mb-4">Explore as histórias criadas por <?php echo htmlspecialchars($user_data['nome']); ?>.</p>
                    <a href="historias.php?autor=<?php echo $id_perfil; ?>" class="btn btn-outline-dark rounded-pill px-4">
                        Ver Todas as Histórias <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>

            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <p class="mb-1">&copy; 2025 Abismo de Letras.</p>
            <p class="small opacity-75">Projeto de TCC - Etec Monsenhor Antonio Magliano.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>AOS.init({ duration: 800, once: true });</script>
</body>
</html>
<?php $conn->close(); ?>