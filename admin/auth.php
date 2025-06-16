<?php
require_once __DIR__ . '/../includes/config.php';

class Auth {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function login($email, $password) {
        $stmt = $this->pdo->prepare("SELECT * FROM admins WHERE email = ?");
        $stmt->execute([$email]);
        $admin = $stmt->fetch();
        
        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['name'];
            $_SESSION['admin_email'] = $admin['email'];
            $_SESSION['last_activity'] = time();
            
            // Atualiza último login
            $stmt = $this->pdo->prepare("UPDATE admins SET last_login = NOW() WHERE id = ?");
            $stmt->execute([$admin['id']]);
            
            return true;
        }
        
        return false;
    }
    
    public function logout() {
        session_destroy();
        return true;
    }
    
    public function isLoggedIn() {
        if (!isset($_SESSION['admin_id'])) {
            return false;
        }
        
        // Verifica tempo de inatividade
        if (time() - $_SESSION['last_activity'] > SESSION_LIFETIME) {
            $this->logout();
            return false;
        }
        
        // Atualiza tempo de atividade
        $_SESSION['last_activity'] = time();
        
        return true;
    }
    
    public function getCurrentAdmin() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        $stmt = $this->pdo->prepare("SELECT * FROM admins WHERE id = ?");
        $stmt->execute([$_SESSION['admin_id']]);
        return $stmt->fetch();
    }
    
    public function changePassword($current_password, $new_password) {
        if (!$this->isLoggedIn()) {
            return false;
        }
        
        $admin = $this->getCurrentAdmin();
        
        if (!password_verify($current_password, $admin['password'])) {
            return false;
        }
        
        $hash = password_hash($new_password, PASSWORD_BCRYPT, ['cost' => HASH_COST]);
        
        $stmt = $this->pdo->prepare("UPDATE admins SET password = ? WHERE id = ?");
        return $stmt->execute([$hash, $admin['id']]);
    }
    
    public function createAdmin($name, $email, $password) {
        // Verifica se email já existe
        $stmt = $this->pdo->prepare("SELECT id FROM admins WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return false;
        }
        
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => HASH_COST]);
        
        $stmt = $this->pdo->prepare("INSERT INTO admins (name, email, password) VALUES (?, ?, ?)");
        return $stmt->execute([$name, $email, $hash]);
    }
    
    public function updateAdmin($id, $name, $email) {
        // Verifica se email já existe para outro admin
        $stmt = $this->pdo->prepare("SELECT id FROM admins WHERE email = ? AND id != ?");
        $stmt->execute([$email, $id]);
        if ($stmt->fetch()) {
            return false;
        }
        
        $stmt = $this->pdo->prepare("UPDATE admins SET name = ?, email = ? WHERE id = ?");
        return $stmt->execute([$name, $email, $id]);
    }
    
    public function deleteAdmin($id) {
        // Não permite deletar o próprio usuário
        if ($id == $_SESSION['admin_id']) {
            return false;
        }
        
        $stmt = $this->pdo->prepare("DELETE FROM admins WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public function getAllAdmins() {
        $stmt = $this->pdo->query("SELECT id, name, email, last_login FROM admins ORDER BY name");
        return $stmt->fetchAll();
    }
}

// Instancia a classe de autenticação
$auth = new Auth($pdo);

// Função para verificar se está logado
function requireLogin() {
    global $auth;
    if (!$auth->isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

// Função para verificar se é admin
function requireAdmin() {
    global $auth;
    if (!$auth->isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
} 