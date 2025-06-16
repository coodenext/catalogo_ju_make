<?php
$page_title = 'Gerenciar Produtos';
require_once 'header.php';

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
            case 'edit':
                $name = sanitize($_POST['name']);
                $description = sanitize($_POST['description']);
                $price = (float)$_POST['price'];
                $stock = (int)$_POST['stock'];
                $active = isset($_POST['active']) ? 1 : 0;

                // Processar upload de imagem
                $image_url = '';
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $upload_dir = __DIR__ . '/../uploads/products/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }

                    $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

                    if (in_array($file_extension, $allowed_extensions)) {
                        $new_filename = uniqid() . '.' . $file_extension;
                        $upload_path = $upload_dir . $new_filename;

                        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                            $image_url = 'uploads/products/' . $new_filename;
                        }
                    }
                } elseif (isset($_POST['image_url'])) {
                    $image_url = sanitize($_POST['image_url']);
                }

                if (empty($image_url)) {
                    echo '<div class="alert alert-danger">A imagem é obrigatória.</div>';
                } else {
                    if ($_POST['action'] === 'add') {
                        $stmt = $pdo->prepare("INSERT INTO products (name, description, price, stock, image_url, active) VALUES (?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$name, $description, $price, $stock, $image_url, $active]);
                        echo '<div class="alert alert-success">Produto adicionado com sucesso!</div>';
                    } else {
                        $stmt = $pdo->prepare("UPDATE products SET name = ?, description = ?, price = ?, stock = ?, image_url = ?, active = ? WHERE id = ?");
                        $stmt->execute([$name, $description, $price, $stock, $image_url, $active, $_POST['id']]);
                        echo '<div class="alert alert-success">Produto atualizado com sucesso!</div>';
                    }
                }
                break;

            case 'delete':
                if (isset($_POST['id'])) {
                    // Buscar o produto para excluir a imagem
                    $stmt = $pdo->prepare("SELECT image_url FROM products WHERE id = ?");
                    $stmt->execute([$_POST['id']]);
                    $product = $stmt->fetch();

                    if ($product && file_exists(__DIR__ . '/../' . $product['image_url'])) {
                        unlink(__DIR__ . '/../' . $product['image_url']);
                    }

                    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
                    $stmt->execute([$_POST['id']]);
                    echo '<div class="alert alert-success">Produto excluído com sucesso!</div>';
                }
                break;
        }
    }
}

// Buscar produto para edição
$product = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $product = $stmt->fetch();
}

// Buscar todos os produtos
$stmt = $pdo->query("SELECT * FROM products ORDER BY name ASC");
$products = $stmt->fetchAll();
?>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <?php echo $product ? 'Editar Produto' : 'Adicionar Novo Produto'; ?>
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="<?php echo $product ? 'edit' : 'add'; ?>">
                    <?php if ($product): ?>
                        <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="name" class="form-label">Nome do Produto *</label>
                        <input type="text" class="form-control" id="name" name="name" required
                               value="<?php echo $product ? $product['name'] : ''; ?>">
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Descrição</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?php echo $product ? $product['description'] : ''; ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="price" class="form-label">Preço *</label>
                        <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" required
                               value="<?php echo $product ? $product['price'] : ''; ?>">
                    </div>

                    <div class="mb-3">
                        <label for="stock" class="form-label">Estoque *</label>
                        <input type="number" class="form-control" id="stock" name="stock" min="0" required
                               value="<?php echo $product ? $product['stock'] : ''; ?>">
                    </div>

                    <div class="mb-3">
                        <label for="image" class="form-label">Imagem do Produto *</label>
                        <?php if ($product && $product['image_url']): ?>
                            <div class="mb-2">
                                <img src="<?php echo $product['image_url']; ?>" alt="Produto atual" class="img-thumbnail" style="max-height: 200px;">
                            </div>
                        <?php endif; ?>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*" <?php echo $product ? '' : 'required'; ?>>
                        <small class="text-muted">Formatos aceitos: JPG, JPEG, PNG, GIF. Tamanho máximo: 5MB</small>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="active" name="active"
                                   <?php echo ($product && $product['active']) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="active">Produto Ativo</label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <?php echo $product ? 'Atualizar Produto' : 'Adicionar Produto'; ?>
                    </button>
                    
                    <?php if ($product): ?>
                        <a href="products.php" class="btn btn-secondary">Cancelar</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Lista de Produtos</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach ($products as $p): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card">
                                <img src="<?php echo $p['image_url']; ?>" class="card-img-top" alt="<?php echo $p['name']; ?>"
                                     style="height: 200px; object-fit: cover;">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo $p['name']; ?></h5>
                                    <p class="card-text"><?php echo $p['description']; ?></p>
                                    <p class="card-text">
                                        <strong>Preço:</strong> R$ <?php echo number_format($p['price'], 2, ',', '.'); ?><br>
                                        <strong>Estoque:</strong> <?php echo $p['stock']; ?> unidades
                                    </p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge <?php echo $p['active'] ? 'bg-success' : 'bg-danger'; ?>">
                                            <?php echo $p['active'] ? 'Ativo' : 'Inativo'; ?>
                                        </span>
                                        <div>
                                            <a href="?action=edit&id=<?php echo $p['id']; ?>" class="btn btn-sm btn-primary">
                                                <i class="bi bi-pencil"></i> Editar
                                            </a>
                                            <form method="POST" action="" class="d-inline" onsubmit="return confirm('Tem certeza que deseja excluir este produto?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="bi bi-trash"></i> Excluir
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?> 