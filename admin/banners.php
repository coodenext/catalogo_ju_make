<?php
$page_title = 'Gerenciar Banners';
require_once 'header.php';

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
            case 'edit':
                $title = sanitize($_POST['title']);
                $text = sanitize($_POST['text']);
                $active = isset($_POST['active']) ? 1 : 0;
                $order = (int)$_POST['order'];

                // Processar upload de imagem
                $image_url = '';
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $upload_dir = __DIR__ . '/../uploads/banners/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }

                    $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

                    if (in_array($file_extension, $allowed_extensions)) {
                        $new_filename = uniqid() . '.' . $file_extension;
                        $upload_path = $upload_dir . $new_filename;

                        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                            $image_url = 'uploads/banners/' . $new_filename;
                        } else {
                            echo '<div class="alert alert-danger">Erro ao fazer upload da imagem.</div>';
                            break;
                        }
                    } else {
                        echo '<div class="alert alert-danger">Formato de arquivo não permitido. Use apenas JPG, JPEG, PNG ou GIF.</div>';
                        break;
                    }
                } elseif (isset($_POST['image_url'])) {
                    $image_url = sanitize($_POST['image_url']);
                }

                if (empty($image_url)) {
                    echo '<div class="alert alert-danger">A imagem é obrigatória.</div>';
                } else {
                    try {
                        if ($_POST['action'] === 'add') {
                            $stmt = $pdo->prepare("INSERT INTO banners (title, image_url, text, active, display_order) VALUES (?, ?, ?, ?, ?)");
                            $stmt->execute([$title, $image_url, $text, $active, $order]);
                            echo '<div class="alert alert-success">Banner adicionado com sucesso!</div>';
                        } else {
                            // Se estiver editando, excluir a imagem antiga se houver uma nova
                            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                                $stmt = $pdo->prepare("SELECT image_url FROM banners WHERE id = ?");
                                $stmt->execute([$_POST['id']]);
                                $old_banner = $stmt->fetch();
                                
                                if ($old_banner && file_exists(__DIR__ . '/../' . $old_banner['image_url'])) {
                                    unlink(__DIR__ . '/../' . $old_banner['image_url']);
                                }
                            }

                            $stmt = $pdo->prepare("UPDATE banners SET title = ?, image_url = ?, text = ?, active = ?, display_order = ? WHERE id = ?");
                            $stmt->execute([$title, $image_url, $text, $active, $order, $_POST['id']]);
                            echo '<div class="alert alert-success">Banner atualizado com sucesso!</div>';
                        }
                    } catch (PDOException $e) {
                        echo '<div class="alert alert-danger">Erro ao salvar o banner: ' . $e->getMessage() . '</div>';
                    }
                }
                break;

            case 'delete':
                if (isset($_POST['id'])) {
                    try {
                        // Buscar o banner para excluir a imagem
                        $stmt = $pdo->prepare("SELECT image_url FROM banners WHERE id = ?");
                        $stmt->execute([$_POST['id']]);
                        $banner = $stmt->fetch();

                        if ($banner && file_exists(__DIR__ . '/../' . $banner['image_url'])) {
                            unlink(__DIR__ . '/../' . $banner['image_url']);
                        }

                        $stmt = $pdo->prepare("DELETE FROM banners WHERE id = ?");
                        $stmt->execute([$_POST['id']]);
                        echo '<div class="alert alert-success">Banner excluído com sucesso!</div>';
                    } catch (PDOException $e) {
                        echo '<div class="alert alert-danger">Erro ao excluir o banner: ' . $e->getMessage() . '</div>';
                    }
                }
                break;
        }
    }
}

// Buscar banner para edição
$banner = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM banners WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $banner = $stmt->fetch();
}

// Buscar todos os banners
$stmt = $pdo->query("SELECT * FROM banners ORDER BY display_order ASC, created_at DESC");
$banners = $stmt->fetchAll();
?>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <?php echo $banner ? 'Editar Banner' : 'Adicionar Novo Banner'; ?>
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="<?php echo $banner ? 'edit' : 'add'; ?>">
                    <?php if ($banner): ?>
                        <input type="hidden" name="id" value="<?php echo $banner['id']; ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="title" class="form-label">Título do Banner *</label>
                        <input type="text" class="form-control" id="title" name="title" required
                               value="<?php echo $banner ? $banner['title'] : ''; ?>">
                    </div>

                    <div class="mb-3">
                        <label for="image" class="form-label">Imagem do Banner *</label>
                        <?php if ($banner && $banner['image_url']): ?>
                            <div class="mb-2">
                                <img src="<?php echo $banner['image_url']; ?>" alt="Banner atual" class="img-thumbnail" style="max-height: 200px;">
                            </div>
                        <?php endif; ?>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*" <?php echo $banner ? '' : 'required'; ?>>
                        <small class="text-muted">Formatos aceitos: JPG, JPEG, PNG, GIF. Tamanho máximo: 5MB</small>
                    </div>

                    <div class="mb-3">
                        <label for="text" class="form-label">Texto (opcional)</label>
                        <textarea class="form-control" id="text" name="text" rows="3"><?php echo $banner ? $banner['text'] : ''; ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="order" class="form-label">Ordem de Exibição</label>
                        <input type="number" class="form-control" id="order" name="order" min="1" value="<?php echo $banner ? $banner['display_order'] : '1'; ?>">
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="active" name="active"
                                   <?php echo ($banner && $banner['active']) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="active">Banner Ativo</label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <?php echo $banner ? 'Atualizar Banner' : 'Adicionar Banner'; ?>
                    </button>
                    
                    <?php if ($banner): ?>
                        <a href="banners.php" class="btn btn-secondary">Cancelar</a>
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
                <h5 class="card-title mb-0">Lista de Banners</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach ($banners as $b): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card">
                                <img src="<?php echo $b['image_url']; ?>" class="card-img-top" alt="<?php echo $b['title']; ?>"
                                     style="height: 200px; object-fit: cover;">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo $b['title']; ?></h5>
                                    <?php if ($b['text']): ?>
                                        <p class="card-text"><?php echo $b['text']; ?></p>
                                    <?php endif; ?>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge <?php echo $b['active'] ? 'bg-success' : 'bg-danger'; ?>">
                                            <?php echo $b['active'] ? 'Ativo' : 'Inativo'; ?>
                                        </span>
                                        <div>
                                            <a href="?action=edit&id=<?php echo $b['id']; ?>" class="btn btn-sm btn-primary">
                                                <i class="bi bi-pencil"></i> Editar
                                            </a>
                                            <form method="POST" action="" class="d-inline" onsubmit="return confirm('Tem certeza que deseja excluir este banner?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo $b['id']; ?>">
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