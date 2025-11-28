<?php
include 'conexao.php';
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}

$id_redacao = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_redacao === 0) {
    header('Location: pratica_enem.php');
    exit;
}

// -----------------------------------------------------
// Busca a Redação Original/Base
// -----------------------------------------------------
$sql_base = "SELECT r.titulo, r.tema, r.texto, u.nome AS autor, r.id_usuario AS id_autor, r.tipo_contribuicao
             FROM redacoes_enem r 
             JOIN usuarios u ON r.id_usuario = u.id_usuario 
             WHERE r.id_redacao = ?";
$stmt_base = $conn->prepare($sql_base);
$stmt_base->bind_param("i", $id_redacao);
$stmt_base->execute();
$redacao_data = $stmt_base->get_result()->fetch_assoc();
$stmt_base->close();

if (!$redacao_data) {
    echo "<p>Redação não encontrada.</p>";
    exit;
}

// -----------------------------------------------------
// Busca Contribuições (Continuações/Correções)
// -----------------------------------------------------
$sql_contribuicoes = "SELECT r.id_redacao, r.titulo, u.nome AS autor, r.tipo_contribuicao, r.data_salva 
                      FROM redacoes_enem r 
                      JOIN usuarios u ON r.id_usuario = u.id_usuario 
                      WHERE r.id_redacao_original = ?
                      ORDER BY r.data_salva DESC";
$stmt_contribuicoes = $conn->prepare($sql_contribuicoes);
$stmt_contribuicoes->bind_param("i", $id_redacao);
$stmt_contribuicoes->execute();
$resultado_contribuicoes = $stmt_contribuicoes->get_result();
$stmt_contribuicoes->close();

$tipo_badge = [
    'continuação' => 'Continuação do Rascunho',
    'correcao_peer' => 'Versão Corrigida (Peer Review)'
];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($redacao_data['titulo']); ?> - Colaboração ENEM</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .contrib-card { border-left: 4px solid #007bff; }
        .badge-correcao { background-color: #d1ecf1; color: #0c5460; padding: 3px 8px; border-radius: 4px; font-weight: bold; font-size: 0.9em; }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    <main class="container">
        <h2>Redação: <?php echo htmlspecialchars($redacao_data['titulo']); ?></h2>
        <p><strong>Tema:</strong> <?php echo htmlspecialchars($redacao_data['tema']); ?></p>
        <p><strong>Autor:</strong> <?php echo htmlspecialchars($redacao_data['autor']); ?></p>
        
        <?php if ($redacao_data['id_autor'] !== NULL): ?>
            <p style="font-weight: bold;">[<?php echo strtoupper($redacao_data['tipo_contribuicao']); ?>]</p>
        <?php endif; ?>

        <div class="card" style="border-left: 5px solid var(--cor-secundaria);">
            <div style="white-space: pre-wrap; line-height: 1.6;">
                <?php echo htmlspecialchars($redacao_data['texto']); ?>
            </div>
        </div>

        <a href='publicar_redacao.php?original_id=<?php echo $id_redacao; ?>' class='btn-cta'>🤝 Continuar ou Corrigir esta Redação</a>
        <a href='pratica_enem.php' class='btn' style='margin-left: 10px;'>Voltar para Prática</a>

        <hr style="margin-top: 40px;">

        <h3>Versões Colaborativas da Comunidade (Peer Review)</h3>
        
        <?php
        if ($resultado_contribuicoes->num_rows > 0) {
            while ($contrib = $resultado_contribuicoes->fetch_assoc()) {
                $badge_texto = $tipo_badge[$contrib['tipo_contribuicao']] ?? 'Contribuição';
                echo "<div class='card contrib-card'>";
                echo "<h4>" . htmlspecialchars($contrib['titulo']) . "</h4>";
                echo "<p><span class='badge-correcao'>{$badge_texto}</span> por: " . htmlspecialchars($contrib['autor']) . "</p>";
                echo "<a href='ver_redacao.php?id=" . $contrib['id_redacao'] . "' class='btn' style='margin-top:0;'>Visualizar Versão</a>";
                echo "</div>";
            }
        } else {
            echo "<p>Nenhuma contribuição (correção ou continuação) para esta redação ainda. Seja o primeiro a ajudar!</p>";
        }
        ?>
    </main>
    <footer><p>&copy; 2025 Abismo de Letras.</p></footer>
</body>
</html>
<?php $conn->close(); ?>