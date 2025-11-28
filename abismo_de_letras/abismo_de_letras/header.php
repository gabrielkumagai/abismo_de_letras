<?php
// Previne erros se a sessão já estiver aberta
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Variáveis de fallback para evitar erros de "Undefined variable"
$nome_usuario_header = isset($_SESSION['nome']) ? explode(' ', $_SESSION['nome'])[0] : 'Visitante';
$foto_perfil_header = isset($_SESSION['foto_perfil']) && !empty($_SESSION['foto_perfil']) ? $_SESSION['foto_perfil'] : 'https://cdn-icons-png.flaticon.com/512/3135/3135715.png';

// Lógica simples para marcar o link ativo (opcional, baseada na URL atual)
$pagina_atual = basename($_SERVER['PHP_SELF']);
?>

<style>
    /* --- CSS Específico do Header (Encapsulado para funcionar em qualquer include) --- */
    :root {
        --nav-glass: rgba(255, 255, 255, 0.95);
        --nav-text: #4a5d3f; /* Verde Escuro */
        --nav-gold: #d4af37; /* Dourado */
        --nav-green: #7d8c66; /* Verde Sálvia */
    }

    .navbar-premium {
        background: var(--nav-glass);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        padding: 0.8rem 0;
        transition: all 0.3s ease;
    }

    /* Logo */
    .brand-font-header {
        font-family: 'Great Vibes', cursive;
        font-size: 2rem;
        color: var(--nav-text) !important;
        transition: transform 0.3s;
    }
    .brand-font-header:hover {
        transform: scale(1.05);
    }

    /* Links de Navegação */
    .nav-link-custom {
        color: var(--nav-text) !important;
        font-family: 'Lato', sans-serif;
        font-weight: 600;
        font-size: 0.95rem;
        margin: 0 12px;
        position: relative;
        padding-bottom: 5px;
        transition: color 0.3s;
    }

    /* Animação do Sublinhado Dourado */
    .nav-link-custom::after {
        content: '';
        position: absolute;
        width: 0;
        height: 2px;
        bottom: 0;
        left: 0;
        background-color: var(--nav-gold);
        transition: width 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    }

    .nav-link-custom:hover::after,
    .nav-link-custom.active::after {
        width: 100%;
    }

    .nav-link-custom:hover {
        color: var(--nav-green) !important;
    }

    /* Botão de Perfil */
    .profile-btn-header {
        display: flex;
        align-items: center;
        background: white;
        border: 1px solid rgba(0,0,0,0.08);
        padding: 6px 16px 6px 6px;
        border-radius: 50px;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .profile-btn-header:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(125, 140, 102, 0.15);
        border-color: var(--nav-green);
    }

    .profile-img-header {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid var(--nav-gold);
        margin-right: 10px;
    }

    .profile-name-header {
        font-weight: 700;
        color: var(--nav-text);
        font-size: 0.9rem;
        font-family: 'Lato', sans-serif;
    }

    /* Dropdown Menu */
    .dropdown-menu-custom {
        border: none;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        padding: 10px;
        margin-top: 15px;
    }

    .dropdown-item-custom {
        border-radius: 8px;
        padding: 10px 15px;
        font-size: 0.9rem;
        transition: all 0.2s;
        font-weight: 500;
        color: #555;
    }

    .dropdown-item-custom:hover {
        background-color: rgba(125, 140, 102, 0.1); /* Verde bem clarinho */
        color: var(--nav-text);
        transform: translateX(3px);
    }

    .dropdown-item-custom.text-danger:hover {
        background-color: rgba(220, 53, 69, 0.1);
    }
</style>

<nav class="navbar navbar-expand-lg fixed-top navbar-premium">
    <div class="container">
        <a class="navbar-brand brand-font-header" href="index.php">
            <i class="fas fa-feather-alt text-success me-2"></i>Abismo de Letras
        </a>
        
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navContent">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse justify-content-end" id="navContent">
            <ul class="navbar-nav align-items-center">
                <li class="nav-item">
                    <a class="nav-link nav-link-custom <?php echo ($pagina_atual == 'index.php') ? 'active' : ''; ?>" href="index.php">Início</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link nav-link-custom <?php echo ($pagina_atual == 'biblioteca.php' || $pagina_atual == 'historias.php') ? 'active' : ''; ?>" href="biblioteca.php">Biblioteca</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link nav-link-custom <?php echo ($pagina_atual == 'enem.php') ? 'active' : ''; ?>" href="enem.php">Módulo ENEM</a>
                </li>
                
                <?php if (isset($_SESSION['id_usuario'])): ?>
                    <li class="nav-item ms-lg-2">
                        <a href="publicar.php" class="btn btn-sm btn-outline-success rounded-pill px-4 py-2 fw-bold" style="border-width: 2px;">
                            <i class="fas fa-pen-nib me-1"></i> Escrever
                        </a>
                    </li>
                    
                    <li class="nav-item dropdown ms-lg-4 mt-3 mt-lg-0">
                        <a class="nav-link p-0" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="profile-btn-header">
                                <img src="<?php echo htmlspecialchars($foto_perfil_header); ?>" alt="Foto" class="profile-img-header">
                                <span class="profile-name-header me-2"><?php echo htmlspecialchars($nome_usuario_header); ?></span>
                                <i class="fas fa-chevron-down small text-muted"></i>
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-custom" aria-labelledby="userDropdown">
                            <li><h6 class="dropdown-header text-uppercase small fw-bold text-muted letter-spacing-1">Minha Conta</h6></li>
                            <li>
                                <a class="dropdown-item dropdown-item-custom" href="perfil.php">
                                    <i class="fas fa-user-circle me-2 text-success"></i> Meu Perfil
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item dropdown-item-custom" href="historias.php?autor=<?php echo $_SESSION['id_usuario']; ?>">
                                    <i class="fas fa-book me-2 text-success"></i> Minhas Histórias
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item dropdown-item-custom text-danger fw-bold" href="logout.php">
                                    <i class="fas fa-sign-out-alt me-2"></i> Sair
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item ms-lg-3">
                        <a href="login.php" class="btn btn-sm btn-outline-dark rounded-pill px-4 py-2 fw-bold">Entrar</a>
                    </li>
                    <li class="nav-item ms-2">
                        <a href="cadastro.php" class="btn btn-sm btn-dark rounded-pill px-4 py-2 fw-bold" style="background-color: var(--nav-text); border:none;">Criar Conta</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>