  <?php
// Conexão com o banco
include "ADM/conexaoHistoria.php";
?>

<!DOCTYPE html>
  <html lang="pt-br">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Abismo de Letras</title>
    <style>
      * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
      }

      body {
        font-family: 'Georgia', serif;
        background-color: #fffdf6;
        color: #2c2c2c;
        line-height: 1.6;
      }

      header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px;
        border-bottom: 1px solid #ddd;
        background-color: #fff8f2;
      }

      header img {
        margin: 0;
        width: 250px;

        object-fit: cover;
        border-radius: 5px;
        margin: 0px 0;
      }

      .logo span {
        font-size: 24px;
        font-weight: bold;
        color: #919974;
      }

      .search-login input {
        padding: 8px 12px;
        border-radius: 10px;
        border: 1px solid #ccc;
      }

      nav {
        display: flex;
        justify-content: center;
        gap: 40px;
        padding: 10px;
        background-color: #fff0e5;
      }

      nav a {
        text-decoration: none;
        color: #e49052;
        font-weight: bold;
      }

      .carousel {
    position: relative;
    display: flex;
    align-items: center;
    width: 100%;
    max-width: 900px;
    margin: 40px auto;
    padding: 10px 0;
  }

  .carousel-container {
    display: flex;
    overflow: hidden;
    scroll-behavior: smooth;
    gap: 20px;
    width: 100%;
  }

  .carousel-item {
    min-width: 180px;
    background: #0f3f47;
    padding: 14px;
    border-radius: 14px;
    text-align: center;
    transition: 0.3s;
    box-shadow: 0 4px 14px rgba(0,0,0,0.3);
  }

  .carousel-item img {
    width: 80%;
    height: 160px;
    object-fit: cover;
    border-radius: 10px;
  }

  .carousel-item p {
    margin-top: 10px;
    font-size: 16px;
    color: white;
    font-weight: 600;
  }

  .carousel-btn {
    border: none;
    background: #0d5c63;
    color: white;
    font-size: 26px;
    padding: 10px 16px;
    cursor: pointer;
    border-radius: 10px;
    transition: 0.2s;
  }

  .carousel-btn:hover {
    background: #0e7078;
  }


      main {
        padding: 30px 20px;
      }

      .categoria {
        margin-bottom: 50px;
      }

      .categoria h2 {
        font-style: italic;
        font-size: 20px;
        color: #e49052;
        margin-bottom: 15px;
        text-align: left;
      }

      .content {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
      }

      .content img {
        width: 180px;
        height: auto;
        border-radius: 10px;
      }

      .text h3 {
        font-size: 18px;
        margin-bottom: 10px;
      }

      .text p {
        margin-bottom: 10px;
      }

      .text button {
        margin-right: 10px;
        padding: 6px 12px;
        border: none;
        background-color: #919974;
        color: white;
        border-radius: 5px;
        cursor: pointer;
      }

      footer {
        display: flex;
        justify-content: space-between;
        padding: 20px;
        background: linear-gradient(to right, #919974, #e49052);
        color: white;
      }

      footer .contact p,
      footer .rights p {
        margin-bottom: 5px;
      }


      /* MODAL / OVERFLOW */
.modal-bg {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0,0,0,0.55);
  display: none;
  justify-content: center;
  align-items: center;
  z-index: 999;
}

.modal-box {
  background: #fffdf6;
  padding: 30px;
  width: 350px;
  border-radius: 12px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.3);
  animation: fadeIn 0.3s ease;
}

.modal-box h2 {
  margin-bottom: 20px;
  color: #e49052;
}

.modal-box input,
.modal-box select {
  width: 100%;
  padding: 10px;
  margin-top: 5px;
  margin-bottom: 20px;
  border-radius: 8px;
  border: 1px solid #ccc;
  background: white;
}

.modal-btns {
  display: flex;
  justify-content: space-between;
}

.modal-btns button {
  padding: 10px;
  width: 48%;
  border: none;
  background: #e49052;
  color: white;
  border-radius: 6px;
  cursor: pointer;
}

