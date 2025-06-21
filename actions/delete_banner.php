<?php
// actions/delete_banner.php

session_start();
require_once '../config.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    $_SESSION['form_message'] = 'error:Acesso negado. Por favor, faça login.';
    header('Location: ../admin.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['banner_id'])) {
    $bannerId = intval($_POST['banner_id']);
    try {
        // Buscar nome do arquivo para remover do disco
        $stmt = $pdo->prepare('SELECT imagem FROM banners WHERE id = ?');
        $stmt->execute([$bannerId]);
        $banner = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($banner && !empty($banner['imagem'])) {
            $filePath = '../uploads/banners/' . $banner['imagem'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
        // Excluir do banco
        $stmt = $pdo->prepare('DELETE FROM banners WHERE id = ?');
        $stmt->execute([$bannerId]);
        $_SESSION['form_message'] = 'success:Banner excluído com sucesso!';
    } catch (PDOException $e) {
        $_SESSION['form_message'] = 'error:Erro ao excluir banner: ' . $e->getMessage();
    }
} else {
    $_SESSION['form_message'] = 'error:Requisição inválida.';
}
header('Location: ../admin.php');
exit(); 