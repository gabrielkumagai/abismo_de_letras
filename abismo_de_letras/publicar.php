<?php
include 'conexao.php';
// Garante sessão
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['id_usuario']) || !is_numeric($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}

$id_autor = (int)$_SESSION['id_usuario']; 
$mensagem = "";
$tipo_alerta = "";
$id_original_url = isset($_GET['continuar']) ? (int)$_GET['continuar'] : NULL;
$historia_original_titulo = "";

// -----------------------------------------------------
// Lógica de Processamento (Mantida e Organizada)
// -----------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titulo = $conn->real_escape_string($_POST['titulo']);
    $conteudo = $conn->real_escape_string($_POST['conteudo']); 
    $id_historia_original = isset($_POST['id_original']) && $_POST['id_original'] != 0 ? (int)$_POST['id_original'] : NULL;
    $acesso = $conn->real_escape_string($_POST['acesso']);
    $id_genero = (int)$_POST['id_genero']; 
    $capa_imagem_path = 'https://placehold.co/400x600/e0e0e0/888888?text=Capa+Padrao'; // Placeholder melhorado

    // Upload
    if (isset($_FILES['capa_imagem']) && $_FILES['capa_imagem']['error'] == 0) {
        $target_dir = "uploads/capas/";
        if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }

        $file_extension = pathinfo($_FILES['capa_imagem']['name'], PATHINFO_EXTENSION);
        $file_name = uniqid('capa_') . '.' . $file_extension;
        $target_file = $target_dir . $file_name;

        if (move_uploaded_file($_FILES['capa_imagem']['tmp_name'], $target_file)) {
            $capa_imagem_path = $target_file;
        } else {
            $mensagem .= " Falha no upload da capa.";
            $tipo_alerta = "warning";
        }
    }

    if ($id_autor <= 0) {
        $mensagem = "Erro de sessão. Faça login novamente.";
        $tipo_alerta = "danger";
    } else {
        $sql = "INSERT INTO historias (id_autor, titulo, conteudo, acesso, id_historia_original, capa_imagem, id_genero) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("isssisi", $id_autor, $titulo, $conteudo, $acesso, $id_historia_original, $capa_imagem_path, $id_genero);
            
            if ($stmt->execute()) {
                $novo_id = $conn->insert_id;
                
                // Badge Colaborador
                if ($id_historia_original !== NULL) {
                     $sql_give = "INSERT IGNORE INTO usuario_badge (id_usuario, id_badge) SELECT ?, id_badge FROM badges WHERE nome = 'O Colaborador'";
                     $stmt_badge = $conn->prepare($sql_give);
                     $stmt_badge->bind_param("i", $id_autor);
                     $stmt_badge->execute();
                     $stmt_badge->close();
                }
                
                header('Location: ver_historia.php?id=' . $novo_id);
                exit;
            } else {
                $mensagem = "Erro ao publicar: " . $stmt->error;
                $tipo_alerta = "danger";
            }
            $stmt->close();
        }
    }
}

// Carregar título original (se for continuação)
if ($id_original_url !== NULL && $id_original_url > 0) {
    $sql_orig = "SELECT titulo FROM historias WHERE id_historia = ?";
    $stmt_orig = $conn->prepare($sql_orig);
    $stmt_orig->bind_param("i", $id_original_url);
    $stmt_orig->execute();
    $res_orig = $stmt_orig->get_result();
    if ($res_orig->num_rows > 0) {
        $historia = $res_orig->fetch_assoc();
        $historia_original_titulo = $historia['titulo'];
    }
    $stmt_orig->close();
}

