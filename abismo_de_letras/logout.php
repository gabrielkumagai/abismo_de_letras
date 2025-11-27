<?php
// Inicia a sessão para que o PHP saiba qual sessão destruir
session_start();

// 1. Remove todas as variáveis de sessão
// Isso limpa os dados como $_SESSION['id_usuario'], $_SESSION['nome'], etc.
session_unset(); 

// 2. Destrói a sessão
// Isso remove os dados da sessão do servidor e invalida o cookie de sessão no navegador.
session_destroy(); 

// 3. Redireciona o usuário para a página inicial (deslogado)
header('Location: index.php'); 
exit;
?>