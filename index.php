<?php
require_once 'includes/config.php';

// Buscar banners ativos
$stmt = $pdo->query("SELECT * FROM banners WHERE active = 1 ORDER BY display_order ASC");
$banners = $stmt->fetchAll();

// Buscar produtos ativos
$stmt = $pdo->query("SELECT * FROM products WHERE active = 1 ORDER BY name ASC");
$products = $stmt->fetchAll();

// Buscar configurações
$stmt = $pdo->query("SELECT * FROM settings");
$settings = [];
while ($row = $stmt->fetch()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $settings['site_name'] ?? 'Catálogo Ju Make'; ?></title>
    <meta name="description" content="<?php echo $settings['site_description'] ?? ''; ?>">
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
            transition: transform 0.2s;
            background: white;
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .product-card:hover {
            transform: translateY(-5px);
        }

        .product-card img {
            height: 200px;
            object-fit: cover;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: #ff1493;
            border-color: #ff1493;
        }

        .footer {
            background-color: white;
            padding: 2rem 0;
            margin-top: 3rem;
        }

        .social-links a {
            color: var(--text-color);
            font-size: 1.5rem;
            margin: 0 0.5rem;
        }

        .social-links a:hover {
            color: var(--primary-color);
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <?php if (isset($settings['logo_url'])): ?>
                    <img src="<?php echo $settings['logo_url']; ?>" alt="<?php echo $settings['site_name'] ?? 'Catálogo Ju Make'; ?>">
                <?php else: ?>
                    <?php echo $settings['site_name'] ?? 'Catálogo Ju Make'; ?>
                <?php endif; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#products">Produtos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Contato</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Banner Carousel -->
    <div class="container mt-4">
        <?php if (!empty($banners)): ?>
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
                            <img src="<?php echo $banner['image_url']; ?>" class="d-block w-100" alt="<?php echo $banner['title']; ?>">
                            <?php if ($banner['text']): ?>
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
    </div>

    <!-- Products Section -->
    <section id="products" class="container my-5">
        <h2 class="text-center mb-4">Nossos Produtos</h2>
        <div class="row">
            <?php foreach ($products as $product): ?>
                <div class="col-md-4 mb-4">
                    <div class="card product-card">
                        <img src="<?php echo $product['image_url']; ?>" class="card-img-top" alt="<?php echo $product['name']; ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $product['name']; ?></h5>
                            <p class="card-text"><?php echo $product['description']; ?></p>
                            <p class="card-text">
                                <strong>Preço:</strong> R$ <?php echo number_format($product['price'], 2, ',', '.'); ?>
                            </p>
                            <?php if (isset($settings['whatsapp_number'])): ?>
                                <a href="https://wa.me/<?php echo $settings['whatsapp_number']; ?>?text=<?php echo urlencode("Olá! Gostaria de saber mais sobre o produto: {$product['name']}"); ?>" 
                                   class="btn btn-primary w-100" target="_blank">
                                    <i class="bi bi-whatsapp"></i> Comprar pelo WhatsApp
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="container my-5">
        <h2 class="text-center mb-4">Entre em Contato</h2>
        <div class="row justify-content-center">
            <div class="col-md-8 text-center">
                <?php if (isset($settings['whatsapp_number'])): ?>
                    <a href="https://wa.me/<?php echo $settings['whatsapp_number']; ?>" class="btn btn-primary btn-lg mb-3" target="_blank">
                        <i class="bi bi-whatsapp"></i> WhatsApp
                    </a>
                <?php endif; ?>
                
                <div class="social-links mt-4">
                    <?php if (isset($settings['instagram_url'])): ?>
                        <a href="<?php echo $settings['instagram_url']; ?>" target="_blank">
                            <i class="bi bi-instagram"></i>
                        </a>
                    <?php endif; ?>
                    
                    <?php if (isset($settings['facebook_url'])): ?>
                        <a href="<?php echo $settings['facebook_url']; ?>" target="_blank">
                            <i class="bi bi-facebook"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container text-center">
            <p>&copy; <?php echo date('Y'); ?> <?php echo $settings['site_name'] ?? 'Catálogo Ju Make'; ?>. Todos os direitos reservados.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 