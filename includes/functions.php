<?php
// Função para formatar preço
function formatPrice($price) {
    return 'R$ ' . number_format($price, 2, ',', '.');
}

// Função para gerar slug
function generateSlug($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return $text;
}

// Função para validar URL de imagem
function isValidImageUrl($url) {
    $headers = @get_headers($url);
    if ($headers) {
        $content_type = '';
        foreach ($headers as $header) {
            if (strpos($header, 'Content-Type:') !== false) {
                $content_type = $header;
                break;
            }
        }
        return strpos($content_type, 'image/') !== false;
    }
    return false;
}

// Função para validar número do WhatsApp
function isValidWhatsAppNumber($number) {
    return preg_match('/^[0-9]{10,13}$/', $number);
}

// Função para gerar mensagem do WhatsApp
function generateWhatsAppMessage($items, $total) {
    $message = "Olá! Quero finalizar a compra com os seguintes itens:\n\n";
    
    foreach ($items as $item) {
        $message .= "- {$item['name']} - R$ " . number_format($item['price'], 2, ',', '.') . 
                   " - {$item['quantity']} unidade(s)\n";
    }
    
    $message .= "\nTotal: R$ " . number_format($total, 2, ',', '.');
    
    return $message;
}

// Função para verificar se um produto está em estoque
function isProductInStock($product_id, $quantity = 1) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    
    return $product && $product['stock'] >= $quantity;
}

// Função para atualizar estoque
function updateStock($product_id, $quantity) {
    global $pdo;
    
    $stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
    return $stmt->execute([$quantity, $product_id]);
}

// Função para buscar configurações
function getSettings() {
    global $pdo;
    
    $stmt = $pdo->query("SELECT * FROM settings");
    $settings = [];
    while ($row = $stmt->fetch()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    
    return $settings;
}

// Função para buscar banners ativos
function getActiveBanners() {
    global $pdo;
    
    $stmt = $pdo->query("SELECT * FROM banners WHERE active = 1 ORDER BY created_at DESC");
    return $stmt->fetchAll();
}

// Função para buscar produtos
function getProducts($search = '', $limit = null) {
    global $pdo;
    
    $where = '';
    $params = [];
    
    if (!empty($search)) {
        $where = "WHERE name LIKE ?";
        $params[] = "%$search%";
    }
    
    $sql = "SELECT * FROM products $where ORDER BY name ASC";
    if ($limit) {
        $sql .= " LIMIT ?";
        $params[] = $limit;
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// Função para buscar produto por ID
function getProductById($id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// Função para verificar se o usuário está logado
function isLoggedIn() {
    return isset($_SESSION['admin_id']);
}

// Função para verificar se o usuário é admin
function isAdmin() {
    return isLoggedIn();
}

// Função para redirecionar se não estiver logado
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

// Função para redirecionar se não for admin
function requireAdmin() {
    if (!isAdmin()) {
        header('Location: login.php');
        exit;
    }
} 