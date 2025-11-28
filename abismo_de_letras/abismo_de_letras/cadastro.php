<?php
include 'conexao.php';
session_start();

$mensagem = "";
$tipo_alerta = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $conn->real_escape_string($_POST['nome']);
    $email = $conn->real_escape_string($_POST['email']);
    $senha_pura = $_POST['senha'];
    $tipo_usuario = $conn->real_escape_string($_POST['tipo_usuario']);
    $foto_perfil_path = 'default.png'; 

    // Upload
    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] == 0) {
        $target_dir = "uploads/perfil/";
        if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }
        $file_extension = pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION);
        $file_name = uniqid('perfil_') . '.' . $file_extension;
        $target_file = $target_dir . $file_name;

        if (move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $target_file)) {
            $foto_perfil_path = $target_file;
        } else {
            $mensagem .= " Aviso: Falha no upload da foto.";
        }
    }

    $senha_hash = password_hash($senha_pura, PASSWORD_DEFAULT);
    $sql = "INSERT INTO usuarios (nome, email, senha, tipo_usuario, foto_perfil) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("sssss", $nome, $email, $senha_hash, $tipo_usuario, $foto_perfil_path);
        if ($stmt->execute()) {
            $mensagem = "Conta criada com sucesso!";
            $tipo_alerta = "success";
            header("refresh:2;url=login.php"); 
        } else {
            $tipo_alerta = "danger";
            if ($conn->errno == 1062) {
                $mensagem = "Ops! Este e-mail já está em uso.";
            } else {
                $mensagem = "Erro: " . $stmt->error;
            }
        }
        $stmt->close();
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - Abismo de Letras</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Playfair+Display:wght@400;700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        :root {
            --primary-green: #7d8c66;
            --dark-green: #4a5d3f;
            --gold: #d4af37;
            --glass-bg: rgba(255, 255, 255, 0.25);
            --glass-border: rgba(255, 255, 255, 0.5);
        }

        body {
            background: linear-gradient(rgba(45, 56, 38, 0.6), rgba(45, 56, 38, 0.6)), url('https://images.unsplash.com/photo-1519681393784-d120267933ba?q=80&w=2000&auto=format&fit=crop');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            font-family: 'Lato', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px; /* Padding extra para mobile */
        }

        .glass-card {
            background: var(--glass-bg);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4);
            padding: 3rem;
            max-width: 650px; /* Mais largo */
            width: 100%;
            position: relative;
        }

        .glass-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 100%; height: 5px;
            background: linear-gradient(90deg, transparent, var(--gold), transparent);
        }

        .brand-logo {
            font-family: 'Great Vibes', cursive;
            font-size: 2.5rem;
            color: white;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
            text-decoration: none;
            display: block;
        }

        h2 {
            font-family: 'Playfair Display', serif;
            color: white;
            font-weight: 700;
            margin-bottom: 2rem;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        /* Estilo dos Inputs */
        .form-floating > .form-control, .form-select {
            background: rgba(255, 255, 255, 0.9);
            border: 2px solid transparent;
            border-radius: 12px;
            height: 55px;
        }
        
        .form-select { padding-top: 0.5rem; padding-bottom: 0.5rem; }

        .form-floating > .form-control:focus, .form-select:focus {
            background: #fff;
            border-color: var(--primary-green);
            box-shadow: 0 0 0 4px rgba(125, 140, 102, 0.2);
        }

        /* Botão */
        .btn-register {
            background: linear-gradient(135deg, var(--dark-green), var(--primary-green));
            border: none;
            border-radius: 50px;
            padding: 14px;
            font-weight: bold;
            font-family: 'Playfair Display', serif;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: white;
            width: 100%;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        }

        .btn-register:hover {
            transform: translateY(-2px);
            filter: brightness(1.1);
            color: white;
        }

        /* Upload Container Customizado */
        .upload-container {
            background: rgba(255, 255, 255, 0.8);
            border-radius: 15px;
            padding: 10px;
            display: flex;
            align-items: center;
            border: 2px dashed rgba(125, 140, 102, 0.5);
            transition: all 0.3s;
        }
        .upload-container:hover { background: white; border-color: var(--primary-green); }

        .img-preview {
            width: 60px; height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--gold);
            display: none;
            margin-left: auto;
        }
        
        .upload-icon {
            font-size: 1.5rem; color: var(--dark-green); margin: 0 15px;
        }

        .link-login {
            color: white; font-weight: bold; text-decoration: none;
        }
        .link-login:hover { text-decoration: underline; color: var(--gold); }

    </style>
