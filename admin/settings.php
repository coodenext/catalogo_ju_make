<?php
$page_title = 'Configurações';
require_once 'header.php';

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'update_settings') {
        // Processar upload da logomarca
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/../uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $file_extension = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

            if (in_array($file_extension, $allowed_extensions)) {
                $new_filename = 'logo.' . $file_extension;
                $upload_path = $upload_dir . $new_filename;

                if (move_uploaded_file($_FILES['logo']['tmp_name'], $upload_path)) {
                    $logo_url = 'uploads/' . $new_filename;
                    
                    // Atualizar a configuração da logomarca
                    $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('logo_url', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
                    $stmt->execute([$logo_url, $logo_url]);
                }
            }
        }

        // Atualizar outras configurações
        $settings = [
            'site_name' => sanitize($_POST['site_name']),
            'site_description' => sanitize($_POST['site_description']),
            'whatsapp_number' => sanitize($_POST['whatsapp_number']),
            'instagram_url' => sanitize($_POST['instagram_url']),
            'facebook_url' => sanitize($_POST['facebook_url'])
        ];

        foreach ($settings as $key => $value) {
            $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
            $stmt->execute([$key, $value, $value]);
        }

        echo '<div class="alert alert-success">Configurações atualizadas com sucesso!</div>';
    }
}

// Buscar configurações atuais
$stmt = $pdo->query("SELECT * FROM settings");
$settings = [];
while ($row = $stmt->fetch()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Configurações do Site</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="update_settings">

                    <div class="mb-3">
                        <label for="logo" class="form-label">Logomarca</label>
                        <?php if (isset($settings['logo_url'])): ?>
                            <div class="mb-2">
                                <img src="<?php echo $settings['logo_url']; ?>" alt="Logomarca atual" class="img-thumbnail" style="max-height: 100px;">
                            </div>
                        <?php endif; ?>
                        <input type="file" class="form-control" id="logo" name="logo" accept="image/*">
                        <small class="text-muted">Formatos aceitos: JPG, JPEG, PNG, GIF. Tamanho máximo: 5MB</small>
                    </div>

                    <div class="mb-3">
                        <label for="site_name" class="form-label">Nome do Site</label>
                        <input type="text" class="form-control" id="site_name" name="site_name" required
                               value="<?php echo $settings['site_name'] ?? ''; ?>">
                    </div>

                    <div class="mb-3">
                        <label for="site_description" class="form-label">Descrição do Site</label>
                        <textarea class="form-control" id="site_description" name="site_description" rows="3"><?php echo $settings['site_description'] ?? ''; ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="whatsapp_number" class="form-label">Número do WhatsApp</label>
                        <input type="text" class="form-control" id="whatsapp_number" name="whatsapp_number"
                               value="<?php echo $settings['whatsapp_number'] ?? ''; ?>"
                               placeholder="Ex: 5511999999999">
                    </div>

                    <div class="mb-3">
                        <label for="instagram_url" class="form-label">URL do Instagram</label>
                        <input type="url" class="form-control" id="instagram_url" name="instagram_url"
                               value="<?php echo $settings['instagram_url'] ?? ''; ?>">
                    </div>

                    <div class="mb-3">
                        <label for="facebook_url" class="form-label">URL do Facebook</label>
                        <input type="url" class="form-control" id="facebook_url" name="facebook_url"
                               value="<?php echo $settings['facebook_url'] ?? ''; ?>">
                    </div>

                    <button type="submit" class="btn btn-primary">Salvar Configurações</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?> 