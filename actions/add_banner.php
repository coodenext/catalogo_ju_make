<?php
// actions/add_banner.php

session_start();
require_once '../config.php'; // Ajuste o caminho conforme a estrutura da sua pasta

// Verifica se o usuário está logado
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    $_SESSION['form_message'] = 'error:Acesso negado. Por favor, faça login.';
    header('Location: ../admin.php');
    exit();
}

// Verifica se a requisição é POST e se um arquivo foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['bannerImage'])) {
    $uploadDir = '../uploads/banners/'; // Diretório para salvar as imagens dos banners
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif']; // Tipos de arquivo permitidos
    $maxFileSize = 5 * 1024 * 1024; // 5 MB (em bytes)

    $fileName = $_FILES['bannerImage']['name'];
    $fileTmpName = $_FILES['bannerImage']['tmp_name'];
    $fileSize = $_FILES['bannerImage']['size'];
    $fileError = $_FILES['bannerImage']['error'];
    $fileType = $_FILES['bannerImage']['type'];

    // Validação do upload
    if ($fileError !== 0) {
        $_SESSION['form_message'] = 'error:Ocorreu um erro no upload do arquivo.';
        header('Location: ../admin.php');
        exit();
    }

    if (!in_array($fileType, $allowedTypes)) {
        $_SESSION['form_message'] = 'error:Tipo de arquivo não permitido. Apenas JPG, PNG ou GIF.';
        header('Location: ../admin.php');
        exit();
    }

    if ($fileSize > $maxFileSize) {
        $_SESSION['form_message'] = 'error:O arquivo é muito grande. Tamanho máximo: 5MB.';
        header('Location: ../admin.php');
        exit();
    }

    // Gera um nome único para o arquivo para evitar sobrescrever
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $newFileName = uniqid('banner_', true) . '.' . $fileExt;
    $filePath = $uploadDir . $newFileName;

    // Tenta mover o arquivo para o diretório de uploads
    if (move_uploaded_file($fileTmpName, $filePath)) {
        try {
            // Insere o nome do arquivo no banco de dados
            $stmt = $pdo->prepare("INSERT INTO banners (imagem) VALUES (?)");
            $stmt->execute([$newFileName]);

            $_SESSION['form_message'] = 'success:Banner adicionado com sucesso!';
        } catch (PDOException $e) {
            // Se houver um erro no BD, tenta remover o arquivo uploaded
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            $_SESSION['form_message'] = 'error:Erro ao salvar banner no banco de dados: ' . $e->getMessage();
        }
    } else {
        $_SESSION['form_message'] = 'error:Erro ao mover o arquivo para o diretório de uploads. Verifique as permissões da pasta.';
    }
} else {
    $_SESSION['form_message'] = 'error:Requisição inválida ou nenhum arquivo enviado.';
}

// Redireciona de volta para o painel administrativo
header('Location: ../admin.php');
exit();
?>