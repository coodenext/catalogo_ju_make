<?php
// admin.php

// Inicia a sessão PHP
// Isso é crucial para manter o estado de login do usuário
session_start();

// Define usuário e senha (apenas para demonstração, use banco de dados e hashes em produção!)
$usuario_correto = 'admin';
$senha_correta = '123'; // MUDE PARA UMA SENHA FORTE E SEGURA!

$login_error = ''; // Variável para armazenar mensagens de erro de login
$show_login_screen = true; // Flag para controlar qual tela exibir

// --- Lógica de Logout ---
if (isset($_GET['logout'])) {
    session_unset();   // Remove todas as variáveis de sessão
    session_destroy(); // Destrói a sessão
    // Redireciona para a página de login para evitar reenvio do formulário de logout
    header('Location: admin.php?loggedout=true');
    exit();
}

// --- Lógica de Processamento de Login ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($username === $usuario_correto && $password === $senha_correta) {
        // Login bem-sucedido
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $username;
        // Redireciona para evitar reenvio do formulário POST
        header('Location: admin.php?loginsuccess=true');
        exit();
    } else {
        // Credenciais inválidas
        $login_error = 'Usuário ou senha incorretos.';
    }
}

// --- Verifica se o usuário está logado para exibir o painel ---
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    $show_login_screen = false;
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo - Ju Make</title>
    <link rel="stylesheet" href="css/admin.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.12.2/dist/sweetalert2.min.css">
</head>
<body>

    <?php if ($show_login_screen): // Exibe a tela de login se não estiver logado ?>
        <div id="loginScreen" class="login-container">
            <div class="login-box">
                <h2>Login Administrativo</h2>
                <form id="loginForm" action="admin.php" method="POST"> <div class="mb-3">
                        <label for="username" class="form-label">Usuário</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Senha</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Entrar</button>
                    <?php if (!empty($login_error)): // Exibe erro de login se houver ?>
                        <div class="mt-3 text-danger">
                            <?php echo $login_error; ?>
                        </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    <?php else: // Exibe o painel administrativo se estiver logado ?>
        <div id="adminPanel" class="admin-panel">
            <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
                <div class="container">
                    <a class="navbar-brand" href="#">Painel Admin</a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarNav">
                        <ul class="navbar-nav">
                            <li class="nav-item">
                                <a class="nav-link" href="#" data-section="products">Produtos</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#" data-section="banners">Banners</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#" data-section="settings">Configurações</a>
                            </li>
                        </ul>
                        <ul class="navbar-nav ms-auto">
                            <li class="nav-item">
                                <a class="nav-link" href="index.html" target="_blank">
                                    <i class="fas fa-external-link-alt"></i> Ver Site
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="admin.php?logout=true" id="logoutBtn"> <i class="fas fa-sign-out-alt"></i> Sair
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>

            <div class="container mt-4">
                <div id="productsSection" class="admin-section">
                    <h2>Gerenciar Produtos</h2>
                    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addProductModal">
                        <i class="fas fa-plus"></i> Novo Produto
                    </button>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Imagem</th>
                                    <th>Nome</th>
                                    <th>Preço</th>
                                    <th>Estoque</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody id="productsTableBody">
                                </tbody>
                        </table>
                    </div>
                </div>

                <div id="bannersSection" class="admin-section" style="display: none;">
                    <h2>Gerenciar Banners</h2>
                    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addBannerModal">
                        <i class="fas fa-plus"></i> Novo Banner
                    </button>
                    <div class="row" id="bannersGrid">
                        </div>
                </div>

                <div id="settingsSection" class="admin-section" style="display: none;">
                    <h2>Configurações</h2>
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Logo da Loja</h5>
                            <div class="mb-3">
                                <img id="currentLogo" src="images/default-logo.png" alt="Logo atual" class="img-thumbnail mb-2">
                                <input type="file" class="form-control" id="logoUpload" accept="image/*">
                            </div>
                            <h5 class="card-title mt-4">WhatsApp da Loja</h5>
                            <div class="mb-3">
                                <input type="text" class="form-control" id="whatsappNumber" placeholder="Número do WhatsApp (ex: 5511999999999)">
                            </div>
                            <button class="btn btn-primary" id="saveSettings">Salvar Configurações</button>
                            
                            <h5 class="card-title mt-4">Alterar Credenciais de Acesso</h5>
                            <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#changeCredentialsModal">
                                <i class="fas fa-key"></i> Alterar Usuário e Senha
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php include 'modals.php'; // Se seus modais estiverem em um arquivo separado, inclua aqui ?>

    <?php endif; // Fim do bloco do painel administrativo ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.12.2/dist/sweetalert2.all.min.js"></script>
    <script src="js/admin.js"></script>

    <script>
        // Lógica para exibir SweetAlert2 com base nos parâmetros da URL (após redirecionamento PHP)
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            
            if (urlParams.has('loginsuccess') && urlParams.get('loginsuccess') === 'true') {
                Swal.fire({
                    icon: 'success',
                    title: 'Login Bem-Sucedido!',
                    text: 'Bem-vindo ao Painel Administrativo.',
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    // Limpa o parâmetro da URL para não mostrar o alerta novamente se a página for recarregada
                    history.replaceState({}, document.title, window.location.pathname);
                });
            } else if (urlParams.has('loggedout') && urlParams.get('loggedout') === 'true') {
                 Swal.fire({
                    icon: 'info',
                    title: 'Desconectado!',
                    text: 'Você foi desconectado com sucesso.',
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    history.replaceState({}, document.title, window.location.pathname);
                });
            }
        });
    </script>
</body>
</html>