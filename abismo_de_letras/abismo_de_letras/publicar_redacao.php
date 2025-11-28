<?php
include 'conexao.php';
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}

$id_usuario = (int)$_SESSION['id_usuario'];
$mensagem = "";
$id_original = isset($_GET['original_id']) ? (int)$_GET['original_id'] : NULL;
$tema_original = "";
$texto_original = "";
$tipo_contribuicao_default = 'rascunho';

// -----------------------------------------------------
// Lógica de Processamento do Formulário
// -----------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titulo = $conn->real_escape_string($_POST['titulo']);
    $tema = $conn->real_escape_string($_POST['tema']);
    $texto = $conn->real_escape_string($_POST['texto']); 
    $id_original_post = isset($_POST['id_original']) && $_POST['id_original'] != 0 ? (int)$_POST['id_original'] : NULL;
    $tipo_contribuicao = $conn->real_escape_string($_POST['tipo_contribuicao']);

    $sql = "INSERT INTO redacoes_enem (id_usuario, titulo, tema, texto, id_redacao_original, tipo_contribuicao) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("isssis", $id_usuario, $titulo, $tema, $texto, $id_original_post, $tipo_contribuicao);
        
        if ($stmt->execute()) {
            $novo_id = $conn->insert_id;
            
            // *** REDIRECIONAMENTO PÓS-SUCESSO ***
            header('Location: ver_redacao.php?id=' . $novo_id);
            exit;
        } else {
            $mensagem = "Erro ao salvar contribuição: " . $stmt->error;
        }
        $stmt->close();
    }
}

// -----------------------------------------------------
// Carregar dados da redação original se for colaboração
// -----------------------------------------------------
if ($id_original !== NULL && $id_original > 0) {
    $sql_original = "SELECT tema, texto FROM redacoes_enem WHERE id_redacao = ?";
    $stmt_original = $conn->prepare($sql_original);
    $stmt_original->bind_param("i", $id_original);
    $stmt_original->execute();
    $resultado = $stmt_original->get_result();
    
    if ($resultado->num_rows > 0) {
        $redacao = $resultado->fetch_assoc();
        $tema_original = $redacao['tema'];
        $texto_original = $redacao['texto']; 
        $tipo_contribuicao_default = 'correcao_peer';
    }
    $stmt_original->close();
}

// Temas (para redação original)
$temas_base = [
    "O desafio da mobilidade urbana no século XXI",
    "A importância da democratização do acesso à internet no Brasil",
    "Caminhos para combater a invisibilidade social"
];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Publicar Redação - Abismo de Letras</title>
    <link rel="stylesheet" href="style.css">
    </head>
<body>
    <?php include 'header.php'; ?>
    <main class="container">
        <h2><?php echo $id_original > 0 ? "Contribuir com Redação" : "Começar Nova Redação"; ?></h2>
        <div class="card">
            <?php if (!empty($mensagem)): ?>
                <div class="alert-success"><?php echo $mensagem; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="publicar_redacao.php">
                
                <?php if ($id_original > 0) : ?>
                    <input type="hidden" name="id_original" value="<?php echo $id_original; ?>">
                    <p style="color: var(--cor-secundaria); font-weight: bold;">Você está colaborando com a redação original sobre: **<?php echo htmlspecialchars($tema_original); ?>**.</p>
                    
                    <label for="tipo_contribuicao">Tipo de Contribuição:</label>
                    <select id="tipo_contribuicao" name="tipo_contribuicao" required>
                        <option value="continuação" <?php echo $tipo_contribuicao_default == 'continuação' ? 'selected' : ''; ?>>Continuação (Se o texto original for um rascunho inacabado)</option>
                        <option value="correcao_peer" <?php echo $tipo_contribuicao_default == 'correcao_peer' ? 'selected' : ''; ?>>Versão Corrigida/Revisada</option>
                    </select><br><br>

                    <input type="hidden" name="tema" value="<?php echo htmlspecialchars($tema_original); ?>">

                <?php else: ?>
                    <label for="tipo_contribuicao">Tipo de Contribuição:</label>
                    <select id="tipo_contribuicao" name="tipo_contribuicao" required>
                        <option value="rascunho">Novo Rascunho (Original)</option>
                    </select><br><br>

                    <label for="tema">Tema:</label>
                    <select id="tema" name="tema" required>
                        <?php foreach ($temas_base as $t): ?>
                            <option value="<?php echo htmlspecialchars($t); ?>"><?php echo htmlspecialchars($t); ?></option>
                        <?php endforeach; ?>
                    </select>

                    <input type="hidden" name="id_original" value="0">
                <?php endif; ?>

                <label for="titulo">Título da sua Redação/Versão:</label>
                <input type="text" id="titulo" name="titulo" required>

                <label for="texto">Conteúdo da Redação:</label>
                <textarea id="texto" name="texto" rows="15" required><?php echo $id_original > 0 ? htmlspecialchars($texto_original) : ''; ?></textarea>

                <button type="submit" class="btn-cta">Publicar Contribuição</button>
            </form>
        </div>
    </main>
    <footer><p>&copy; 2025 Abismo de Letras.</p></footer>
</body>
</html>
<?php $conn->close(); ?>