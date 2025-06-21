<?php
// admin.php

// Inicia a sessão PHP. Essencial para manter o estado de login do usuário.
session_start();

// Inclui o arquivo de conexão com o banco de dados.
require_once 'config.php';

// --- Credenciais de Login para o Painel Administrativo (APENAS PARA DEMONSTRAÇÃO) ---
// EM UM AMBIENTE DE PRODUÇÃO REAL, VOCÊ NUNCA DEVE ARMAZENAR SENHAS EM TEXTO SIMPLES.
// Use password_hash() para armazenar senhas criptografadas no banco de dados
// e password_verify() para verificá-las durante o login.
$admin_username = 'admin';
$admin_password = '123'; // <<< MUDE ESTA SENHA PARA ALGO SEGURO E FORTE!

// Variáveis para mensagens SweetAlert2
$login_message_script = ''; // Para mensagens de login/logout
$form_message_script = '';  // Para mensagens de sucesso/erro de operações de formulário (ex: adicionar produto)

// --- Lógica de Logout ---
// Se o parâmetro 'logout' estiver presente na URL, destrói a sessão.
if (isset($_GET['logout'])) {
    session_unset();   // Remove todas as variáveis de sessão
    session_destroy(); // Destrói a sessão
    // Redireciona para a página de login com um indicador de logout bem-sucedido
    header('Location: admin.php?loggedout=true');
    exit();
}

// --- Lógica de Processamento de Login ---
// Se a requisição for POST (significa que o formulário de login foi submetido)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Busca usuário do banco
    $userFromDb = null;
    try {
        $stmt = $pdo->prepare('SELECT username, password FROM admin_users WHERE id = 1 LIMIT 1');
        $stmt->execute();
        $userFromDb = $stmt->fetch();
    } catch (PDOException $e) {
        $userFromDb = null;
    }

    if ($userFromDb) {
        if ($username === $userFromDb['username'] && password_verify($password, $userFromDb['password'])) {
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $username;
            header('Location: admin.php?loginsuccess=true');
            exit();
        } else {
            $login_message_script = "
                Swal.fire({
                    icon: 'error',
                    title: 'Erro!',
                    text: 'Usuário ou senha incorretos.',
                    showConfirmButton: true
                });
                document.addEventListener('DOMContentLoaded', function() {
                    document.getElementById('password').value = '';
                });
            ";
        }
    } else {
        // Fallback para usuário/senha padrão
        if ($username === $admin_username && $password === $admin_password) {
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $username;
            header('Location: admin.php?loginsuccess=true');
            exit();
        } else {
            $login_message_script = "
                Swal.fire({
                    icon: 'error',
                    title: 'Erro!',
                    text: 'Usuário ou senha incorretos.',
                    showConfirmButton: true
                });
                document.addEventListener('DOMContentLoaded', function() {
                    document.getElementById('password').value = '';
                });
            ";
        }
    }
}

// --- Verifica se o usuário está logado para exibir o painel ou a tela de login ---
$show_login_screen = true; // Por padrão, mostra a tela de login
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    $show_login_screen = false; // Se logado, mostra o painel
}

// --- Prepara mensagens de formulário (sucesso/erro de adição, edição, etc.) para SweetAlert2 ---
// Essas mensagens são armazenadas em $_SESSION['form_message'] por scripts como 'actions/add_product.php'
if (isset($_SESSION['form_message'])) {
    // Divide a mensagem em tipo (success, error) e texto
    list($type, $text) = explode(':', $_SESSION['form_message'], 2);
    $title = ($type === 'success') ? 'Sucesso!' : 'Erro!';
    
    // Gera o script JavaScript para exibir o SweetAlert2
    $form_message_script = "
        Swal.fire({
            icon: '{$type}',
            title: '{$title}',
            text: '{$text}',
            showConfirmButton: true,
            timer: " . ($type === 'error' ? 'null' : '2000') . " // Sem timer para erro, 2s para sucesso
        });
    ";
    unset($_SESSION['form_message']); // Limpa a mensagem da sessão para que não apareça novamente
}

