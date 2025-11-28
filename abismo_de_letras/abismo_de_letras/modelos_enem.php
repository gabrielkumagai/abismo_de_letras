<?php
include 'conexao.php';
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}

$sql = "SELECT titulo, tema, texto_modelo, nota FROM modelos_nota_mil ORDER BY nota DESC";
$resultado = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modelos ENEM - Abismo de Letras</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <main class="container">
        <h2>Modelos de Redação (+900 Pontos)</h2>
        <p>Utilize estes modelos para ter uma base sólida de estudo, analisando a estrutura, a competência e o uso de repertório sociocultural.</p>

        <?php
        if ($resultado->num_rows > 0) {
            while ($modelo = $resultado->fetch_assoc()) {
                echo "<div class='card' style='border-left: 5px solid #00aaff;'>";
                echo "<h3>" . htmlspecialchars($modelo['titulo']) . "</h3>";
                echo "<p><strong>Tema:</strong> " . htmlspecialchars($modelo['tema']) . "</p>";
                echo "<p><strong>Nota Estimada:</strong> <span style='color: green; font-weight: bold;'>" . htmlspecialchars($modelo['nota']) . " pontos</span></p>";
                echo "<hr>";
                echo "<div style='white-space: pre-wrap; line-height: 1.6; background-color: #f9f9f9; padding: 15px; border-radius: 5px;'>";
                echo htmlspecialchars($modelo['texto_modelo']);
                echo "</div>";
                echo "</div>";
            }
        } else {
            echo "<p class='card'>Nenhum modelo de redação disponível no momento.</p>";
        }
        ?>
        <a href="enem.php" class="btn-cta">Voltar para o Módulo ENEM</a>
    </main>
    <footer><p>&copy; 2025 Abismo de Letras.</p></footer>
</body>
</html>
<?php $conn->close(); ?>