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

    .page-wrapper {
      flex: 1;
      display: flex;
      flex-direction: column;
      position: relative;
    }

    header {
      position: absolute;
      top: 20px;
      left: 20px;
      z-index: 2;
    }

    header img {
      position: absolute;
      top: 0;
      left: 0;
      width: 300px;
      height: auto;
      border-radius: 5px;
    }

    .overlay {
      position: absolute;
      width: 100%;
      height: 100%;
      background-color: rgba(255, 255, 255, 0.6);
      z-index: 0;
    }

    main {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      z-index: 1;
    }

    .container {
      background-color: rgba(255, 255, 255, 0.3);
      padding: 40px;
      border-radius: 10px;
      backdrop-filter: blur(5px);
      box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
      text-align: center;
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
  <div class="page-wrapper">
    <header>
      <img src="../img/logo_branca.png" alt="Logo Abismo de Letras">
    </header>

    <div class="overlay"></div>

    <main>
      <div class="container">
        <form action="autenticar.php" method="POST">
          <h2>ENTRAR</h2>
          <input  class="input-field"type="email" name="email" placeholder="Email" required>
          <input class="input-field"type="password" name="senha" placeholder="Senha" required>
         <button class="button" type="submit">Entrar</button>

        <?php   
          if (isset($_GET['erro'])) {
            echo "<p class='erro'>Email ou senha incorretos!</p>";
          }
        ?>
        </form>
      </div>
    </main>
  </div>


  <footer>
    <p>&copy; 2025 Oficina da Leitura. Todos os direitos reservados.</p>
    <p>
      <a href="#">Contato</a> | 
      <a href="#">Instagram</a> | 
      <a href="#">Facebook</a>
    </p>
  </footer>
</body>
</html>
