<?php
include 'conexao.php';
// Garante sessão
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}

$id_usuario = (int)$_SESSION['id_usuario'];
$mensagem = "";
$tipo_alerta = "";
$id_original = isset($_GET['original_id']) ? (int)$_GET['original_id'] : NULL;
$tema_original = "";
$texto_original = "";
$tipo_contribuicao_default = 'rascunho';

// -----------------------------------------------------
// Lógica de Processamento
// -----------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titulo = $conn->real_escape_string($_POST['titulo']);
    $tema = $conn->real_escape_string($_POST['tema']);
    $texto = $conn->real_escape_string($_POST['texto']); 
    $id_original_post = isset($_POST['id_original']) && $_POST['id_original'] != 0 ? (int)$_POST['id_original'] : NULL;
    $tipo_contribuicao = $conn->real_escape_string($_POST['tipo_contribuicao']);

    $sql = "INSERT INTO redacoes_enem (id_usuario, titulo, tema, texto, id_redacao_original, tipo_contribuicao) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("isssis", $id_usuario, $titulo, $tema, $texto, $id_original_post, $tipo_contribuicao);
        
        if ($stmt->execute()) {
            $novo_id = $conn->insert_id;
            header('Location: ver_redacao.php?id=' . $novo_id);
            exit;
        } else {
            $mensagem = "Erro ao salvar: " . $stmt->error;
            $tipo_alerta = "danger";
        }
        $stmt->close();
    }
}

// -----------------------------------------------------
// Carregar dados originais (Colaboração)
// -----------------------------------------------------
if ($id_original !== NULL && $id_original > 0) {
    $sql_original = "SELECT tema, texto FROM redacoes_enem WHERE id_redacao = ?";
    $stmt_original = $conn->prepare($sql_original);
    $stmt_original->bind_param("i", $id_original);
    $stmt_original->execute();
    $resultado = $stmt_original->get_result();
    
    if ($resultado->num_rows > 0) {
        $redacao = $resultado->fetch_assoc();
        $tema_original = $redacao['tema'];
        $texto_original = $redacao['texto']; 
        $tipo_contribuicao_default = 'correcao_peer';
    }
    $stmt_original->close();
}

