<?php
// actions/edit_banner.php

session_start();
require_once '../config.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    $_SESSION['form_message'] = 'error:Acesso negado. Por favor, faça login.';
    header('Location: ../admin.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['banner_id'])) {
    $bannerId = intval($_POST['banner_id']);
    $link = trim($_POST['banner_link'] ?? '');
    $imageFileName = null;
    $uploadDir = '../uploads/banners/';
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $maxFileSize = 5 * 1024 * 1024;

    // Processa upload da imagem se enviada
    if (isset($_FILES['bannerImage']) && $_FILES['bannerImage']['error'] === UPLOAD_ERR_OK) {
        $fileName = $_FILES['bannerImage']['name'];
        $fileTmpName = $_FILES['bannerImage']['tmp_name'];
        $fileSize = $_FILES['bannerImage']['size'];
        $fileType = $_FILES['bannerImage']['type'];

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
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $newFileName = uniqid('banner_', true) . '.' . $fileExt;
        $filePath = $uploadDir . $newFileName;
        if (move_uploaded_file($fileTmpName, $filePath)) {
            $imageFileName = $newFileName;
        } else {
            $_SESSION['form_message'] = 'error:Erro ao mover o arquivo para o diretório de uploads.';
            header('Location: ../admin.php');
            exit();
        }
    }

    try {
        // Atualiza banner
        if ($imageFileName) {
            // Remove imagem antiga
            $stmt = $pdo->prepare('SELECT imagem FROM banners WHERE id = ?');
            $stmt->execute([$bannerId]);
            $old = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($old && !empty($old['imagem'])) {
                $oldPath = $uploadDir . $old['imagem'];
                if (file_exists($oldPath)) unlink($oldPath);
            }
            $stmt = $pdo->prepare('UPDATE banners SET imagem = ?, link = ? WHERE id = ?');
            $stmt->execute([$imageFileName, $link, $bannerId]);
        } else {
            $stmt = $pdo->prepare('UPDATE banners SET link = ? WHERE id = ?');
            $stmt->execute([$link, $bannerId]);
        }
        $_SESSION['form_message'] = 'success:Banner atualizado com sucesso!';
    } catch (PDOException $e) {
        if ($imageFileName && file_exists($filePath)) unlink($filePath);
        $_SESSION['form_message'] = 'error:Erro ao atualizar banner: ' . $e->getMessage();
    }
} else {
    $_SESSION['form_message'] = 'error:Requisição inválida.';
}
header('Location: ../admin.php');
exit(); 