</head>
<body>

    <div class="glass-card" data-aos="fade-up" data-aos-duration="1000">
        <div class="text-center">
            <a href="index.php" class="brand-logo"><i class="fas fa-feather-alt"></i> Abismo de Letras</a>
            <h2>Criar nova conta</h2>
        </div>

        <?php if (!empty($mensagem)): ?>
            <div class="alert alert-<?php echo $tipo_alerta; ?> alert-dismissible fade show rounded-4" role="alert">
                <i class="fas fa-info-circle me-2"></i> <?php echo $mensagem; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form method="POST" action="cadastro.php" enctype="multipart/form-data" onsubmit="return validarSenha()" class="row g-3">
            
            <div class="col-12">
                <div class="form-floating">
                    <input type="text" class="form-control" id="nome" name="nome" placeholder="Nome" required>
                    <label for="nome"><i class="fas fa-user me-2"></i>Nome Completo</label>
                </div>
            </div>

            <div class="col-md-7">
                <div class="form-floating">
                    <input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
                    <label for="email"><i class="fas fa-envelope me-2"></i>Email</label>
                </div>
            </div>
            <div class="col-md-5">
                <div class="form-floating">
                    <select id="tipo_usuario" name="tipo_usuario" class="form-select" required>
                        <option value="" disabled selected>Selecione...</option>
                        <option value="escritor">Escritor (Publicar)</option>
                        <option value="leitor">Leitor (Ler/Comentar)</option>
                        <option value="estudante">Estudante (ENEM)</option>
                    </select>
                    <label for="tipo_usuario"><i class="fas fa-users me-2"></i>Perfil</label>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-floating">
                    <input type="password" class="form-control" id="senha" name="senha" placeholder="Senha" required>
                    <label for="senha"><i class="fas fa-lock me-2"></i>Criar Senha</label>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-floating">
                    <input type="password" class="form-control" id="confirmar_senha" placeholder="Confirmar" required>
                    <label for="confirmar_senha"><i class="fas fa-check-double me-2"></i>Confirmar Senha</label>
                </div>
            </div>
            
            <div class="col-12">
                <small id="erro-senha" class="text-danger fw-bold bg-white px-2 rounded shadow-sm" style="display:none;"></small>
            </div>

            <div class="col-12">
                <label class="text-white mb-2 small ps-2">Foto de Perfil (Opcional)</label>
                <div class="upload-container">
                    <i class="fas fa-cloud-upload-alt upload-icon"></i>
                    <input type="file" class="form-control form-control-sm border-0 bg-transparent" id="foto_perfil" name="foto_perfil" accept="image/*" onchange="previewImage(event)">
                    <img id="preview_perfil_cadastro" src="#" alt="Preview" class="img-preview shadow-sm">
                </div>
            </div>

            <div class="col-12 mt-4">
                <button type="submit" class="btn btn-register">Concluir Cadastro</button>
            </div>
            
            <div class="col-12 text-center mt-3">
                <span class="text-white opacity-75">Já é membro?</span> 
                <a href="login.php" class="link-login ms-1">Entrar</a>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init();

        function previewImage(event) {
            var reader = new FileReader();
            reader.onload = function(){
                var output = document.getElementById('preview_perfil_cadastro');
                output.src = reader.result;
                output.style.display = 'block';
            };
            if(event.target.files[0]){
                reader.readAsDataURL(event.target.files[0]);
            }
        }

        function validarSenha() {
            var senha = document.getElementById("senha").value;
            var confirmacao = document.getElementById("confirmar_senha").value;
            var erro = document.getElementById("erro-senha");

            if (senha !== confirmacao) {
                erro.style.display = "inline-block";
                erro.innerHTML = "<i class='fas fa-exclamation-triangle me-1'></i> As senhas não conferem!";
                return false; 
            }
            erro.style.display = "none";
            return true;
        }
    </script>
</body>
</html>