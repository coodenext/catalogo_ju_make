<?php
require_once 'includes/config.php';

// Buscar configurações
$stmt = $pdo->query("SELECT * FROM settings");
$settings = [];
while ($row = $stmt->fetch()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// Buscar banners ativos
$stmt = $pdo->query("SELECT * FROM banners WHERE active = 1 ORDER BY created_at DESC");
$banners = $stmt->fetchAll();

// Buscar produtos
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$where = '';
$params = [];

if (!empty($search)) {
    $where = "WHERE name LIKE ?";
    $params[] = "%$search%";
}

$stmt = $pdo->prepare("SELECT * FROM products $where ORDER BY name ASC");
$stmt->execute($params);
$products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $settings['site_name'] ?? 'JU Make'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #ff69b4;
            --secondary-color: #ffd700;
            --text-color: #333;
            --light-bg: #fff5f8;
        }

        body {
            background-color: var(--light-bg);
            color: var(--text-color);
        }

        .navbar {
            background-color: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .navbar-brand img {
            max-height: 50px;
        }

        .banner-carousel {
            margin-bottom: 2rem;
        }

        .banner-carousel .carousel-item img {
            height: 400px;
            object-fit: cover;
        }

        .product-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            transition: transform 0.3s;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .product-card:hover {
            transform: translateY(-5px);
        }

        .product-card img {
            height: 200px;
            object-fit: cover;
        }

        .product-card .card-body {
            padding: 1rem;
        }

        .product-card .price {
            color: var(--primary-color);
            font-size: 1.25rem;
            font-weight: bold;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: #ff1493;
            border-color: #ff1493;
        }

        .btn-whatsapp {
            background-color: #25d366;
            border-color: #25d366;
            color: white;
        }

        .btn-whatsapp:hover {
            background-color: #128c7e;
            border-color: #128c7e;
            color: white;
        }

        .search-box {
            background: white;
            padding: 1rem;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <?php if (!empty($settings['site_logo'])): ?>
                    <img src="<?php echo $settings['site_logo']; ?>" alt="<?php echo $settings['site_name']; ?>">
                <?php else: ?>
                    <?php echo $settings['site_name'] ?? 'JU Make'; ?>
                <?php endif; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="cart.php">
                            <i class="bi bi-cart"></i> Carrinho
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Banners -->
        <?php if (count($banners) > 0): ?>
            <div id="bannerCarousel" class="carousel slide banner-carousel" data-bs-ride="carousel">
                <div class="carousel-indicators">
                    <?php foreach ($banners as $index => $banner): ?>
                        <button type="button" data-bs-target="#bannerCarousel" data-bs-slide-to="<?php echo $index; ?>"
                                <?php echo $index === 0 ? 'class="active"' : ''; ?>></button>
                    <?php endforeach; ?>
                </div>
                <div class="carousel-inner">
                    <?php foreach ($banners as $index => $banner): ?>
                        <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                            <img src="<?php echo $banner['image_url']; ?>" class="d-block w-100" alt="Banner">
                            <?php if (!empty($banner['text'])): ?>
                                <div class="carousel-caption d-none d-md-block">
                                    <p><?php echo $banner['text']; ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#bannerCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon"></span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#bannerCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon"></span>
                </button>
            </div>
        <?php endif; ?>

        <!-- Busca -->
        <div class="search-box">
            <form method="GET" action="" class="row g-3">
                <div class="col-md-10">
                    <input type="text" class="form-control" name="search" placeholder="Buscar produtos..."
                           value="<?php echo $search; ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Buscar</button>
                </div>
            </form>
        </div>

        <!-- Produtos -->
        <div class="row">
            <?php foreach ($products as $product): ?>
                <div class="col-md-4 mb-4">
                    <div class="product-card">
                        <img src="<?php echo $product['image_url']; ?>" class="card-img-top" alt="<?php echo $product['name']; ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $product['name']; ?></h5>
                            <p class="price">R$ <?php echo number_format($product['price'], 2, ',', '.'); ?></p>
                            <div class="d-grid gap-2">
                                <a href="product.php?id=<?php echo $product['id']; ?>" class="btn btn-primary">
                                    Ver Detalhes
                                </a>
                                <button class="btn btn-whatsapp" onclick="addToCart(<?php echo $product['id']; ?>)">
                                    <i class="bi bi-cart-plus"></i> Adicionar ao Carrinho
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function addToCart(productId) {
            // Implementar lógica do carrinho
            alert('Produto adicionado ao carrinho!');
        }
    </script>
</body>
</html> 