<?php
$page_title = 'Gerenciar Banners';
require_once 'header.php';

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
            case 'edit':
                $image_url = sanitize($_POST['image_url']);
                $text = sanitize($_POST['text']);
                $active = isset($_POST['active']) ? 1 : 0;

                if (empty($image_url)) {
                    echo '<div class="alert alert-danger">A URL da imagem é obrigatória.</div>';
                } else {
                    if ($_POST['action'] === 'add') {
                        $stmt = $pdo->prepare("INSERT INTO banners (image_url, text, active) VALUES (?, ?, ?)");
                        $stmt->execute([$image_url, $text, $active]);
                        echo '<div class="alert alert-success">Banner adicionado com sucesso!</div>';
                    } else {
                        $stmt = $pdo->prepare("UPDATE banners SET image_url = ?, text = ?, active = ? WHERE id = ?");
                        $stmt->execute([$image_url, $text, $active, $_POST['id']]);
                        echo '<div class="alert alert-success">Banner atualizado com sucesso!</div>';
                    }
                }
                break;

            case 'delete':
                if (isset($_POST['id'])) {
                    $stmt = $pdo->prepare("DELETE FROM banners WHERE id = ?");
                    $stmt->execute([$_POST['id']]);
                    echo '<div class="alert alert-success">Banner excluído com sucesso!</div>';
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
$stmt = $pdo->query("SELECT * FROM banners ORDER BY created_at DESC");
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
                <form method="POST" action="">
                    <input type="hidden" name="action" value="<?php echo $banner ? 'edit' : 'add'; ?>">
                    <?php if ($banner): ?>
                        <input type="hidden" name="id" value="<?php echo $banner['id']; ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="image_url" class="form-label">URL da Imagem *</label>
                        <input type="url" class="form-control" id="image_url" name="image_url" required
                               value="<?php echo $banner ? $banner['image_url'] : ''; ?>">
                    </div>

                    <div class="mb-3">
                        <label for="text" class="form-label">Texto (opcional)</label>
                        <textarea class="form-control" id="text" name="text" rows="3"><?php echo $banner ? $banner['text'] : ''; ?></textarea>
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
                                <img src="<?php echo $b['image_url']; ?>" class="card-img-top" alt="Banner"
                                     style="height: 200px; object-fit: cover;">
                                <div class="card-body">
                                    <?php if ($b['text']): ?>
                                        <p class="card-text"><?php echo $b['text']; ?></p>
                                    <?php endif; ?>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge <?php echo $b['active'] ? 'bg-success' : 'bg-danger'; ?>">
                                            <?php echo $b['active'] ? 'Ativo' : 'Inativo'; ?>
                                        </span>
                                        <div>
                                            <a href="?action=edit&id=<?php echo $b['id']; ?>" class="btn btn-sm btn-primary">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form method="POST" action="" class="d-inline" onsubmit="return confirmDelete()">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo $b['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="bi bi-trash"></i>
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