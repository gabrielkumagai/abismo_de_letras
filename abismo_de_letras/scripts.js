// Função para pré-visualizar imagens (Capas e Perfis)
function previewImage(event, outputId) {
    var reader = new FileReader();
    reader.onload = function(){
        var output = document.getElementById(outputId);
        output.src = reader.result;
        output.style.display = 'block'; // Torna a imagem visível
    }
    // Certifique-se de que há um arquivo selecionado
    if (event.target.files && event.target.files[0]) {
        reader.readAsDataURL(event.target.files[0]);
    }
}