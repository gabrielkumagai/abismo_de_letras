<?php
include 'conexao.php';
session_start();

// -----------------------------------------------------
// VERIFICAÇÃO RIGOROSA DA SESSÃO E DO ID DO AUTOR
// -----------------------------------------------------
if (!isset($_SESSION['id_usuario']) || !is_numeric($_SESSION['id_usuario'])) {
    // Redireciona se o usuário não estiver logado ou se o ID não for um número válido
    header('Location: login.php');
    exit;
}

// Garante que $id_autor seja um inteiro válido, compatível com a chave estrangeira
$id_autor = (int)$_SESSION['id_usuario']; 
$mensagem = "";
$id_original_url = isset($_GET['continuar']) ? (int)$_GET['continuar'] : NULL;
$historia_original_titulo = "";

// -----------------------------------------------------
// Lógica de Processamento do Formulário
// -----------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titulo = $conn->real_escape_string($_POST['titulo']);
    $conteudo = $conn->real_escape_string($_POST['conteudo']);
    $id_historia_original = isset($_POST['id_original']) && $_POST['id_original'] != 0 ? (int)$_POST['id_original'] : NULL;
    $acesso = $conn->real_escape_string($_POST['acesso']);

    // Verifica se o id_autor é um número positivo antes de tentar inserir
    if ($id_autor <= 0) {
        $mensagem = "Erro: ID de autor inválido. Por favor, faça login novamente.";
    } else {
        // Prepara a query de inserção
        $sql = "INSERT INTO historias (id_autor, titulo, conteudo, acesso, id_historia_original) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("isssi", $id_autor, $titulo, $conteudo, $acesso, $id_historia_original);
            
            if ($stmt->execute()) { // LINHA 31 CORRIGIDA
                $mensagem = "Sua história/versão foi publicada com sucesso! Veja em <a href='historias.php'>Histórias Colaborativas</a>.";
                
                // Lógica para dar a Badge 'O Colaborador' se for uma continuação
                if ($id_historia_original !== NULL) {
                     $sql_give_badge = "INSERT IGNORE INTO usuario_badge (id_usuario, id_badge) SELECT ?, id_badge FROM badges WHERE nome = 'O Colaborador'";
                     $stmt_badge = $conn->prepare($sql_give_badge);
                     $stmt_badge->bind_param("i", $id_autor);
                     $stmt_badge->execute();
                     $stmt_badge->close();
                }
            } else {
                $mensagem = "Erro ao publicar: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// -----------------------------------------------------
// Lógica para carregar o título da história original (se for uma continuação via GET)
// -----------------------------------------------------
if ($id_original_url !== NULL && $id_original_url > 0) {
    $sql_original = "SELECT titulo FROM historias WHERE id_historia = ?";
    $stmt_original = $conn->prepare($sql_original);
    
    if ($stmt_original) {
        $stmt_original->bind_param("i", $id_original_url);
        $stmt_original->execute();
        $resultado = $stmt_original->get_result();
        
        if ($resultado->num_rows > 0) {
            $historia = $resultado->fetch_assoc();
            $historia_original_titulo = $historia['titulo'];
        }
        $stmt_original->close();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Publicar História - Abismo de Letras</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; // Inclui o cabeçalho ?>
    <main class="container">
        <h2><?php echo $id_original_url > 0 ? "Continuar História: " . htmlspecialchars($historia_original_titulo) : "Escreva uma Nova História"; ?></h2>
        <div class="card">
            <?php if (!empty($mensagem)): ?>
                <div class="alert-success"><?php echo $mensagem; ?></div>
            <?php endif; ?>
            <form method="POST" action="publicar.php">
                
                <?php if ($id_original_url > 0) : ?>
                    <input type="hidden" name="id_original" value="<?php echo $id_original_url; ?>">
                    <p style="color: var(--cor-secundaria); font-weight: bold;">Você está criando uma **versão alternativa/continuação** de: **<?php echo htmlspecialchars($historia_original_titulo); ?>**.</p>
                <?php endif; ?>

                <label for="titulo">Título da sua Versão/História:</label>
                <input type="text" id="titulo" name="titulo" required>

                <label for="conteudo">Conteúdo da História:</label>
                <textarea id="conteudo" name="conteudo" rows="15" required></textarea>

                <label for="acesso">Configuração de Acesso:</label>
                <select id="acesso" name="acesso" required>
                    <option value="publico">Público</option>
                    <option value="restrito">Restrito (Rascunho)</option>
                </select>

                <button type="submit" class="btn-cta">Publicar/Salvar</button>
            </form>
        </div>
    </main>
    <footer><p>&copy; 2025 Abismo de Letras.</p></footer>
</body>
</html>
<?php $conn->close(); ?>