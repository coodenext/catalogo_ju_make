<?php
require_once 'auth.php';
checkAuth();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo - JU Make</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #f8f9fa;
            padding: 20px;
        }
        .sidebar .nav-link {
            color: #333;
            padding: 10px 15px;
            margin: 5px 0;
            border-radius: 5px;
        }
        .sidebar .nav-link:hover {
            background-color: #e9ecef;
        }
        .sidebar .nav-link.active {
            background-color: #0d6efd;
            color: white;
        }
        .content {
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar">
                <div class="text-center mb-4">
                    <h4>JU Make</h4>
                    <p class="text-muted">Painel Administrativo</p>
                </div>
                
                <nav class="nav flex-column">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="index.php">
                        <i class="bi bi-house-door"></i> Dashboard
                    </a>
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : ''; ?>" href="products.php">
                        <i class="bi bi-box"></i> Produtos
                    </a>
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'banners.php' ? 'active' : ''; ?>" href="banners.php">
                        <i class="bi bi-images"></i> Banners
                    </a>
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>" href="settings.php">
                        <i class="bi bi-gear"></i> Configurações
                    </a>
                    <a class="nav-link text-danger" href="logout.php">
                        <i class="bi bi-box-arrow-right"></i> Sair
                    </a>
                </nav>
            </div>

            <!-- Conteúdo Principal -->
            <div class="col-md-9 col-lg-10 content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><?php echo $page_title ?? 'Dashboard'; ?></h2>
                    <div class="user-info">
                        <span class="text-muted">Bem-vindo, Admin</span>
                    </div>
                </div> 