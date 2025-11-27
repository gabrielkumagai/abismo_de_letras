<?php 
session_start();
// O include 'conexao.php' não é estritamente necessário na index se ela não consulta o DB
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Abismo de Letras – Incentivo à Escrita e Comunidade</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; // Inclui o cabeçalho ?>

    <main class="container">
        <h2>Bem-vindo ao Abismo de Letras</h2>
        <p>Este portal surge para mitigar o ofuscamento de escritores independentes e fomentar uma **comunidade colaborativa** entre escritores iniciantes e experientes[cite: 33, 65].</p>

        <section class="card">
            <h3>📖 Escreva e Colabore</h3>
            <p>Publique suas histórias autorais e encontre apoio mútuo em nossa comunidade. Focamos em **histórias colaborativas**, permitindo que outros usuários criem e interajam com narrativas existentes ou desenvolvam suas próprias histórias[cite: 12].</p>
            <a href="<?php echo isset($_SESSION['id_usuario']) ? 'publicar.php' : 'cadastro.php'; ?>" class="btn-cta">Iniciar uma História</a>
        </section>

        <section class="card">
            <h3>📚 Preparação para o ENEM</h3>
            <p>A seção de apoio ao ENEM disponibiliza **roteiros personalizáveis e flexíveis** que auxiliam na organização de ideias, estrutura textual e argumentação, contribuindo para um desempenho mais eficaz no exame[cite: 35, 36].</p>
            <a href="#" class="btn-cta">Acessar Roteiros (EM BREVE)</a>
        </section>
        
    </main>
    <footer>
        <p>&copy; 2025 Abismo de Letras. Projeto TCC - Etec Monsenhor Antonio Magliano, Garça[cite: 7].</p>
    </footer>
</body>
</html>