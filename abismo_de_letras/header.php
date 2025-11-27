<?php
// Não inicia sessão aqui. Assume que session_start() foi chamado no arquivo principal.
?>
<header>
    <div class="container">
        <h1>ABISMO DE LETRAS</h1>
        <nav>
            <ul>
                <li><a href="index.php">Início</a></li>
                <li><a href="historias.php">Histórias</a></li>
                <li><a href="enem.php">ENEM</a></li> 
                <?php if (isset($_SESSION['id_usuario'])): ?>
                    <li><a href="publicar.php">Escrever</a></li>
                    <li><a href="perfil.php">Meu Perfil (<?php echo htmlspecialchars($_SESSION['nome']); ?>)</a></li>
                    <li><a href="logout.php">Sair</a></li>
                <?php else: ?>
                    <li><a href="cadastro.php">Cadastre-se</a></li>
                    <li><a href="login.php">Login</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>