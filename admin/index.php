<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/auth.php';

// Verifica se está logado
requireLogin();

// Busca estatísticas
$stats = [
    'products' => $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn(),
    'products_low_stock' => $pdo->query("SELECT COUNT(*) FROM products WHERE stock <= 5")->fetchColumn(),
    'banners' => $pdo->query("SELECT COUNT(*) FROM banners")->fetchColumn(),
    'banners_active' => $pdo->query("SELECT COUNT(*) FROM banners WHERE active = 1")->fetchColumn()
];

// Busca últimos produtos
$latest_products = $pdo->query("
    SELECT * FROM products 
    ORDER BY created_at DESC 
    LIMIT 5
")->fetchAll();

// Busca produtos com estoque baixo
$low_stock_products = $pdo->query("
    SELECT * FROM products 
    WHERE stock <= 5 
    ORDER BY stock ASC 
    LIMIT 5
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #212529;
            padding-top: 20px;
        }
        .sidebar .nav-link {
            color: #fff;
            padding: 10px 20px;
            margin: 5px 0;
        }
        .sidebar .nav-link:hover {
            background-color: rgba(255,255,255,0.1);
        }
        .sidebar .nav-link.active {
            background-color: #0d6efd;
        }
        .sidebar .nav-link i {
            margin-right: 10px;
        }
        .main-content {
            padding: 20px;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid #eee;
            padding: 15px 20px;
        }
        .stat-card {
            text-align: center;
            padding: 20px;
        }
        .stat-card i {
            font-size: 2rem;
            margin-bottom: 10px;
            color: #0d6efd;
        }
        .stat-card h3 {
            font-size: 1.5rem;
            margin-bottom: 5px;
        }
        .stat-card p {
            color: #6c757d;
            margin: 0;
        }
        .table th {
            border-top: none;
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar">
                <div class="d-flex flex-column">
                    <div class="text-center mb-4">
                        <h4 class="text-white"><?php echo SITE_NAME; ?></h4>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="index.php">
                                <i class="bi bi-house"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="products.php">
                                <i class="bi bi-box"></i> Produtos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="banners.php">
                                <i class="bi bi-image"></i> Banners
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="settings.php">
                                <i class="bi bi-gear"></i> Configurações
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">
                                <i class="bi bi-box-arrow-right"></i> Sair
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Dashboard</h2>
                    <div>
                        <span class="text-muted">Bem-vindo, <?php echo $_SESSION['admin_name']; ?></span>
                    </div>
                </div>
                
                <!-- Statistics -->
                <div class="row">
                    <div class="col-md-3">
                        <div class="card stat-card">
                            <i class="bi bi-box"></i>
                            <h3><?php echo $stats['products']; ?></h3>
                            <p>Total de Produtos</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card">
                            <i class="bi bi-exclamation-triangle"></i>
                            <h3><?php echo $stats['products_low_stock']; ?></h3>
                            <p>Produtos com Estoque Baixo</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card">
                            <i class="bi bi-image"></i>
                            <h3><?php echo $stats['banners']; ?></h3>
                            <p>Total de Banners</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card">
                            <i class="bi bi-check-circle"></i>
                            <h3><?php echo $stats['banners_active']; ?></h3>
                            <p>Banners Ativos</p>
                        </div>
                    </div>
                </div>
                
                <!-- Latest Products -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Últimos Produtos</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Nome</th>
                                        <th>Preço</th>
                                        <th>Estoque</th>
                                        <th>Data</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($latest_products as $product): ?>
                                        <tr>
                                            <td><?php echo $product['name']; ?></td>
                                            <td>R$ <?php echo number_format($product['price'], 2, ',', '.'); ?></td>
                                            <td><?php echo $product['stock']; ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($product['created_at'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Low Stock Products -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Produtos com Estoque Baixo</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Nome</th>
                                        <th>Preço</th>
                                        <th>Estoque</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($low_stock_products as $product): ?>
                                        <tr>
                                            <td><?php echo $product['name']; ?></td>
                                            <td>R$ <?php echo number_format($product['price'], 2, ',', '.'); ?></td>
                                            <td>
                                                <span class="badge bg-danger"><?php echo $product['stock']; ?></span>
                                            </td>
                                            <td>
                                                <a href="products.php?action=edit&id=<?php echo $product['id']; ?>" class="btn btn-sm btn-primary">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 