$temas_base = [
    "O desafio da mobilidade urbana no século XXI",
    "A importância da democratização do acesso à internet no Brasil",
    "Caminhos para combater a invisibilidade social",
    "Os impactos da inteligência artificial no mercado de trabalho",
    "A persistência da violência contra a mulher na sociedade brasileira"
];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Escrever Redação - Abismo de Letras</title>
    
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
            background-color: #f4f6f5; /* Cinza muito suave para foco */
            color: var(--text-dark);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        h1, h2, h3 { font-family: 'Playfair Display', serif; }

        /* --- LAYOUT DO EDITOR --- */
        .editor-container {
            padding-top: 110px;
            padding-bottom: 60px;
        }

        .editor-header {
            margin-bottom: 30px;
            border-bottom: 1px solid #e0e0e0;
            padding-bottom: 15px;
        }

        /* Sidebar de Opções */
        .options-panel {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            height: 100%;
            border: 1px solid rgba(0,0,0,0.02);
        }

        /* Papel de Redação */
        .writing-paper {
            background: white;
            padding: 50px;
            border-radius: 5px; /* Bordas retas como papel */
            box-shadow: 0 5px 25px rgba(0,0,0,0.08);
            min-height: 800px;
            position: relative;
            border-top: 5px solid var(--gold);
        }

        /* Estilo do Textarea (Linhas de caderno opcionais ou limpo) */
        .essay-textarea {
            width: 100%;
            height: 600px;
            border: none;
            resize: none;
            font-family: 'Merriweather', serif;
            font-size: 1.1rem;
            line-height: 2; /* Espaçamento duplo ideal para correção */
            color: #333;
            background: transparent;
            outline: none;
            background-image: linear-gradient(transparent 95%, #e0e0e0 95%);
            background-size: 100% 2rem; /* Altura da linha */
            margin-top: 20px;
        }
        .essay-textarea::placeholder { font-style: italic; color: #bbb; }

        .essay-title-input {
            border: none;
            border-bottom: 2px solid #eee;
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            font-weight: 700;
            width: 100%;
            padding: 10px 0;
            background: transparent;
            margin-bottom: 20px;
        }
        .essay-title-input:focus { outline: none; border-color: var(--primary-green); }

        /* Badge de Status */
        .mode-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 50px;
            font-weight: bold;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 15px;
        }
        .mode-new { background: rgba(74, 93, 63, 0.1); color: var(--dark-green); }
        .mode-edit { background: rgba(212, 175, 55, 0.15); color: #b08d1e; border: 1px solid var(--gold); }

        /* Botão Enviar */
        .btn-submit {
            background: linear-gradient(135deg, var(--dark-green), var(--primary-green));
            color: white; border: none; width: 100%;
            padding: 12px; border-radius: 50px; font-weight: bold;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1); transition: 0.3s;
        }
        .btn-submit:hover { transform: translateY(-2px); color: white; box-shadow: 0 10px 20px rgba(0,0,0,0.15); }

        /* Contador */
        .word-counter {
            text-align: right;
            font-size: 0.85rem;
            color: #888;
            margin-top: 10px;
            font-weight: 600;
        }

        /* Competências Info */
        .info-box {
            background: #f8f9fa; border-radius: 10px; padding: 15px;
            margin-top: 20px; border-left: 3px solid var(--primary-green);
        }

        footer { margin-top: auto; background: #222; color: #aaa; padding: 40px 0; text-align: center; }
    </style>
</head>
<body>

    <?php include 'header.php'; ?>

    <main class="container editor-container">
        
        <form method="POST" action="publicar_redacao.php">
            
            <div class="editor-header d-flex justify-content-between align-items-center" data-aos="fade-down">
                <div>
                    <h2 class="mb-0 fw-bold">Sala de Redação</h2>
                    <p class="text-muted small mb-0">Foco total. Respeite a norma culta e os direitos humanos.</p>
                </div>
            </div>

            <?php if (!empty($mensagem)): ?>
                <div class="alert alert-<?php echo $tipo_alerta; ?> alert-dismissible fade show" role="alert">
                    <?php echo $mensagem; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row g-4">
                
                <div class="col-lg-4" data-aos="fade-right">
                    <div class="options-panel">
                        <h5 class="fw-bold text-secondary mb-4"><i class="fas fa-sliders-h me-2"></i> Configurações</h5>

                        <?php if ($id_original > 0) : ?>
                            <div class="alert alert-warning border-0 small">
                                <i class="fas fa-info-circle me-1"></i> Você está colaborando no tema:<br>
                                <strong class="d-block mt-2 text-dark"><?php echo htmlspecialchars($tema_original); ?></strong>
                            </div>
                            <input type="hidden" name="id_original" value="<?php echo $id_original; ?>">
                            <input type="hidden" name="tema" value="<?php echo htmlspecialchars($tema_original); ?>">

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">Tipo de Contribuição</label>
                                <select class="form-select" name="tipo_contribuicao" required>
                                    <option value="correcao_peer" <?php echo $tipo_contribuicao_default == 'correcao_peer' ? 'selected' : ''; ?>>Correção / Revisão</option>
                                    <option value="continuação" <?php echo $tipo_contribuicao_default == 'continuação' ? 'selected' : ''; ?>>Continuar Texto</option>
                                </select>
                            </div>

                        <?php else: ?>
                            <input type="hidden" name="id_original" value="0">
                            <input type="hidden" name="tipo_contribuicao" value="rascunho">

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">Tema da Proposta</label>
                                <select class="form-select py-2" id="tema" name="tema" required>
                                    <option value="" selected disabled>Selecione um tema...</option>
                                    <?php foreach ($temas_base as $t): ?>
                                        <option value="<?php echo htmlspecialchars($t); ?>"><?php echo htmlspecialchars($t); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>

                        <div class="info-box">
                            <h6 class="fw-bold small"><i class="fas fa-lightbulb text-warning me-1"></i> Lembretes ENEM:</h6>
                            <ul class="list-unstyled small text-muted mb-0 ps-1">
                                <li class="mb-1">• Mínimo 7 linhas, máx 30.</li>
                                <li class="mb-1">• 5 Competências avaliadas.</li>
                                <li>• Proposta de intervenção detalhada.</li>
                            </ul>
                        </div>

                        <hr class="my-4">
                        
                        <button type="submit" class="btn-submit">
                            <i class="fas fa-paper-plane me-2"></i> Enviar Texto
                        </button>
                    </div>
                </div>

                <div class="col-lg-8" data-aos="fade-left">
                    <div class="writing-paper">
                        
                        <?php if ($id_original > 0): ?>
                            <span class="mode-badge mode-edit"><i class="fas fa-pen-alt me-1"></i> Modo Edição/Correção</span>
                        <?php else: ?>
                            <span class="mode-badge mode-new"><i class="fas fa-file-alt me-1"></i> Novo Rascunho</span>
                        <?php endif; ?>

                        <input type="text" class="essay-title-input" id="titulo" name="titulo" placeholder="Dê um título à sua redação..." required autocomplete="off">

                        <textarea class="essay-textarea" id="texto" name="texto" placeholder="Comece seu texto aqui (introdução)..." required oninput="countWords()"><?php echo $id_original > 0 ? htmlspecialchars($texto_original) : ''; ?></textarea>
                        
                        <div class="word-counter" id="counter_display">
                            0 palavras | 0 caracteres
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

        // Contador de Palavras Simples
        function countWords() {
            let text = document.getElementById('texto').value;
            let charCount = text.length;
            let wordCount = text.trim() === '' ? 0 : text.trim().split(/\s+/).length;
            
            // Estimativa de linhas (média de 75 chars por linha manuscrita aprox, varia muito no digital)
            // Apenas ilustrativo
            
            document.getElementById('counter_display').innerText = `${wordCount} palavras | ${charCount} caracteres`;
        }

        // Roda ao carregar (caso seja edição e já tenha texto)
        window.onload = countWords;
    </script>
</body>
</html>
<?php $conn->close(); ?>