$sql_generos = "SELECT id_genero, nome FROM generos ORDER BY nome";
$resultado_generos = $conn->query($sql_generos);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estúdio de Criação - Abismo de Letras</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Playfair+Display:wght@400;700&family=Lato:wght@300;400;700&family=Merriweather:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        :root {
            --primary-green: #7d8c66;
            --dark-green: #4a5d3f;
            --cream: #f9f7f2;
            --gold: #d4af37;
            --text-dark: #2c3e50;
        }

        body {
            font-family: 'Lato', sans-serif;
            background-color: #f4f7f6; /* Fundo levemente cinza para contraste */
            color: var(--text-dark);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        h1, h2, h3 { font-family: 'Playfair Display', serif; }
        
        /* --- ESTÚDIO LAYOUT --- */
        .studio-container {
            padding-top: 120px;
            padding-bottom: 60px;
        }

        .studio-header {
            margin-bottom: 30px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 15px;
        }

        /* Sidebar de Configurações (Esquerda) */
        .config-panel {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            height: 100%;
        }

        /* Área de Escrita (Direita) */
        .editor-panel {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            min-height: 600px;
        }

        /* Inputs Estilizados */
        .form-control-lg {
            border: none;
            border-bottom: 2px solid #eee;
            border-radius: 0;
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            font-weight: bold;
            padding-left: 0;
            background: transparent;
        }
        .form-control-lg:focus {
            box-shadow: none;
            border-color: var(--primary-green);
        }

        .editor-textarea {
            width: 100%;
            border: none;
            resize: none;
            font-family: 'Merriweather', serif; /* Fonte de livro */
            font-size: 1.1rem;
            line-height: 1.8;
            color: #333;
            outline: none;
            min-height: 500px;
            background: transparent;
        }
        .editor-textarea::placeholder { color: #ccc; font-style: italic; }

        /* Upload de Capa */
        .cover-upload-wrapper {
            position: relative;
            width: 100%;
            padding-top: 140%; /* Ratio 2:3 */
            background-color: #f8f9fa;
            border: 2px dashed #ddd;
            border-radius: 10px;
            overflow: hidden;
            transition: 0.3s;
            cursor: pointer;
        }
        .cover-upload-wrapper:hover {
            border-color: var(--primary-green);
            background-color: #f0f5f1;
        }
        
        .cover-preview {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            object-fit: cover;
            display: none; /* Escondido até ter imagem */
        }
        
        .upload-label {
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            color: #888;
            width: 100%;
        }

        /* Botão Publicar */
        .btn-publish {
            background: linear-gradient(135deg, var(--dark-green), var(--primary-green));
            color: white; border: none;
            padding: 12px 0; width: 100%;
            border-radius: 50px; font-weight: bold;
            letter-spacing: 1px; transition: 0.3s;
            box-shadow: 0 5px 15px rgba(74, 93, 63, 0.3);
        }
        .btn-publish:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(74, 93, 63, 0.4);
            color: white;
        }

        footer { margin-top: auto; background: #222; color: #aaa; padding: 40px 0; text-align: center; }
    </style>
</head>
<body>

    <?php include 'header.php'; ?>

    <main class="container studio-container">
        
        <form method="POST" action="publicar.php" enctype="multipart/form-data">
            
            <div class="studio-header d-flex justify-content-between align-items-center" data-aos="fade-down">
                <div>
                    <h2 class="mb-0 fw-bold text-dark">
                        <?php echo $id_original_url > 0 ? "Escrever Continuação" : "Nova História"; ?>
                    </h2>
                    <?php if ($id_original_url > 0): ?>
                        <small class="text-muted">
                            Baseado em: <strong class="text-success"><?php echo htmlspecialchars($historia_original_titulo); ?></strong>
                        </small>
                        <input type="hidden" name="id_original" value="<?php echo $id_original_url; ?>">
                    <?php endif; ?>
                </div>
                
                <div class="d-block d-md-none">
                    <button type="submit" class="btn btn-success btn-sm rounded-pill">Publicar</button>
                </div>
            </div>

            <?php if (!empty($mensagem)): ?>
                <div class="alert alert-<?php echo $tipo_alerta; ?> alert-dismissible fade show rounded-3" role="alert">
                    <?php echo $mensagem; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row g-4">
                
                <div class="col-lg-4 order-lg-2" data-aos="fade-left" data-aos-delay="100">
                    <div class="config-panel">
                        <h5 class="mb-4 fw-bold text-secondary">Detalhes da Obra</h5>
                        
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-uppercase text-muted">Capa do Livro</label>
                            <label for="capa_imagem" class="cover-upload-wrapper">
                                <img id="preview_img" class="cover-preview" alt="Preview">
                                <div class="upload-label" id="upload_text">
                                    <i class="fas fa-cloud-upload-alt fa-2x mb-2"></i><br>
                                    Clique para enviar<br>
                                    <small>(JPG, PNG)</small>
                                </div>
                                <input type="file" id="capa_imagem" name="capa_imagem" accept="image/*" class="d-none" onchange="previewCover(event)">
                            </label>
                        </div>

                        <div class="mb-3">
                            <label for="id_genero" class="form-label small fw-bold text-muted">Gênero Literário</label>
                            <select class="form-select rounded-3 py-2" id="id_genero" name="id_genero" required>
                                <option value="" selected disabled>Escolha uma categoria...</option>
                                <?php 
                                $resultado_generos->data_seek(0);
                                while ($genero = $resultado_generos->fetch_assoc()): ?>
                                    <option value="<?php echo $genero['id_genero']; ?>">
                                        <?php echo htmlspecialchars($genero['nome']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="acesso" class="form-label small fw-bold text-muted">Visibilidade</label>
                            <select class="form-select rounded-3 py-2" id="acesso" name="acesso" required>
                                <option value="publico">🌍 Público (Todos podem ler)</option>
                                <option value="restrito">🔒 Rascunho (Apenas eu)</option>
                            </select>
                        </div>

                        <hr>

                        <button type="submit" class="btn-publish">
                            <i class="fas fa-paper-plane me-2"></i> Publicar História
                        </button>
                    </div>
                </div>

                <div class="col-lg-8 order-lg-1" data-aos="fade-right">
                    <div class="editor-panel">
                        <div class="mb-4">
                            <input type="text" class="form-control form-control-lg" id="titulo" name="titulo" placeholder="Digite o Título Aqui..." required autocomplete="off">
                        </div>

                        <div class="form-group">
                            <textarea class="editor-textarea" id="conteudo" name="conteudo" placeholder="Era uma vez... Comece a escrever sua história aqui." required></textarea>
                        </div>
                    </div>
                </div>

            </div>
        </form>

    </main>

    <footer>
        <div class="container">
            <p class="mb-1">&copy; 2025 Abismo de Letras.</p>
            <p class="small opacity-75">Projeto de TCC - Etec Monsenhor Antonio Magliano.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({ duration: 800, once: true });

        // Função para Preview da Capa
        function previewCover(event) {
            var reader = new FileReader();
            reader.onload = function(){
                var output = document.getElementById('preview_img');
                var text = document.getElementById('upload_text');
                output.src = reader.result;
                output.style.display = 'block';
                text.style.display = 'none'; // Esconde o texto "Clique para enviar"
            };
            if(event.target.files[0]){
                reader.readAsDataURL(event.target.files[0]);
            }
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>