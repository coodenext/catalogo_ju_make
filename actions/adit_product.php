<?php
// actions/edit_product.php

session_start();
require_once '../config.php'; // Ajuste o caminho conforme a estrutura da sua pasta

// Verifica se o usuário está logado
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    $_SESSION['form_message'] = 'error:Acesso negado. Por favor, faça login.';
    header('Location: ../admin.php');
    exit();
}

// Verifica se a requisição é POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Coleta os dados do formulário
    $productId = $_POST['productId'] ?? null;
    $name = trim($_POST['name'] ?? '');
    $price = str_replace(',', '.', trim($_POST['price'] ?? '')); // Garante ponto como separador decimal
    $stock = trim($_POST['stock'] ?? '');
    $description = trim($_POST['description'] ?? '');

    // Validação básica dos dados
    if (empty($productId) || !is_numeric($productId) || $productId <= 0) {
        $_SESSION['form_message'] = 'error:ID do produto inválido.';
        header('Location: ../admin.php');
        exit();
    }
    if (empty($name)) {
        $_SESSION['form_message'] = 'error:O nome do produto não pode estar vazio.';
        header('Location: ../admin.php');
        exit();
    }
    if (!is_numeric($price) || $price < 0) {
        $_SESSION['form_message'] = 'error:Preço inválido.';
        header('Location: ../admin.php');
        exit();
    }
    if (!is_numeric($stock) || $stock < 0) {
        $_SESSION['form_message'] = 'error:Estoque inválido.';
        header('Location: ../admin.php');
        exit();
    }

    $imageFileName = null;
    $uploadDir = '../uploads/produtos/'; // Diretório para salvar as imagens dos produtos
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif']; // Tipos de arquivo permitidos
    $maxFileSize = 5 * 1024 * 1024; // 5 MB (em bytes)

    // Processamento do upload da imagem (se uma nova imagem foi enviada)
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $fileName = $_FILES['image']['name'];
        $fileTmpName = $_FILES['image']['tmp_name'];
        $fileSize = $_FILES['image']['size'];
        $fileType = $_FILES['image']['type'];

        if (!in_array($fileType, $allowedTypes)) {
            $_SESSION['form_message'] = 'error:Tipo de arquivo de imagem não permitido. Apenas JPG, PNG ou GIF.';
            header('Location: ../admin.php');
            exit();
        }

        if ($fileSize > $maxFileSize) {
            $_SESSION['form_message'] = 'error:A imagem é muito grande. Tamanho máximo: 5MB.';
            header('Location: ../admin.php');
            exit();
        }

        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $newFileName = uniqid('product_', true) . '.' . $fileExt;
        $filePath = $uploadDir . $newFileName;

        if (move_uploaded_file($fileTmpName, $filePath)) {
            $imageFileName = $newFileName;
        } else {
            $_SESSION['form_message'] = 'error:Erro ao mover a nova imagem para o diretório de uploads. Verifique as permissões da pasta.';
            header('Location: ../admin.php');
            exit();
        }
    }

    try {
        // Primeiro, obtenha o nome da imagem antiga se uma nova foi enviada
        if ($imageFileName !== null) {
            $stmt = $pdo->prepare("SELECT imagem FROM produtos WHERE id = ?");
            $stmt->execute([$productId]);
            $oldImage = $stmt->fetchColumn();
        }

        // Prepara a consulta SQL para atualização
        if ($imageFileName !== null) {
            // Se uma nova imagem foi enviada, atualiza o campo 'imagem'
            $stmt = $pdo->prepare("UPDATE produtos SET nome = ?, preco = ?, estoque = ?, descricao = ?, imagem = ? WHERE id = ?");
            $stmt->execute([$name, $price, $stock, $description, $imageFileName, $productId]);
        } else {
            // Se nenhuma nova imagem foi enviada, mantém a imagem existente
            $stmt = $pdo->prepare("UPDATE produtos SET nome = ?, preco = ?, estoque = ?, descricao = ? WHERE id = ?");
            $stmt->execute([$name, $price, $stock, $description, $productId]);
        }

        // Se uma nova imagem foi uploaded e havia uma imagem antiga, remove a antiga
        if ($imageFileName !== null && !empty($oldImage) && file_exists($uploadDir . $oldImage)) {
            unlink($uploadDir . $oldImage);
        }

        $_SESSION['form_message'] = 'success:Produto atualizado com sucesso!';
    } catch (PDOException $e) {
        // Se houver um erro no BD, e uma nova imagem foi uploaded, remove a nova imagem
        if ($imageFileName !== null && file_exists($filePath)) {
            unlink($filePath);
        }
        $_SESSION['form_message'] = 'error:Erro ao atualizar produto no banco de dados: ' . $e->getMessage();
    }
} else {
    $_SESSION['form_message'] = 'error:Requisição inválida.';
}

header('Location: ../admin.php');
exit();
?>