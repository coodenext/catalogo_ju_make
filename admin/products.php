<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/auth.php';

// Verifica se está logado
requireLogin();

// Processa ações
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;
$message = '';
$error = '';

switch ($action) {
    case 'add':
    case 'edit':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
            $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
            $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
            $stock = filter_input(INPUT_POST, 'stock', FILTER_VALIDATE_INT);
            $image_url = filter_input(INPUT_POST, 'image_url', FILTER_VALIDATE_URL);
            
            if (!$name) {
                $error = 'Nome do produto é obrigatório';
            } elseif (!$price || $price <= 0) {
                $error = 'Preço inválido';
            } elseif (!$stock || $stock < 0) {
                $error = 'Estoque inválido';
            } elseif ($image_url && !isValidImageUrl($image_url)) {
                $error = 'URL da imagem inválida';
            } else {
                if ($action === 'add') {
                    $stmt = $pdo->prepare("
                        INSERT INTO products (name, description, price, stock, image_url) 
                        VALUES (?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([$name, $description, $price, $stock, $image_url]);
                    $message = 'Produto adicionado com sucesso!';
                } else {
                    $stmt = $pdo->prepare("
                        UPDATE products 
                        SET name = ?, description = ?, price = ?, stock = ?, image_url = ? 
                        WHERE id = ?
                    ");
                    $stmt->execute([$name, $description, $price, $stock, $image_url, $id]);
                    $message = 'Produto atualizado com sucesso!';
                }
                
                header('Location: products.php?message=' . urlencode($message));
                exit;
            }
        }
        
        if ($action === 'edit' && $id) {
            $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->execute([$id]);
            $product = $stmt->fetch();
            
            if (!$product) {
                header('Location: products.php');
                exit;
            }
        }
        break;
        
    case 'delete':
        if ($id) {
            $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$id]);
            $message = 'Produto excluído com sucesso!';
            
            header('Location: products.php?message=' . urlencode($message));
            exit;
        }
        break;
}

// Busca produtos
$search = $_GET['search'] ?? '';
$page = max(1, $_GET['page'] ?? 1);
$limit = ITEMS_PER_PAGE;
$offset = ($page - 1) * $limit;

$where = '';
$params = [];

if ($search) {
    $where = "WHERE name LIKE ? OR description LIKE ?";
    $params = ["%$search%", "%$search%"];
}

$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM products 
    $where
");
$stmt->execute($params);
$total = $stmt->fetchColumn();
$total_pages = ceil($total / $limit);

$stmt = $pdo->prepare("
    SELECT * 
    FROM products 
    $where 
    ORDER BY name 
    LIMIT ? OFFSET ?
");
$params[] = $limit;
$params[] = $offset;
$stmt->execute($params);
$products = $stmt->fetchAll();

// Mensagem de sucesso
if (isset($_GET['message'])) {
    $message = $_GET['message'];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Produtos - <?php echo SITE_NAME; ?></title>
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
        .table th {
            border-top: none;
            background-color: #f8f9fa;
        }
        .product-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 5px;
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
                            <a class="nav-link" href="index.php">
                                <i class="bi bi-house"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="products.php">
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
                    <h2>Gerenciar Produtos</h2>
                    <a href="products.php?action=add" class="btn btn-primary">
                        <i class="bi bi-plus"></i> Novo Produto
                    </a>
                </div>
                
                <?php if ($message): ?>
                    <div class="alert alert-success"><?php echo $message; ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($action === 'add' || $action === 'edit'): ?>
                    <!-- Formulário de Produto -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><?php echo $action === 'add' ? 'Adicionar' : 'Editar'; ?> Produto</h5>
                        </div>
                        <div class="card-body">
                            <form method="post" action="">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nome</label>
                                    <input type="text" class="form-control" id="name" name="name" required
                                           value="<?php echo $product['name'] ?? ''; ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="description" class="form-label">Descrição</label>
                                    <textarea class="form-control" id="description" name="description" rows="3"><?php echo $product['description'] ?? ''; ?></textarea>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="price" class="form-label">Preço</label>
                                            <div class="input-group">
                                                <span class="input-group-text">R$</span>
                                                <input type="number" class="form-control" id="price" name="price" step="0.01" required
                                                       value="<?php echo $product['price'] ?? ''; ?>">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="stock" class="form-label">Estoque</label>
                                            <input type="number" class="form-control" id="stock" name="stock" required
                                                   value="<?php echo $product['stock'] ?? ''; ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="image_url" class="form-label">URL da Imagem</label>
                                    <input type="url" class="form-control" id="image_url" name="image_url"
                                           value="<?php echo $product['image_url'] ?? ''; ?>">
                                </div>
                                
                                <div class="d-flex justify-content-between">
                                    <a href="products.php" class="btn btn-secondary">Cancelar</a>
                                    <button type="submit" class="btn btn-primary">Salvar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Lista de Produtos -->
                    <div class="card">
                        <div class="card-header">
                            <form method="get" class="d-flex gap-2">
                                <input type="text" class="form-control" name="search" placeholder="Buscar produtos..."
                                       value="<?php echo $search; ?>">
                                <button type="submit" class="btn btn-primary">Buscar</button>
                                <?php if ($search): ?>
                                    <a href="products.php" class="btn btn-secondary">Limpar</a>
                                <?php endif; ?>
                            </form>
                        </div>
                        <div class="card-body">
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
                                    <tbody>
                                        <?php foreach ($products as $product): ?>
                                            <tr>
                                                <td>
                                                    <?php if ($product['image_url']): ?>
                                                        <img src="<?php echo $product['image_url']; ?>" 
                                                             alt="<?php echo $product['name']; ?>"
                                                             class="product-image">
                                                    <?php else: ?>
                                                        <div class="product-image bg-light d-flex align-items-center justify-content-center">
                                                            <i class="bi bi-image text-muted"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo $product['name']; ?></td>
                                                <td>R$ <?php echo number_format($product['price'], 2, ',', '.'); ?></td>
                                                <td>
                                                    <?php if ($product['stock'] <= 5): ?>
                                                        <span class="badge bg-danger"><?php echo $product['stock']; ?></span>
                                                    <?php else: ?>
                                                        <?php echo $product['stock']; ?>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a href="products.php?action=edit&id=<?php echo $product['id']; ?>" 
                                                           class="btn btn-sm btn-primary">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                        <a href="products.php?action=delete&id=<?php echo $product['id']; ?>" 
                                                           class="btn btn-sm btn-danger"
                                                           onclick="return confirm('Tem certeza que deseja excluir este produto?')">
                                                            <i class="bi bi-trash"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <?php if ($total_pages > 1): ?>
                                <nav class="mt-4">
                                    <ul class="pagination justify-content-center">
                                        <?php if ($page > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>">
                                                    Anterior
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                        
                                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                                <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>">
                                                    <?php echo $i; ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>
                                        
                                        <?php if ($page < $total_pages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>">
                                                    Próxima
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 