.modal-btns button:hover {
  background: #cf7d40;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-10px); }
  to { opacity: 1; transform: translateY(0); }
}

    </style>
  </head>
  <body>
    <header>


      <a href="../INDEX/Pag_principal.html">
        <img src="../img/logo_laranja.png" alt="Imagem 5" />
      </a>
      
      <div class="search-login">
        <input type="text" placeholder="Pesquisar">
      </div>
    </header>

<nav>
  <a href="#" onclick="openModal(event)">Criar minha própria história</a>
  <a href="../INSIDE/pag_criarpropria.html">Continuar uma história</a>
  <a href="#">ENEM</a>
</nav>


    <section class="carousel">
    <button class="carousel-btn left" onclick="moveCarousel(-1)">❮</button>

    <div class="carousel-container" id="carousel">
      
      <div class="carousel-item">
        <img src="../img/fantasia.png" alt="Fantasia">
        <p>Fantasia</p>
      </div>

      <div class="carousel-item">
        <img src="../img/ficcao.png" alt="Ficção Científica">
        <p>Ficção Científica</p>
      </div>

      <div class="carousel-item">
        <img src="../img/romance.png" alt="Romance">
        <p>Romance</p>
      </div>

      <div class="carousel-item">
        <img src="../img/misterio.png" alt="Mistério">
        <p>Mistério</p>
      </div>

      <div class="carousel-item">
        <img src="../img/terror.png" alt="Terror">
        <p>Terror</p>
      </div>

      <div class="carousel-item">
        <img src="../img/drama.png" alt="Drama">
        <p>Drama</p>
      </div>

    </div>

    <button class="carousel-btn right" onclick="moveCarousel(1)">❯</button>
    <script>
    function moveCarousel(direction) {
      const carousel = document.getElementById("carousel");
      const itemWidth = carousel.querySelector(".carousel-item").offsetWidth + 20;
      carousel.scrollLeft += direction * itemWidth;
    }
  </script>

  </section>


    <main>
        <eader>Minhas Histórias</header>    
      <section class="tasks" id="tasksContainer"></section>


  </main>

    <footer>
      <div class="contact">
        <p>Fale com a gente:</p>
        <p>Instagram: @abismo_letras</p>
        <p>Email: contato@abismodeletras.com</p>
      </div>
      <div class="rights">
        <p>Todos os direitos reservados ©</p>
      </div>
    </footer>


    <!-- MODAL -->
<div id="modalCriar" class="modal-bg">
  <div class="modal-box">
    <h2>Criar nova história</h2>

    <label>Título:</label>
    <input type="text" name="Titulo" id="tituloHistoria" placeholder="Digite o título...">

    <label>Gênero:</label>
    <select id="generoHistoria">
      <option value="" disabled selected>Selecione</option>
      <option>Romance</option>
      <option>Fantasia</option>
      <option>Mistério</option>
      <option>Suspense</option>
      <option>Terror</option>
      <option>Drama</option>
    </select>

    <div class="modal-btns">
      <button onclick="fecharModal()">Cancelar</button>
      <button onclick="continuarCriacao()">Continuar</button>
    </div>
  </div>
</div>





<script>

document.addEventListener("DOMContentLoaded", () => {

  const tasksContainer = document.getElementById("tasksContainer");


});
function openModal(e) {
  e.preventDefault();
  document.getElementById('modalCriar').style.display = 'flex';
}

function fecharModal() {
  document.getElementById('modalCriar').style.display = 'none';
}

function continuarCriacao() {
  const titulo = document.getElementById('tituloHistoria').value;
  const genero = document.getElementById('generoHistoria').value;

  if (titulo.trim() === '' || genero === '') {
    alert('Preencha o título e o gênero para continuar.');
    return;
  }


}

</script>


  </body>
  </html>
/////////
/////////
/////////
/////////
/////////





