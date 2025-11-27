<?php
include 'conexao.php';
session_start();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histórias Colaborativas - Abismo de Letras</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; // CORREÇÃO: Inclui o cabeçalho externo ?>
    
    <main class="container">
        <h2>Início de Narrativas (Os Abismos)</h2>
        <p>Histórias que aguardam a sua versão alternativa ou continuação. Clique para ler e entrar na comunidade de escritores.</p>

        <?php
        // Busca apenas as histórias originais (que não são continuações)
        $sql = "SELECT h.id_historia, h.titulo, u.nome AS autor, h.data_publicacao FROM historias h JOIN usuarios u ON h.id_autor = u.id_usuario WHERE h.id_historia_original IS NULL AND h.acesso = 'publico' ORDER BY h.data_publicacao DESC";
        $resultado = $conn->query($sql);

        if ($resultado->num_rows > 0) {
            while ($historia = $resultado->fetch_assoc()) {
                echo "<div class='card'>";
                echo "<h3>" . htmlspecialchars($historia['titulo']) . "</h3>";
                echo "<p>Autor: **" . htmlspecialchars($historia['autor']) . "** | Publicado em: " . date('d/m/Y', strtotime($historia['data_publicacao'])) . "</p>";
                echo "<a href='ver_historia.php?id=" . $historia['id_historia'] . "' class='btn-cta'>Ler e Ver Versões</a>";
                echo "</div>";
            }
        } else {
            echo "<p>Nenhuma história original publicada ainda. Seja o primeiro!</p>";
            if (isset($_SESSION['id_usuario'])) {
                 echo "<a href='publicar.php' class='btn-cta'>Começar a Escrever</a>";
            }
        }
        ?>
    </main>
    <footer><p>&copy; 2025 Abismo de Letras. Projeto TCC - Etec Monsenhor Antonio Magliano, Garça.</p></footer>
</body>
</html>
<?php $conn->close(); ?>