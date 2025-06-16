<?php
$page_title = 'Configurações';
require_once 'header.php';

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'update_settings') {
        $settings = [
            'site_name' => sanitize($_POST['site_name']),
            'site_description' => sanitize($_POST['site_description']),
            'whatsapp_number' => sanitize($_POST['whatsapp_number']),
            'site_logo' => sanitize($_POST['site_logo'])
        ];

        foreach ($settings as $key => $value) {
            $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
            $stmt->execute([$value, $key]);
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
                <form method="POST" action="">
                    <input type="hidden" name="action" value="update_settings">

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
                        <div class="input-group">
                            <span class="input-group-text">+</span>
                            <input type="text" class="form-control" id="whatsapp_number" name="whatsapp_number" required
                                   value="<?php echo $settings['whatsapp_number'] ?? ''; ?>"
                                   placeholder="Ex: 5511999999999">
                        </div>
                        <small class="text-muted">Digite o número com código do país e DDD, sem espaços ou caracteres especiais.</small>
                    </div>

                    <div class="mb-3">
                        <label for="site_logo" class="form-label">URL da Logo</label>
                        <input type="url" class="form-control" id="site_logo" name="site_logo"
                               value="<?php echo $settings['site_logo'] ?? ''; ?>">
                        <?php if (!empty($settings['site_logo'])): ?>
                            <div class="mt-2">
                                <img src="<?php echo $settings['site_logo']; ?>" alt="Logo" style="max-height: 100px;">
                            </div>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="btn btn-primary">Salvar Configurações</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?> 