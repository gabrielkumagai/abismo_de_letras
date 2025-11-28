<?php
include 'conexao.php';
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prática Colaborativa ENEM - Abismo de Letras</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <main class="container">
        <h2>Prática Colaborativa de Redação</h2>
        <p>Encontre rascunhos de outros estudantes para dar continuidade ou oferecer uma correção. Ou comece a sua! </p>
        
        <a href="publicar_redacao.php" class="btn-cta" style="margin-bottom: 20px;">+ Começar um Novo Rascunho</a>

        <h3>Rascunhos Originais Disponíveis para Colaboração</h3>
        
        <?php
        // Busca apenas os rascunhos originais (que não são continuações/correções)
        $sql = "SELECT r.id_redacao, r.titulo, r.tema, u.nome AS autor, r.data_salva 
                FROM redacoes_enem r 
                JOIN usuarios u ON r.id_usuario = u.id_usuario 
                WHERE r.id_redacao_original IS NULL AND r.tipo_contribuicao = 'rascunho'
                ORDER BY r.data_salva DESC";
        $resultado = $conn->query($sql);

        if ($resultado->num_rows > 0) {
            while ($redacao = $resultado->fetch_assoc()) {
                echo "<div class='card'>";
                echo "<h3>" . htmlspecialchars($redacao['titulo']) . "</h3>";
                echo "<p><strong>Tema:</strong> " . htmlspecialchars($redacao['tema']) . "</p>";
                echo "<p>Autor: **" . htmlspecialchars($redacao['autor']) . "** | Data: " . date('d/m/Y', strtotime($redacao['data_salva'])) . "</p>";
                echo "<a href='ver_redacao.php?id=" . $redacao['id_redacao'] . "' class='btn-cta'>Ver Rascunho e Colaborar</a>";
                echo "</div>";
            }
        } else {
            echo "<p>Nenhum rascunho original disponível para colaboração. Seja o primeiro a pedir ajuda!</p>";
        }
        ?>
    </main>
    <footer><p>&copy; 2025 Abismo de Letras.</p></footer>
</body>
</html>
<?php $conn->close(); ?>