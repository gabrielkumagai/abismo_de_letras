<?php
include 'conexao.php';
session_start(); // Sessão iniciada aqui, no topo

$mensagem = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $conn->real_escape_string($_POST['nome']);
    $email = $conn->real_escape_string($_POST['email']);
    $senha_pura = $_POST['senha'];
    $tipo_usuario = $conn->real_escape_string($_POST['tipo_usuario']);

    // Hash da senha
    $senha_hash = password_hash($senha_pura, PASSWORD_DEFAULT);

    // Query de inserção
    $sql = "INSERT INTO usuarios (nome, email, senha, tipo_usuario) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("ssss", $nome, $email, $senha_hash, $tipo_usuario);
        if ($stmt->execute()) {
            $mensagem = "Cadastro realizado com sucesso! Você pode fazer o login.";
        } else {
            if ($conn->errno == 1062) {
                $mensagem = "Erro: Este e-mail já está cadastrado.";
            } else {
                $mensagem = "Erro ao cadastrar: " . $stmt->error;
            }
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - Abismo de Letras</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; // Inclui o cabeçalho ?>
    <main class="container">
        <h2>Cadastre-se na Comunidade</h2>
        <div class="card">
            <?php if (!empty($mensagem)): ?>
                <div class="alert-success"><?php echo $mensagem; ?></div>
            <?php endif; ?>
            <form method="POST" action="cadastro.php">
                <label for="nome">Nome:</label>
                <input type="text" id="nome" name="nome" required>

                <label for="email">E-mail:</label>
                <input type="email" id="email" name="email" required>

                <label for="senha">Senha:</label>
                <input type="password" id="senha" name="senha" required>

                <label for="tipo_usuario">Perfil:</label>
                <select id="tipo_usuario" name="tipo_usuario" required>
                    <option value="escritor">Escritor (Pode publicar)</option>
                    <option value="leitor">Leitor (Pode interagir)</option>
                    <option value="estudante">Estudante (Foco na redação ENEM)</option>
                </select>

                <button type="submit" class="btn-cta">Finalizar Cadastro</button>
            </form>
        </div>
    </main>
    <footer><p>&copy; 2025 Abismo de Letras.</p></footer>
</body>
</html>
<?php $conn->close(); ?>