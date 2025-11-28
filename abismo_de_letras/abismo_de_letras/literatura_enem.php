<?php
include 'conexao.php';
session_start();

$id_livro = isset($_GET['livro_id']) ? (int)$_GET['livro_id'] : 0;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Literatura Brasileira ENEM - Abismo de Letras</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .livro-card { border-left: 5px solid #00aaff; margin-bottom: 25px; }
        .detalhe-livro { background-color: #f7f7f7; padding: 20px; border-radius: 5px; }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    <main class="container">
        <h2>📚 Consulta de Obras da Literatura Brasileira para o ENEM</h2>
        <p>Acesse resumos e análises das obras clássicas mais cobradas no exame. Uma ferramenta para consulta rápida e estudo aprofundado.</p>
        
        <?php if ($id_livro > 0): ?>
            <?php
            $sql_detalhe = "SELECT * FROM livros_enem WHERE id_livro = ?";
            $stmt_detalhe = $conn->prepare($sql_detalhe);
            $stmt_detalhe->bind_param("i", $id_livro);
            $stmt_detalhe->execute();
            $livro = $stmt_detalhe->get_result()->fetch_assoc();
            $stmt_detalhe->close();

            if ($livro):
            ?>
                <div class="card detalhe-livro">
                    <h3><?php echo htmlspecialchars($livro['titulo']); ?></h3>
                    <p><strong>Autor:</strong> <?php echo htmlspecialchars($livro['autor']); ?></p>
                    <p><strong>Escola Literária:</strong> <span style="color: var(--cor-secundaria); font-weight: bold;"><?php echo htmlspecialchars($livro['escola_literaria']); ?></span></p>
                    
                    <hr>
                    
                    <h4>Sinopse:</h4>
                    <p><?php echo nl2br(htmlspecialchars($livro['sinopse'])); ?></p>
                    
                    <h4>Relevância para o ENEM:</h4>
                    <p><?php echo nl2br(htmlspecialchars($livro['relevancia_enem'])); ?></p>
                </div>
                <a href="literatura_enem.php" class="btn-cta">← Voltar à Lista de Obras</a>
            <?php else: ?>
                <p class="alert-success">Livro não encontrado.</p>
                <a href="literatura_enem.php" class="btn-cta">← Voltar à Lista de Obras</a>
            <?php endif; ?>

        <?php else: ?>
            <?php
            $sql_lista = "SELECT id_livro, titulo, autor, escola_literaria FROM livros_enem ORDER BY escola_literaria, titulo";
            $resultado_lista = $conn->query($sql_lista);

            if ($resultado_lista->num_rows > 0):
                while ($livro_lista = $resultado_lista->fetch_assoc()):
            ?>
                    <div class="card livro-card">
                        <h3><?php echo htmlspecialchars($livro_lista['titulo']); ?></h3>
                        <p>
                            <strong>Autor:</strong> <?php echo htmlspecialchars($livro_lista['autor']); ?> | 
                            <strong>Escola:</strong> <span style="color: var(--cor-secundaria);"><?php echo htmlspecialchars($livro_lista['escola_literaria']); ?></span>
                        </p>
                        <a href="literatura_enem.php?livro_id=<?php echo $livro_lista['id_livro']; ?>" class="btn">Consultar Detalhes</a>
                    </div>
            <?php
                endwhile;
            else:
            ?>
                <p class="card">Nenhuma obra literária cadastrada para consulta no momento.</p>
            <?php endif; ?>

        <?php endif; ?>

    </main>
    <footer><p>&copy; 2025 Abismo de Letras.</p></footer>
</body>
</html>
<?php $conn->close(); ?>