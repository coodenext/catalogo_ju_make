<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/auth.php';

// Se já estiver logado, redireciona para o painel
if ($auth->isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';

// Processa o formulário de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';
    
    if (!$email) {
        $error = 'E-mail inválido';
    } elseif (empty($password)) {
        $error = 'Senha não informada';
    } else {
        if ($auth->login($email, $password)) {
            header('Location: index.php');
            exit;
        } else {
            $error = 'E-mail ou senha inválidos';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Painel Administrativo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .login-container {
            max-width: 400px;
            margin: 100px auto;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .card-header {
            background-color: #fff;
            border-bottom: none;
            text-align: center;
            padding: 20px;
        }
        .card-body {
            padding: 30px;
        }
        .btn-primary {
            background-color: #0d6efd;
            border: none;
            padding: 10px;
        }
        .btn-primary:hover {
            background-color: #0b5ed7;
        }
        .form-control {
            padding: 10px;
        }
        .alert {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Painel Administrativo</h4>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form method="post" action="">
                        <div class="mb-3">
                            <label for="email" class="form-label">E-mail</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Senha</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">Entrar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 