// --- Prepara mensagens de login/logout bem-sucedido para SweetAlert2 (usando parâmetros GET) ---
// Verificamos diretamente a superglobal $_GET, sem usar URLSearchParams (que é JS)
if (isset($_GET['loginsuccess']) && $_GET['loginsuccess'] === 'true') {
    $login_message_script = "
        Swal.fire({
            icon: 'success',
            title: 'Login Bem-Sucedido!',
            text: 'Bem-vindo ao Painel Administrativo.',
            showConfirmButton: false,
            timer: 1500
        }).then(() => {
            history.replaceState({}, document.title, window.location.pathname); // Limpa o parâmetro da URL
        });
    ";
} elseif (isset($_GET['loggedout']) && $_GET['loggedout'] === 'true') {
    $login_message_script = "
        Swal.fire({
            icon: 'info',
            title: 'Desconectado!',
            text: 'Você foi desconectado com sucesso.',
            showConfirmButton: false,
            timer: 1500
        }).then(() => {
            history.replaceState({}, document.title, window.location.pathname); // Limpa o parâmetro da URL
        });
    ";
}

// Carregar o número do WhatsApp do banco
$whatsapp_number = '';
try {
    $stmt = $pdo->query("SELECT whatsapp FROM configuracoes WHERE id = 1 LIMIT 1");
    if ($row = $stmt->fetch()) {
        $whatsapp_number = $row['whatsapp'];
    }
} catch (PDOException $e) {
    $whatsapp_number = '';
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

    <?php if ($show_login_screen): // Exibe a tela de login se o usuário NÃO estiver logado ?>
        <div id="loginScreen" class="login-container">
            <div class="login-box">
                <h2>Login Administrativo</h2>
                <form id="loginForm" action="admin.php" method="POST">
                    <div class="mb-3">
                        <label for="username" class="form-label">Usuário</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Senha</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Entrar</button>
                    </form>
            </div>
        </div>
    <?php else: // Exibe o painel administrativo se o usuário estiver logado ?>
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
                                <a class="nav-link" href="index.php" target="_blank">
                                    <i class="fas fa-external-link-alt"></i> Ver Site
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="admin.php?logout=true" id="logoutBtn">
                                    <i class="fas fa-sign-out-alt"></i> Sair
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
                                <?php
                                // --- Lógica para Carregar Produtos do Banco de Dados ---
                                try {
                                    $stmt = $pdo->query("SELECT id, nome, preco, estoque, imagem FROM produtos ORDER BY id DESC");
                                    if ($stmt->rowCount() > 0) {
                                        while ($row = $stmt->fetch()) {
                                            $imagePath = !empty($row['imagem']) ? "uploads/produtos/{$row['imagem']}" : "images/placeholder.png"; // Placeholder se não houver imagem
                                            echo "<tr>";
                                            echo "<td><img src='{$imagePath}' alt='{$row['nome']}' style='width: 50px; height: 50px; object-fit: cover; border-radius: 5px;'></td>";
                                            echo "<td>" . htmlspecialchars($row['nome']) . "</td>";
                                            echo "<td>R$ " . number_format($row['preco'], 2, ',', '.') . "</td>";
                                            echo "<td>" . htmlspecialchars($row['estoque']) . "</td>";
                                            echo "<td>
                                                    <button class='btn btn-sm btn-warning edit-product-btn' 
                                                        data-id='{$row['id']}' 
                                                        data-nome='{$row['nome']}' 
                                                        data-preco='{$row['preco']}' 
                                                        data-estoque='{$row['estoque']}' 
                                                        data-descricao='{$row['descricao']}' 
                                                        data-bs-toggle='modal' data-bs-target='#editProductModal'>Editar</button>
                                                    <button class='btn btn-sm btn-danger delete-product-btn' data-id='{$row['id']}'>Excluir</button>
                                                  </td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='5' class='text-center'>Nenhum produto cadastrado ainda.</td></tr>";
                                    }
                                } catch (PDOException $e) {
                                    echo "<tr><td colspan='5' class='text-center text-danger'>Erro ao carregar produtos: " . $e->getMessage() . "</td></tr>";
                                }
                                ?>
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
                        <?php
                        // Lógica para carregar e exibir banners
                        try {
                            $stmtBanners = $pdo->query("SELECT id, imagem, link, ativo FROM banners ORDER BY ordem ASC, id DESC");
                            if ($stmtBanners->rowCount() > 0) {
                                while ($banner = $stmtBanners->fetch(PDO::FETCH_ASSOC)) {
                                    $bannerImagePath = !empty($banner['imagem']) ? "uploads/banners/" . htmlspecialchars($banner['imagem']) : "images/placeholder-banner.png";
                                    $statusClass = $banner['ativo'] ? 'badge bg-success' : 'badge bg-danger';
                                    $statusText = $banner['ativo'] ? 'Ativo' : 'Inativo';
                        ?>
                                    <div class="col-6 col-md-4 col-lg-3 mb-4">
                                        <div class="card h-100">
                                            <img src="<?php echo $bannerImagePath; ?>" class="card-img-top" alt="Banner">
                                            <div class="card-body">
                                                <h6 class="card-title text-truncate"><?php echo htmlspecialchars($banner['imagem']); ?></h6>
                                                <p class="card-text"><span class="<?php echo $statusClass; ?>"><?php echo $statusText; ?></span></p>
                                                <p class="card-text text-muted text-truncate"><small><?php echo !empty($banner['link']) ? htmlspecialchars($banner['link']) : 'Sem link'; ?></small></p>
                                                <div class="d-flex justify-content-between">
                                                    <button class="btn btn-sm btn-info edit-banner-btn" data-id="<?php echo $banner['id']; ?>" data-imagem="<?php echo htmlspecialchars($banner['imagem']); ?>" data-link="<?php echo htmlspecialchars($banner['link']); ?>" data-bs-toggle="modal" data-bs-target="#editBannerModal">Editar</button>
                                                    <button class="btn btn-sm btn-danger delete-banner-btn" data-id="<?php echo $banner['id']; ?>">Excluir</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                        <?php
                                }
                            } else {
                                echo '<div class="col-12 text-center"><p>Nenhum banner cadastrado ainda.</p></div>';
                            }
                        } catch (PDOException $e) {
                            echo '<div class="col-12 text-center text-danger"><p>Erro ao carregar banners: ' . $e->getMessage() . '</p></div>';
                        }
                        ?>
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
                            <form id="settingsForm" action="actions/save_settings.php" method="POST">
                                <div class="mb-3">
                                    <input type="text" class="form-control" id="whatsappNumber" name="whatsapp" placeholder="Número do WhatsApp (ex: 5511999999999)" value="<?php echo htmlspecialchars($whatsapp_number); ?>">
                                </div>
                                <button type="submit" class="btn btn-primary" id="saveSettings">Salvar Configurações</button>
                            </form>
                            <h5 class="card-title mt-4">Alterar Credenciais de Acesso</h5>
                            <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#changeCredentialsModal">
                                <i class="fas fa-key"></i> Alterar Usuário e Senha
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="addProductModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Novo Produto</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="addProductForm" action="actions/add_product.php" method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label">Nome do Produto</label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Valor</label>
                                <input type="number" class="form-control" name="price" step="0.01" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Quantidade em Estoque</label>
                                <input type="number" class="form-control" name="stock" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Detalhes/Descrição</label>
                                <textarea class="form-control" name="description" rows="3"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Imagem do Produto</label>
                                <input type="file" class="form-control" name="image" accept="image/*" required>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-primary">Salvar Produto</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="addBannerModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Novo Banner</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="addBannerForm" action="actions/add_banner.php" method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label">Imagem do Banner</label>
                                <input type="file" class="form-control" name="bannerImage" accept="image/*" required>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-primary">Salvar Banner</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="changeCredentialsModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Alterar Credenciais de Acesso</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="changeCredentialsForm" action="actions/change_credentials.php" method="POST">
                            <div class="mb-3">
                                <label class="form-label">Novo Nome de Usuário</label>
                                <input type="text" class="form-control" id="newUsername" name="newUsername" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Nova Senha</label>
                                <input type="password" class="form-control" id="newPassword" name="newPassword" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Confirmar Nova Senha</label>
                                <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="editProductModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Editar Produto</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="editProductForm" action="actions/edit_product.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" id="editProductId" name="productId">
                            <div class="mb-3">
                                <label class="form-label">Nome do Produto</label>
                                <input type="text" class="form-control" id="editProductName" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Valor</label>
                                <input type="number" class="form-control" id="editProductPrice" name="price" step="0.01" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Quantidade em Estoque</label>
                                <input type="number" class="form-control" id="editProductStock" name="stock" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Detalhes/Descrição</label>
                                <textarea class="form-control" id="editProductDescription" name="description" rows="3"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Imagem do Produto</label>
                                <input type="file" class="form-control" id="editProductImage" name="image" accept="image/*">
                                <small class="text-muted">Deixe em branco para manter a imagem atual</small>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de Edição de Banner -->
        <div class="modal fade" id="editBannerModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Editar Banner</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="editBannerForm" action="actions/edit_banner.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="banner_id" id="editBannerId">
                            <div class="mb-3">
                                <label class="form-label">Imagem do Banner</label>
                                <input type="file" class="form-control" name="bannerImage" accept="image/*">
                                <small class="text-muted">Deixe em branco para manter a imagem atual</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Link do Banner</label>
                                <input type="text" class="form-control" name="banner_link" id="editBannerLink">
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    <?php endif; // Fim do bloco condicional do painel/login ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.12.2/dist/sweetalert2.all.min.js"></script>
    <script src="js/admin.js"></script>

    <script>
        // Este script é executado após o carregamento do DOM
        document.addEventListener('DOMContentLoaded', function() {
            // SweetAlert2 para mensagens de login/logout ou erro de login.
            // O PHP já preparou o script `$login_message_script` para ser injetado aqui.
            <?php echo $login_message_script; ?>

            // SweetAlert2 para mensagens de sucesso/erro de formulário (ex: adição de produto).
            // O PHP já preparou o script `$form_message_script` para ser injetado aqui.
            <?php echo $form_message_script; ?>

            // Lógica de navegação entre as seções do painel
            const adminPanel = document.getElementById('adminPanel');
            const navLinks = document.querySelectorAll('.navbar-nav .nav-link');
            const adminSections = document.querySelectorAll('.admin-section');

            navLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const sectionId = this.dataset.section;

                    // Esconde todas as seções
                    adminSections.forEach(section => {
                        section.style.display = 'none';
                    });

                    // Mostra a seção clicada
                    const targetSection = document.getElementById(sectionId + 'Section');
                    if (targetSection) {
                        targetSection.style.display = 'block';
                    }

                    // Remove a classe 'active' de todos os links e adiciona ao clicado
                    navLinks.forEach(navLink => navLink.classList.remove('active'));
                    this.classList.add('active');
                });
            });

            // Ativa a seção de "Produtos" por padrão quando o painel é exibido
            if (adminPanel && adminPanel.style.display !== 'none') {
                const productsNavLink = document.querySelector('[data-section="products"]');
                if (productsNavLink) {
                    productsNavLink.click(); // Simula um clique para exibir a seção de produtos
                }
            }
            
            // --- Outras lógicas JS para interações DENTRO do painel ---
            // Como as operações de CRUD serão via PHP direto, o JS aqui é mais para interações de UI.
            
            // Exemplo: Botão Salvar Configurações (apenas demonstração, precisa de backend PHP)
            const saveSettingsBtn = document.getElementById('saveSettings');
            if (saveSettingsBtn) {
                saveSettingsBtn.addEventListener('click', function() {
                    // Aqui você enviaria os dados para um arquivo PHP (ex: actions/save_settings.php)
                    Swal.fire({
                        icon: 'info',
                        title: 'Salvando Configurações...',
                        text: 'Tava cansado e com sono, depois termino isso rsrsrs.',
                        showConfirmButton: false,
                        timer: 2000
                    });
                });
            }

            // Você pode adicionar lógicas JavaScript para pré-visualização de imagens,
            // validação de formulário antes do envio (ainda assim, o PHP deve validar também!), etc.
        });

        // Preencher modal de edição de banner
        $(document).on('click', '.edit-banner-btn', function() {
            var id = $(this).data('id');
            var link = $(this).data('link');
            $('#editBannerId').val(id);
            $('#editBannerLink').val(link);
        });
        // Excluir banner
        $(document).on('click', '.delete-banner-btn', function() {
            if(confirm('Tem certeza que deseja excluir este banner?')) {
                var id = $(this).data('id');
                var form = $('<form>', {action: 'actions/delete_banner.php', method: 'POST'}).append($('<input>', {type: 'hidden', name: 'banner_id', value: id}));
                $('body').append(form);
                form.submit();
            }
        });

        // Excluir produto
        $(document).on('click', '.delete-product-btn', function() {
            if(confirm('Tem certeza que deseja excluir este produto?')) {
                var id = $(this).data('id');
                var form = $('<form>', {action: 'actions/delete_product.php', method: 'POST'}).append($('<input>', {type: 'hidden', name: 'product_id', value: id}));
                $('body').append(form);
                form.submit();
            }
        });

        // Preencher modal de edição de produto
        $(document).on('click', '.edit-product-btn', function() {
            $('#editProductId').val($(this).data('id'));
            $('#editProductName').val($(this).data('nome'));
            $('#editProductPrice').val($(this).data('preco'));
            $('#editProductStock').val($(this).data('estoque'));
            $('#editProductDescription').val($(this).data('descricao'));
        });
    </script>
</body>
</html>