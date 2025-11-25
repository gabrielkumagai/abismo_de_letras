<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Abismo de Letras</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    html, body {
      height: 100%;
    }

    body {
      background: url('../img/fundoentrarcadastrar.png') no-repeat center center fixed;
      background-size: cover;
      display: flex;
      flex-direction: column;
    }

    main {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
    }

    .overlay {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(255, 255, 255, 0.6);
      z-index: 0;
    }

    .container {
      position: relative;
      z-index: 1;
      text-align: center;
      background-color: rgba(255, 255, 255, 0.3);
      padding: 40px;
      border-radius: 10px;
      backdrop-filter: blur(5px);
      box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
    }

    .container h2 {
      color: #919974;
      margin-bottom: 30px;
      font-weight: bold;
    }

    .input-field {
      display: block;
      width: 250px;
      margin: 10px auto;
      padding: 10px;
      border: none;
      border-radius: 10px;
      font-size: 16px;
      background-color: #fff;
    }

    .button {
      margin-top: 20px;
      padding: 10px 20px;
      background-color: #fff;
      color: #333;
      border: none;
      border-radius: 20px;
      cursor: pointer;
      font-weight: bold;
      transition: background-color 0.3s ease;
    }

    .button:hover {
      background-color: #ddd;
    }

    .logo {
      position: absolute;
      top: 20px;
      left: 20px;
      width: 100px;
    }

    header img {
      position: absolute;
      top: 0;
      left: 0;
      width: 300px;
      height: auto;
      border-radius: 5px;
    }

    footer {
      background-color: #e49052;
      color: white;
      text-align: center;
      padding: 10px;
      font-size: 0.9rem;
    }

    footer a {
      color: #ddd;
      text-decoration: none;
      margin: 0 10px;
    }

    footer a:hover {
      text-decoration: underline;
    }
  </style>
</head>

<body>
  <header>
    <img src="../img/logo_branca.png" alt="Imagem logo" />
  </header>

  <main>
    <div class="overlay"></div>
    <div class="container">

    <form action="salvar_cadastro.php" method="POST">
      <h2>CRIE SUA CONTA</h2>
      <input class="input-field" type="text" name="nome" placeholder="Seu nome" required>
      <input class="input-field" type="email" name="email" placeholder="Seu e-mail" required>
      <input class="input-field" type="password" name="senha" placeholder="Sua senha" required>
      <button class="button" type="submit">Cadastrar</button>
      
    </form>
<?php
  if (isset($_GET['sucesso'])) {
     echo "<p class='mensagem' style='color:green;'>Cadastro realizado com sucesso!</p>";
  } elseif (isset($_GET['erro'])) {
    echo "<p class='mensagem'>Erro ao cadastrar! Tente novamente.</p>";
  } elseif (isset($_GET['existente'])) {
    echo "<p class='mensagem'>E-mail já cadastrado!</p>";
  }
?>
    </div>

    
  </main>




  <footer>
    <p>&copy; 2025 Oficina da Leitura. Todos os direitos reservados.</p>
    <p>
      <a href="huahuahua">Contato</a> | 
      <a href="huahuahua#">Instagram</a> | 
      <a href="#">Facebook</a>
    </p>
  </footer>
</body>
</html>
