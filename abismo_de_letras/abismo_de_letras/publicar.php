<?php
include 'conexao.php';
session_start();

if (!isset($_SESSION['id_usuario']) || !is_numeric($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}

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
    $id_genero = (int)$_POST['id_genero']; 
    $capa_imagem_path = 'default_capa.png';

    // --- LÓGICA DE UPLOAD DA IMAGEM DE CAPA ---
    if (isset($_FILES['capa_imagem']) && $_FILES['capa_imagem']['error'] == 0) {
        $target_dir = "uploads/capas/";
        if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }

        $file_extension = pathinfo($_FILES['capa_imagem']['name'], PATHINFO_EXTENSION);
        $file_name = uniqid('capa_') . '.' . $file_extension;
        $target_file = $target_dir . $file_name;

        if (move_uploaded_file($_FILES['capa_imagem']['tmp_name'], $target_file)) {
            $capa_imagem_path = $target_file;
        } else {
            $mensagem .= " Aviso: Falha ao fazer upload da imagem de capa.";
        }
    }
    // ------------------------------------------

    if ($id_autor <= 0) {
        $mensagem = "Erro: ID de autor inválido. Por favor, faça login novamente.";
    } else {
        $sql = "INSERT INTO historias (id_autor, titulo, conteudo, acesso, id_historia_original, capa_imagem, id_genero) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("isssisi", $id_autor, $titulo, $conteudo, $acesso, $id_historia_original, $capa_imagem_path, $id_genero);
            
            if ($stmt->execute()) {
                $novo_id = $conn->insert_id;
                
                // Lógica da Badge 'O Colaborador'
                if ($id_historia_original !== NULL) {
                     $sql_give_badge = "INSERT IGNORE INTO usuario_badge (id_usuario, id_badge) SELECT ?, id_badge FROM badges WHERE nome = 'O Colaborador'";
                     $stmt_badge = $conn->prepare($sql_give_badge);
                     $stmt_badge->bind_param("i", $id_autor);
                     $stmt_badge->execute();
                     $stmt_badge->close();
                }
                
                // *** REDIRECIONAMENTO PÓS-SUCESSO ***
                header('Location: ver_historia.php?id=' . $novo_id);
                exit;

            } else {
                $mensagem = "Erro ao publicar: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// Lógica para carregar o título da história original (continuação via GET)
if ($id_original_url !== NULL && $id_original_url > 0) {
    $sql_original = "SELECT titulo, conteudo FROM historias WHERE id_historia = ?";
    $stmt_original = $conn->prepare($sql_original);
    $stmt_original->bind_param("i", $id_original_url);
    $stmt_original->execute();
    $resultado = $stmt_original->get_result();
    if ($resultado->num_rows > 0) {
        $historia = $resultado->fetch_assoc();
        $historia_original_titulo = $historia['titulo'];
    }
    $stmt_original->close();
}

// Busca gêneros para o dropdown
$sql_generos = "SELECT id_genero, nome FROM generos ORDER BY nome";
$resultado_generos = $conn->query($sql_generos);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Publicar História - Abismo de Letras</title>
    <link rel="stylesheet" href="style.css">
    <script src="scripts.js"></script>
    </head>
<body>
    <?php include 'header.php'; ?>
    <main class="container">
        <h2><?php echo $id_original_url > 0 ? "Continuar História: " . htmlspecialchars($historia_original_titulo) : "Escreva uma Nova História"; ?></h2>
        <div class="card">
            <?php if (!empty($mensagem)): ?>
                <div class="alert-success"><?php echo $mensagem; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="publicar.php" enctype="multipart/form-data">
                
                <?php if ($id_original_url > 0) : ?>
                    <input type="hidden" name="id_original" value="<?php echo $id_original_url; ?>">
                    <p style="color: var(--cor-secundaria); font-weight: bold;">Você está criando uma **versão alternativa/continuação** de: **<?php echo htmlspecialchars($historia_original_titulo); ?>**.</p>
                <?php endif; ?>

                <label for="titulo">Título da sua Versão/História:</label>
                <input type="text" id="titulo" name="titulo" required>

                <label for="capa_imagem">Imagem de Capa (Opcional):</label>
                <input type="file" id="capa_imagem" name="capa_imagem" accept="image/*" onchange="previewImage(event, 'preview_capa')">
                <img id="preview_capa" src="default_capa.png" alt="Pré-visualização da Capa" style="max-width: 150px; margin-top: 10px; display: block;">


                <label for="id_genero">Gênero:</label>
                <select id="id_genero" name="id_genero" required>
                    <option value="">Selecione um Gênero</option>
                    <?php while ($genero = $resultado_generos->fetch_assoc()): ?>
                        <option value="<?php echo $genero['id_genero']; ?>">
                            <?php echo htmlspecialchars($genero['nome']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>

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