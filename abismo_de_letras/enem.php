<?php
include 'conexao.php';
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}

// O módulo ENEM é focado, mas permitimos a visualização para todos logados.
$id_usuario = (int)$_SESSION['id_usuario'];
$mensagem = "";

// Temas de Redação (Simulando uma lista de roteiros)
$temas = [
    1 => "O desafio da mobilidade urbana no século XXI",
    2 => "A importância da democratização do acesso à internet no Brasil",
    3 => "Efeitos da inteligência artificial na produção cultural brasileira",
    4 => "Caminhos para combater a invisibilidade social"
];
$tema_selecionado = isset($_GET['tema_id']) ? (int)$_GET['tema_id'] : 0;


// -----------------------------------------------------
// Lógica para Salvar/Atualizar a Redação
// -----------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['salvar_redacao'])) {
    $tema = $conn->real_escape_string($_POST['tema_titulo']);
    $texto = $conn->real_escape_string($_POST['texto_redacao']);

    // Tenta inserir como novo rascunho
    $sql = "INSERT INTO redacoes_enem (id_usuario, tema, texto) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $id_usuario, $tema, $texto);

    if ($stmt->execute()) {
        $mensagem = "Rascunho da redação sobre **{$tema}** salvo com sucesso!";
    } else {
        $mensagem = "Erro ao salvar o rascunho: " . $stmt->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Módulo ENEM - Abismo de Letras</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; // Inclui o cabeçalho ?>
    <main class="container">
        <h2>📚 Módulo de Preparação ENEM</h2>
        <p>Utilize esta área para praticar a escrita de redações. Salve seus rascunhos e utilize os temas como base para os **roteiros personalizáveis**.</p>

        <?php if (!empty($mensagem)): ?>
            <div class="alert-success"><?php echo $mensagem; ?></div>
        <?php endif; ?>

        <h3>Escolha um Tema para Praticar:</h3>
        <div class="card">
            <?php foreach ($temas as $id => $titulo): ?>
                <p>
                    <strong><?php echo htmlspecialchars($titulo); ?></strong>
                    <a href="enem.php?tema_id=<?php echo $id; ?>" class="btn" style="margin-left: 10px; padding: 5px 10px;">Iniciar Rascunho</a>
                </p>
                <hr>
            <?php endforeach; ?>
        </div>

        <?php if ($tema_selecionado > 0 && isset($temas[$tema_selecionado])): ?>
            <div class="card" style="border-left: 5px solid #007bff; margin-top: 30px;">
                <h3>Praticando: <?php echo htmlspecialchars($temas[$tema_selecionado]); ?></h3>
                <p><em>Roteiro Flexível: Lembre-se de estruturar sua redação em Introdução, Desenvolvimento (D1 e D2) e Proposta de Intervenção.</em></p>
                
                <form method="POST" action="enem.php">
                    <input type="hidden" name="tema_titulo" value="<?php echo htmlspecialchars($temas[$tema_selecionado]); ?>">
                    
                    <label for="texto_redacao">Sua Redação:</label>
                    <textarea id="texto_redacao" name="texto_redacao" rows="20" required>
Introdução: (Tese e Contextualização)

Desenvolvimento 1: (Argumento 1)

Desenvolvimento 2: (Argumento 2)

Conclusão: (Retomada da Tese e Proposta de Intervenção Completa)
                    </textarea>
                    
                    <input type="hidden" name="salvar_redacao" value="1">
                    <button type="submit" class="btn-cta">Salvar Rascunho</button>
                </form>
            </div>
        <?php endif; ?>

        <h3 style="margin-top: 40px;">Meus Rascunhos Salvos:</h3>
        <div class="card">
            <?php
            $sql_rascunhos = "SELECT id_redacao, tema, data_salva FROM redacoes_enem WHERE id_usuario = ? ORDER BY data_salva DESC";
            $stmt_rascunhos = $conn->prepare($sql_rascunhos);
            $stmt_rascunhos->bind_param("i", $id_usuario);
            $stmt_rascunhos->execute();
            $resultado_rascunhos = $stmt_rascunhos->get_result();

            if ($resultado_rascunhos->num_rows > 0) {
                while ($r = $resultado_rascunhos->fetch_assoc()) {
                    echo "<p>Rascunho de **" . htmlspecialchars($r['tema']) . "** (Salvo em: " . date('d/m/Y H:i', strtotime($r['data_salva'])) . ")</p>";
                }
            } else {
                echo "<p>Nenhum rascunho de redação salvo ainda.</p>";
            }
            $stmt_rascunhos->close();
            ?>
        </div>
    </main>
    <footer><p>&copy; 2025 Abismo de Letras.</p></footer>
</body>
</html>
<?php $conn->close(); ?>