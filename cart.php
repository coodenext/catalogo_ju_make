<?php
require_once 'includes/config.php';

// Buscar configurações
$stmt = $pdo->query("SELECT * FROM settings");
$settings = [];
while ($row = $stmt->fetch()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// Inicializar carrinho se não existir
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Processar ações do carrinho
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                if (isset($_POST['product_id']) && isset($_POST['quantity'])) {
                    $product_id = intval($_POST['product_id']);
                    $quantity = intval($_POST['quantity']);
                    
                    // Verificar se o produto existe e tem estoque
                    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND stock >= ?");
                    $stmt->execute([$product_id, $quantity]);
                    $product = $stmt->fetch();
                    
                    if ($product) {
                        if (isset($_SESSION['cart'][$product_id])) {
                            $_SESSION['cart'][$product_id]['quantity'] += $quantity;
                        } else {
                            $_SESSION['cart'][$product_id] = [
                                'quantity' => $quantity,
                                'name' => $product['name'],
                                'price' => $product['price'],
                                'image' => $product['image_url']
                            ];
                        }
                    }
                }
                break;

            case 'update':
                if (isset($_POST['product_id']) && isset($_POST['quantity'])) {
                    $product_id = intval($_POST['product_id']);
                    $quantity = intval($_POST['quantity']);
                    
                    if ($quantity > 0) {
                        $_SESSION['cart'][$product_id]['quantity'] = $quantity;
                    } else {
                        unset($_SESSION['cart'][$product_id]);
                    }
                }
                break;

            case 'remove':
                if (isset($_POST['product_id'])) {
                    $product_id = intval($_POST['product_id']);
                    unset($_SESSION['cart'][$product_id]);
                }
                break;

            case 'clear':
                $_SESSION['cart'] = [];
                break;
        }
    }
}

// Calcular total do carrinho
$total = 0;
foreach ($_SESSION['cart'] as $item) {
    $total += $item['price'] * $item['quantity'];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrinho - <?php echo $settings['site_name'] ?? 'JU Make'; ?></title>
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

        .cart-container {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .cart-item {
            border-bottom: 1px solid #eee;
            padding: 1rem 0;
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .cart-item img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 5px;
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

        .cart-summary {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1.5rem;
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
                        <a class="nav-link active" href="cart.php">
                            <i class="bi bi-cart"></i> Carrinho
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="cart-container">
            <h2 class="mb-4">Carrinho de Compras</h2>

            <?php if (empty($_SESSION['cart'])): ?>
                <div class="text-center py-5">
                    <i class="bi bi-cart-x" style="font-size: 4rem; color: #ccc;"></i>
                    <h4 class="mt-3">Seu carrinho está vazio</h4>
                    <p class="text-muted">Adicione produtos ao carrinho para continuar comprando.</p>
                    <a href="index.php" class="btn btn-primary mt-3">Continuar Comprando</a>
                </div>
            <?php else: ?>
                <div class="row">
                    <div class="col-md-8">
                        <?php foreach ($_SESSION['cart'] as $product_id => $item): ?>
                            <div class="cart-item">
                                <div class="row align-items-center">
                                    <div class="col-md-2">
                                        <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <h5><?php echo $item['name']; ?></h5>
                                        <p class="text-muted mb-0">R$ <?php echo number_format($item['price'], 2, ',', '.'); ?></p>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="input-group">
                                            <button class="btn btn-outline-secondary" type="button" onclick="updateQuantity(<?php echo $product_id; ?>, -1)">-</button>
                                            <input type="number" class="form-control text-center" value="<?php echo $item['quantity']; ?>" 
                                                   min="1" onchange="updateQuantity(<?php echo $product_id; ?>, this.value)">
                                            <button class="btn btn-outline-secondary" type="button" onclick="updateQuantity(<?php echo $product_id; ?>, 1)">+</button>
                                        </div>
                                    </div>
                                    <div class="col-md-2 text-end">
                                        <p class="mb-0">R$ <?php echo number_format($item['price'] * $item['quantity'], 2, ',', '.'); ?></p>
                                        <button class="btn btn-link text-danger p-0" onclick="removeItem(<?php echo $product_id; ?>)">
                                            <i class="bi bi-trash"></i> Remover
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <div class="mt-3">
                            <button class="btn btn-outline-danger" onclick="clearCart()">
                                <i class="bi bi-trash"></i> Limpar Carrinho
                            </button>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="cart-summary">
                            <h4>Resumo do Pedido</h4>
                            <hr>
                            <div class="d-flex justify-content-between mb-3">
                                <span>Subtotal:</span>
                                <span>R$ <?php echo number_format($total, 2, ',', '.'); ?></span>
                            </div>
                            <div class="d-grid">
                                <button class="btn btn-whatsapp" onclick="finishOrder()">
                                    <i class="bi bi-whatsapp"></i> Finalizar Pedido no WhatsApp
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function updateQuantity(productId, change) {
            const input = event.target.parentElement.querySelector('input');
            let newQuantity;
            
            if (typeof change === 'number') {
                newQuantity = parseInt(input.value) + change;
            } else {
                newQuantity = parseInt(change);
            }
            
            if (newQuantity > 0) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="product_id" value="${productId}">
                    <input type="hidden" name="quantity" value="${newQuantity}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function removeItem(productId) {
            if (confirm('Tem certeza que deseja remover este item do carrinho?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="remove">
                    <input type="hidden" name="product_id" value="${productId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function clearCart() {
            if (confirm('Tem certeza que deseja limpar o carrinho?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="clear">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function finishOrder() {
            const whatsappNumber = '<?php echo $settings['whatsapp_number']; ?>';
            let message = 'Olá! Quero finalizar a compra com os seguintes itens:\n\n';
            
            <?php foreach ($_SESSION['cart'] as $product_id => $item): ?>
                message += `- ${<?php echo json_encode($item['name']); ?>} - R$ ${<?php echo number_format($item['price'], 2, ',', '.'); ?>} - ${<?php echo $item['quantity']; ?>} unidade(s)\n`;
            <?php endforeach; ?>
            
            message += `\nTotal: R$ ${<?php echo number_format($total, 2, ',', '.'); ?>}`;
            
            const whatsappUrl = `https://wa.me/${whatsappNumber}?text=${encodeURIComponent(message)}`;
            window.open(whatsappUrl, '_blank');
        }
    </script>
</body>
</html> 