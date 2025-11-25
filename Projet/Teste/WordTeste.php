<?php
include "ADM/conexaoHistoria.php";

$id = $_GET["id"] ?? null;
$titulo = "";

if ($id) {
    $sql = "SELECT titulo FROM tarefas WHERE id = $id LIMIT 1";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $titulo = $result->fetch_assoc()["titulo"];
    }
}
?>


<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Editor de Tarefas</title>

<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded" />

<!-- jsPDF + html2canvas -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<style>
    body {
        margin: 0;
        font-family: "Poppins", sans-serif;
        background: #e6e6e6;
    }

    /* ================= HEADER ================= */

/* Input do título no header */
.titulo-header {
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
    width: 40%;
    padding: 10px 15px;
    font-size: 20px;
    border: none;
    border-radius: 8px;
    text-align: center;
    background: #fff;
    box-shadow: 0 0 5px rgba(0,0,0,0.15);
}


.main {
    width: 100%;
    height: 70px;
    background-color: #fff8f2;
    display: flex;
    align-items: center;
    justify-content: center; /* garante centralização */
    padding: 0 25px;
    box-sizing: border-box;
    position: fixed;
    top: 0;
    left: 0;
    z-index: 50;
    gap: 20px;
}

 .logo-btn {
    position: absolute;
    left: 25px; /* deixa a logo fixa à esquerda */
}

 img {
    height: 60px;
}

.titulo-historia {
    font-size: 22px;
    font-weight: 600;
    color: #945e38;
    text-align: center;
    margin: 0;
}


    /* ================= SIDEBAR ================= */
.sidebar {
    width: 220px;
    background-color: #fff8f2;
    height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    padding-top: 20px;
    box-sizing: border-box;
    color: #919974;
}

.sidebar h3 {
    padding-left: 20px;
    margin-bottom: 10px;
    font-size: 17px;
    font-weight: 600;
}

.sidebar button {
    width: 180px;
    margin: 10px 20px;
    padding: 10px;
    background: #945e38;
    border: none;
    border-radius: 8px;
    color: white;
    font-size: 15px;
    cursor: pointer;
    transition: 0.2s;
    display: flex;
    align-items: center;
    gap: 10px;
}

.sidebar button:hover {
    background: #148399;
}

.sidebar .material-symbols-rounded {
    font-size: 22px;
}

    /* ================= ÁREA PRINCIPAL ================= */
.content {
    margin-left: 220px;
    padding: 20px;
    padding-top: 70px; /* <-- espaço correto */
    box-sizing: border-box;
}
.titulo-header {
 position: absolute; 
left: 50%; 
transform: translateX(-50%); 
width: 40%; 
padding: 10px 15px; 
font-size: 20px; 
border: none;
border-radius: 8px; 
text-align: center;
background: #fff; 
box-shadow: 0 0 5px rgba(0,0,0,0.15); }


    /* ================= FOLHA ================= */
.page {
    width: 800px;
    min-height: 1100px;
    background: white;
    margin: auto;
    padding: 40px;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
    box-sizing: border-box;
}

    /* Placeholder para contenteditable */
#editor[placeholder]:empty:before {
    content: attr(placeholder);
    color: #888;
    pointer-events: none;
}

#editor {
    width: 100%;
    min-height: 1050px;
    outline: none;
    font-size: 18px;
    line-height: 1.6;
    white-space: pre-wrap;
}

#colorPicker {
    position: absolute;
    opacity: 0;
    width: 1px;
    height: 1px;
    pointer-events: none;
}


/* ======== TÍTULO DA HISTÓRIA (CORRIGIDO) ======== */
#tituloTarefa {
    display: block;
    width: 60%;              /* maior */
    margin: 0 auto 25px auto;/* centralizado + espaçamento */
    padding: 12px 18px;
    font-size: 26px;         /* maior */
    font-weight: 600;
    
    border: none;
    border-radius: 10px;
    background: white;
    box-shadow: 0 0 8px rgba(0,0,0,0.15);
    
    text-align: center;
    outline: none;
}

</style>

</head>
<body>

<script>
function getQueryParams() {
    const params = new URLSearchParams(window.location.search);
    return {
        titulo: params.get("titulo"),
        genero: params.get("genero")
    };
}

window.onload = () => {
    const data = getQueryParams();

    // Coloca o título da história
    if (data.titulo) {
        const campoTitulo = document.getElementById("tituloTarefa");
        if (campoTitulo) campoTitulo.value = data.titulo;
    }

    // Se quiser usar o gênero para algo futuramente
    console.log("Gênero recebido:", data.genero);
};
</script>


<!-- ================= SIDEBAR ================= -->
<div class="sidebar">


    <a href="../Teste/pag_home_copia.php" class="logo-btn">
        <img class="logo" src="../img/logo_laranja.png" alt="Logo">
    </a>
    <br><br><br><br>
    <h3>Ferramentas</h3>

    <button onclick="document.execCommand('bold')">
        <span class="material-symbols-rounded">format_bold</span> Negrito
    </button>

    <button onclick="document.execCommand('italic')">
        <span class="material-symbols-rounded">format_italic</span> Itálico
    </button>

    <button onclick="document.execCommand('underline')">
        <span class="material-symbols-rounded">format_underlined</span> Sublinhado
    </button>

    <button onclick="document.execCommand('insertUnorderedList')">
        <span class="material-symbols-rounded">format_list_bulleted</span> Lista
    </button>

    <button onclick="document.execCommand('insertOrderedList')">
        <span class="material-symbols-rounded">format_list_numbered</span> Lista Num.
    </button>

    <button onclick="baixarPDF()">
        <span class="material-symbols-rounded">picture_as_pdf</span> Baixar PDF
    </button>

    <button onclick="abrirColorPicker()">
        <span class="material-symbols-rounded">format_color_text</span> Cor
    </button>

    <div style="position: relative;">
        <input type="color" id="colorPicker" onchange="mudarCor(this.value)">
    </div>

</div>

<!-- ================= ÁREA PRINCIPAL ================= -->
<div class="content">

    <div class="page-title">
        <input class="page-title" type="text" id="tituloTarefa" placeholder="Título da história">
    </div>

    <div class="page" id="page">
        <div id="editor" contenteditable="true" placeholder="Comece a escrever sua historia  aqui..."></div>
    </div>

</div>

<!-- ================= JS DO PDF ================= -->
<script>
async function baixarPDF() {
    const { jsPDF } = window.jspdf;

    const elemento = document.querySelector(".page");

    const canvas = await html2canvas(elemento, {
        scale: 2,
        useCORS: true
    });

    const imgData = canvas.toDataURL("image/jpeg", 1.0);

    const pdf = new jsPDF("p", "pt", "a4");

    const pdfWidth = pdf.internal.pageSize.getWidth();
    const imgWidth = pdfWidth;
    const imgHeight = canvas.height * (imgWidth / canvas.width);

    pdf.addImage(imgData, "JPEG", 0, 0, imgWidth, imgHeight);

    pdf.save("tarefa.pdf");
}

// ==================== FUNÇÃO DO COLOR PICKER ====================
function abrirColorPicker() {
    document.getElementById("colorPicker").click();
}

function mudarCor(cor) {
    document.execCommand("foreColor", false, cor);
}
</script>

</body>
</html>