<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Histórias</title>

  <style>
    body {
      font-family: 'Georgia', serif;
      background-color: #fffdf6;
      color: #2c2c2c;
      line-height: 1.6;    
    }

    header {
           display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 20px;
      border-bottom: 1px solid #ddd;
      background-color: #fff8f2;
    }

    /* Container das histórias */
    #tasksContainer {
      display: flex;
      flex-direction: column;
      gap: 12px;
      padding: 20px;
      margin-bottom: 80px;
    }

    .task {
      display: flex;
      align-items: center;
      background: #181818;
      padding: 16px;
      border-radius: 10px;
      text-decoration: none;
      color: white;
      border: 1px solid #333;
      transition: 0.2s;
    }

    .task:hover {
      background: #222;
    }

    .thumb {
      width: 55px;
      height: 55px;
      background: #333;
      border-radius: 8px;
      font-size: 32px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-right: 15px;
    }

    .meta h3 {
      margin: 0;
      font-size: 20px;
    }

    .meta p {
      margin: 0;
      opacity: 0.7;
      font-size: 14px;
    }

    .date {
      margin-left: auto;
      opacity: 0.6;
      font-size: 12px;
    }

    /* Botão flutuante */
    .add-btn {
      position: fixed;
      bottom: 25px;
      right: 25px;
      width: 70px;
      height: 70px;
      background: #0099ff;
      color: white;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 42px;
      cursor: pointer;
      box-shadow: 0 0 10px #000;
      transition: 0.2s;
    }

    .add-btn:hover {
      background: #007ed4;
    }

    /* Modal */
    .overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0,0,0,0.7);
      display: none;
      align-items: center;
      justify-content: center;
    }

    .overlay.active {
      display: flex;
    }

    .modal {
      background: #1a1a1a;
      padding: 25px;
      width: 350px;
      border-radius: 12px;
      border: 1px solid #444;
    }

    .modal h3 {
      margin-top: 0;
      text-align: center;
      margin-bottom: 20px;
    }

    .modal input, .modal textarea {
      width: 100%;
      padding: 10px;
      margin-bottom: 12px;
      border-radius: 6px;
      border: 1px solid #555;
      background: #111;
      color: white;
    }

    .modal button {
      padding: 10px 20px;
      background: #0099ff;
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 15px;
    }

    .cancel {
      background: #444;
    }
  </style>
</head>
<body>



<!-- Container de histórias -->
<div id="tasksContainer"></div>

<!-- Botão flutuante -->


<!-- Modal -->
<div class="overlay" id="overlayHistoria">
  <div class="modal">
    <h3>Criar História</h3>

    <form id="formHistoria">
      <input type="text" name="titulo" placeholder="Título da história" required>
      <input type="text" name="genero" placeholder="Gênero" required>
      <textarea name="conteudo" placeholder="Conteúdo inicial..." rows="4"></textarea>

      <div style="display:flex;gap:10px;justify-content:flex-end">
        <button type="button" class="cancel" id="cancelHistoria">Cancelar</button>
        <button type="submit">Criar</button>
      </div>
    </form>
  </div>
</div>

<script>
/* ============================
   ABRIR E FECHAR MODAL
   ============================ */

const overlayHistoria = document.getElementById("overlayHistoria");
const addHistoria = document.getElementById("addHistoria");
const cancelHistoria = document.getElementById("cancelHistoria");
const formHistoria = document.getElementById("formHistoria");
const historiasContainer = document.getElementById("tasksContainer");

addHistoria.onclick = () => overlayHistoria.classList.add("active");
cancelHistoria.onclick = () => overlayHistoria.classList.remove("active");

/* ============================
   CADASTRAR HISTÓRIA (POST)
   ============================ */

formHistoria.addEventListener("submit", async (e) => {
  e.preventDefault();

  const dados = new FormData(formHistoria);

  const r = await fetch("../ADM/CriarHistoria.php", {
    method: "POST",
    body: dados
  });

  const json = await r.json();

  if (json.success) {
    overlayHistoria.classList.remove("active");
    formHistoria.reset();
    carregarHistorias();
  } else {
    alert("Erro: " + json.error);
  }
});

/* ============================
   LISTAR HISTÓRIAS (GET)
   ============================ */

async function carregarHistorias() {
  const r = await fetch("../ADM/ListarHistoria.php");
  const lista = await r.json();

  historiasContainer.innerHTML = "";

  lista.forEach(h => {
    const item = document.createElement("a");
    item.className = "task";
    item.href = `canvas.html?id=${h.id}`;

    item.innerHTML = `
      <div class="thumb">📘</div>
      <div class="meta">
        <h3>${h.titulo}</h3>
        <p>${h.genero}</p>
      </div>
      <div class="date">${h.data}</div>
    `;

    historiasContainer.appendChild(item);
  });
}

carregarHistorias();
</script>

</body>
</html>
