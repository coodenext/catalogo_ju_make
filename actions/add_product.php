<?php
// actions/add_product.php

session_start();
require_once '../config.php'; // Inclui o arquivo de conexão com o banco de dados

// Verifica se o usuário está logado
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // Se não estiver logado, redireciona para a página de login com uma mensagem de erro
    header('Location: ../admin.php?error=Acesso não autorizado. Faça login.');
    exit();
}

// Verifica se a requisição é POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Coleta e sanitiza os dados do formulário
    $nome = trim($_POST['name'] ?? '');
    $preco = filter_var($_POST['price'] ?? 0, FILTER_VALIDATE_FLOAT);
    $estoque = filter_var($_POST['stock'] ?? 0, FILTER_VALIDATE_INT);
    $descricao = trim($_POST['description'] ?? '');

    $upload_dir = '../uploads/produtos/'; // Diretório onde as imagens serão salvas
    $imagem_nome = null; // Inicializa o nome da imagem como nulo

    // Validação básica dos dados
    if (empty($nome) || $preco === false || $estoque === false || $preco < 0 || $estoque < 0) {
        $_SESSION['form_message'] = 'error:Por favor, preencha todos os campos obrigatórios corretamente.';
        header('Location: ../admin.php');
        exit();
    }

    // --- Lógica de Upload da Imagem ---
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['image']['tmp_name'];
        $file_name = $_FILES['image']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];

        // Gera um nome único para o arquivo para evitar colisões
        $imagem_nome = uniqid('prod_', true) . '.' . $file_ext;
        $destination = $upload_dir . $imagem_nome;

        // Cria o diretório de upload se não existir
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true); // Permissões 0777 para teste, ajuste para mais seguro em produção
        }

        // Verifica a extensão e move o arquivo
        if (in_array($file_ext, $allowed_ext)) {
            if (!move_uploaded_file($file_tmp, $destination)) {
                $_SESSION['form_message'] = 'error:Falha ao mover a imagem do produto.';
                header('Location: ../admin.php');
                exit();
            }
        } else {
            $_SESSION['form_message'] = 'error:Formato de imagem não permitido. Use JPG, JPEG, PNG ou GIF.';
            header('Location: ../admin.php');
            exit();
        }
    } else if ($_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        // Se houver um erro no upload que não seja "nenhum arquivo selecionado"
        $_SESSION['form_message'] = 'error:Erro no upload da imagem: ' . $_FILES['image']['error'];
        header('Location: ../admin.php');
        exit();
    }


    // --- Inserção dos dados no banco de dados ---
    try {
        $stmt = $pdo->prepare("INSERT INTO produtos (nome, preco, estoque, descricao, imagem) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$nome, $preco, $estoque, $descricao, $imagem_nome]);

        $_SESSION['form_message'] = 'success:Produto adicionado com sucesso!';
        header('Location: ../admin.php');
        exit();

    } catch (PDOException $e) {
        // Se houver um erro no banco de dados, defina a mensagem de erro
        $_SESSION['form_message'] = 'error:Erro ao adicionar o produto: ' . $e->getMessage();
        // Opcional: Remover imagem se a inserção no banco falhar
        if ($imagem_nome && file_exists($destination)) {
            unlink($destination);
        }
        header('Location: ../admin.php');
        exit();
    }

} else {
    // Se não for uma requisição POST, redireciona de volta
    header('Location: ../admin.php');
    exit();
}
?>