<?php
include 'conexao.php';
session_start(); // Inicia a sessão aqui, no topo

$mensagem = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $conn->real_escape_string($_POST['email']);
    $senha_digitada = $_POST['senha'];

    // Busca o usuário pelo email
    $sql = "SELECT id_usuario, nome, senha, tipo_usuario FROM usuarios WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $usuario = $resultado->fetch_assoc();
        
        // Verifica a senha hasheada
        if (password_verify($senha_digitada, $usuario['senha'])) {
            // Sucesso no login, cria a sessão
            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            $_SESSION['nome'] = $usuario['nome'];
            $_SESSION['tipo_usuario'] = $usuario['tipo_usuario'];

            header('Location: index.php'); // Redireciona para a página inicial
            exit;
        } else {
            $mensagem = "Senha incorreta.";
        }
    } else {
        $mensagem = "E-mail não encontrado.";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Abismo de Letras</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; // Inclui o cabeçalho ?>
    <main class="container">
        <h2>Acesso à Plataforma</h2>
        <div class="card">
            <?php if (!empty($mensagem)): ?>
                <div class="alert-success"><?php echo $mensagem; ?></div>
            <?php endif; ?>
            <form method="POST" action="login.php">
                <label for="email">E-mail:</label>
                <input type="email" id="email" name="email" required>

                <label for="senha">Senha:</label>
                <input type="password" id="senha" name="senha" required>

                <button type="submit" class="btn-cta">Entrar</button>
            </form>
            <p style="margin-top: 20px;">Não tem conta? <a href="cadastro.php">Cadastre-se aqui.</a></p>
        </div>
    </main>
    <footer><p>&copy; 2025 Abismo de Letras.</p></footer>
</body>
</html>
<?php $conn->close(); ?>