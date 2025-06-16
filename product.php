<?php
require_once 'includes/config.php';

// Verificar se o ID do produto foi fornecido
if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

// Buscar configurações
$stmt = $pdo->query("SELECT * FROM settings");
$settings = [];
while ($row = $stmt->fetch()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// Buscar detalhes do produto
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$_GET['id']]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product['name']; ?> - <?php echo $settings['site_name'] ?? 'JU Make'; ?></title>
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

        .product-details {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .product-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 10px;
        }

        .product-price {
            color: var(--primary-color);
            font-size: 2rem;
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

        .stock-badge {
            font-size: 1rem;
            padding: 0.5rem 1rem;
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
        <div class="product-details">
            <div class="row">
                <div class="col-md-6">
                    <img src="<?php echo $product['image_url']; ?>" alt="<?php echo $product['name']; ?>" class="product-image">
                </div>
                <div class="col-md-6">
                    <h1 class="mb-3"><?php echo $product['name']; ?></h1>
                    
                    <div class="mb-3">
                        <span class="product-price">R$ <?php echo number_format($product['price'], 2, ',', '.'); ?></span>
                    </div>

                    <div class="mb-3">
                        <span class="badge <?php echo $product['stock'] > 0 ? 'bg-success' : 'bg-danger'; ?> stock-badge">
                            <?php echo $product['stock'] > 0 ? 'Em Estoque' : 'Fora de Estoque'; ?>
                        </span>
                    </div>

                    <?php if (!empty($product['details'])): ?>
                        <div class="mb-4">
                            <h5>Detalhes do Produto:</h5>
                            <p><?php echo nl2br($product['details']); ?></p>
                        </div>
                    <?php endif; ?>

                    <div class="d-grid gap-2">
                        <?php if ($product['stock'] > 0): ?>
                            <button class="btn btn-whatsapp" onclick="buyOnWhatsApp()">
                                <i class="bi bi-whatsapp"></i> Comprar pelo WhatsApp
                            </button>
                            <button class="btn btn-primary" onclick="addToCart(<?php echo $product['id']; ?>)">
                                <i class="bi bi-cart-plus"></i> Adicionar ao Carrinho
                            </button>
                        <?php else: ?>
                            <button class="btn btn-secondary" disabled>
                                Produto Indisponível
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function addToCart(productId) {
            // Implementar lógica do carrinho
            alert('Produto adicionado ao carrinho!');
        }

        function buyOnWhatsApp() {
            const productName = '<?php echo addslashes($product['name']); ?>';
            const productPrice = '<?php echo number_format($product['price'], 2, ',', '.'); ?>';
            const productImage = '<?php echo $product['image_url']; ?>';
            const whatsappNumber = '<?php echo $settings['whatsapp_number']; ?>';
            
            const message = `Olá! Gostaria de comprar o produto:\n\n` +
                          `*${productName}*\n` +
                          `Preço: R$ ${productPrice}\n` +
                          `Link da imagem: ${productImage}`;
            
            const whatsappUrl = `https://wa.me/${whatsappNumber}?text=${encodeURIComponent(message)}`;
            window.open(whatsappUrl, '_blank');
        }
    </script>
</body>
</html> 