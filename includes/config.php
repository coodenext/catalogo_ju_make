<?php
session_start();

// Configurações do banco de dados
define('DB_HOST', 'localhost');
define('DB_NAME', 'catalogo_ju_make');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}

// Inclui arquivo de funções
require_once __DIR__ . '/functions.php';

// Configurações gerais
define('SITE_NAME', 'Catálogo Ju Make');
define('SITE_URL', 'http://localhost/catalogo_ju_make');
define('ADMIN_EMAIL', 'admin@example.com');

// Configurações de upload
define('UPLOAD_DIR', __DIR__ . '/../uploads');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif']);

// Configurações de segurança
define('HASH_COST', 12);
define('SESSION_LIFETIME', 3600); // 1 hora

// Configurações de paginação
define('ITEMS_PER_PAGE', 12);

// Configurações de cache
define('CACHE_ENABLED', true);
define('CACHE_LIFETIME', 3600); // 1 hora

// Configurações de debug
define('DEBUG_MODE', true);

// Configurações de timezone
date_default_timezone_set('America/Sao_Paulo');

// Configurações de locale
setlocale(LC_ALL, 'pt_BR.utf-8', 'pt_BR', 'Portuguese_Brazil');

// Configurações de erro
if (DEBUG_MODE) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}

// Configurações de sessão
ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
ini_set('session.cookie_lifetime', SESSION_LIFETIME);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
ini_set('session.cookie_httponly', true);
ini_set('session.use_only_cookies', true);
ini_set('session.cookie_samesite', 'Lax');

// Configurações de upload
ini_set('upload_max_filesize', '5M');
ini_set('post_max_size', '5M');
ini_set('max_file_uploads', 20);

// Configurações de memória
ini_set('memory_limit', '256M');

// Configurações de tempo de execução
set_time_limit(300); // 5 minutos

// Configurações de cache
if (CACHE_ENABLED) {
    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }
}

// Configurações de segurança
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Content-Security-Policy: default-src \'self\'; script-src \'self\' \'unsafe-inline\' \'unsafe-eval\'; style-src \'self\' \'unsafe-inline\'; img-src \'self\' data: https:; font-src \'self\' data:; connect-src \'self\';');

// Funções de Utilidade
function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

function redirect($url) {
    header("Location: " . $url);
    exit;
} 