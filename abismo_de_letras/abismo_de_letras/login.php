<?php
include 'conexao.php';
session_start();

$mensagem = "";
$tipo_alerta = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $conn->real_escape_string($_POST['email']);
    $senha_digitada = $_POST['senha'];

    $sql = "SELECT id_usuario, nome, senha, tipo_usuario FROM usuarios WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $usuario = $resultado->fetch_assoc();
        if (password_verify($senha_digitada, $usuario['senha'])) {
            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            $_SESSION['nome'] = $usuario['nome'];
            $_SESSION['tipo_usuario'] = $usuario['tipo_usuario'];
            header('Location: index.php'); 
            exit;
        } else {
            $mensagem = "Senha incorreta.";
            $tipo_alerta = "danger";
        }
    } else {
        $mensagem = "E-mail não encontrado.";
        $tipo_alerta = "warning";
    }
    $stmt->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Abismo de Letras</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Playfair+Display:wght@400;700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-green: #7d8c66; /* Verde Sálvia da Home */
            --dark-green: #4a5d3f;
            --gold: #d4af37;
            --glass-bg: rgba(255, 255, 255, 0.25); /* Vidro mais claro */
            --glass-border: rgba(255, 255, 255, 0.5);
        }

        body {
            /* Fundo escurecido para contraste */
            background: linear-gradient(rgba(45, 56, 38, 0.6), rgba(45, 56, 38, 0.6)), url('https://images.unsplash.com/photo-1519681393784-d120267933ba?q=80&w=2000&auto=format&fit=crop');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            font-family: 'Lato', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        /* Cartão de Vidro (Glassmorphism) */
        .glass-card {
            background: var(--glass-bg);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4);
            padding: 3rem 2.5rem;
            max-width: 420px;
            width: 100%;
            position: relative;
            overflow: hidden;
        }

        /* Elemento decorativo dourado no topo */
        .glass-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, transparent, var(--gold), transparent);
        }

        .brand-logo {
            font-family: 'Great Vibes', cursive;
            font-size: 3rem;
            color: white;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
            text-decoration: none;
            display: block;
            margin-bottom: 0.5rem;
            transition: transform 0.3s;
        }
        .brand-logo:hover { transform: scale(1.05); color: #f0f0f0; }

        h2 {
            font-family: 'Playfair Display', serif;
            color: white;
            font-weight: 700;
            letter-spacing: 1px;
            margin-bottom: 2rem;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        /* Inputs Modernos */
        .form-floating > .form-control {
            background: rgba(255, 255, 255, 0.9);
            border: 2px solid transparent;
            border-radius: 12px;
            height: 55px;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        .form-floating > label { color: #666; font-size: 0.9rem; padding-top: 0.8rem; }

        .form-floating > .form-control:focus {
            background: #fff;
            border-color: var(--primary-green);
            box-shadow: 0 0 0 4px rgba(125, 140, 102, 0.2);
        }

        /* Botão Principal */
        .btn-login {
            background: linear-gradient(135deg, var(--dark-green), var(--primary-green));
            border: none;
            border-radius: 50px;
            padding: 12px;
            font-weight: bold;
            font-family: 'Playfair Display', serif;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: white;
            width: 100%;
            margin-top: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(125, 140, 102, 0.4);
            filter: brightness(1.1);
        }

        /* Links */
        .links-container a {
            color: rgba(255,255,255,0.9);
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s;
            font-weight: 400;
        }
        .links-container a:hover {
            color: var(--gold);
            text-decoration: underline;
        }
        
        /* Botão Voltar Flutuante */
        .btn-back {
            position: absolute;
            top: 20px;
            left: 20px;
            color: rgba(255,255,255,0.7);
            font-size: 1.5rem;
            transition: all 0.3s;
        }
        .btn-back:hover { color: var(--gold); transform: translateX(-5px); }

    </style>
</head>
<body>

    <a href="index.php" class="btn-back" title="Voltar para Home"><i class="fas fa-arrow-left"></i></a>

    <div class="glass-card text-center" data-aos="zoom-in" data-aos-duration="1000">
        
        <a href="index.php" class="brand-logo">
            <i class="fas fa-feather-alt"></i> Abismo de Letras
        </a>
        <h2>Bem-vindo de volta</h2>

        <?php if (!empty($mensagem)): ?>
            <div class="alert alert-<?php echo $tipo_alerta; ?> alert-dismissible fade show shadow-sm border-0" role="alert" style="border-radius: 12px; font-size: 0.9rem;">
                <i class="fas fa-info-circle me-2"></i> <?php echo $mensagem; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php" class="mt-4">
            
            <div class="form-floating mb-3">
                <input type="email" class="form-control" id="email" name="email" placeholder="nome@exemplo.com" required>
                <label for="email"><i class="fas fa-envelope me-2"></i>E-mail</label>
            </div>

            <div class="form-floating mb-4">
                <input type="password" class="form-control" id="senha" name="senha" placeholder="Senha" required>
                <label for="senha"><i class="fas fa-lock me-2"></i>Senha</label>
            </div>

            <button type="submit" class="btn btn-login">Entrar</button>

            <div class="d-flex justify-content-between mt-4 links-container">
                <a href="cadastro.php" class="fw-bold">Criar conta nova</a>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init();
    </script>
</body>
</html>