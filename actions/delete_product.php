<?php
// actions/delete_product.php

session_start();
require_once '../config.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    $_SESSION['form_message'] = 'error:Acesso negado. Por favor, faça login.';
    header('Location: ../admin.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $productId = intval($_POST['product_id']);
    try {
        // Buscar nome do arquivo para remover do disco
        $stmt = $pdo->prepare('SELECT imagem FROM produtos WHERE id = ?');
        $stmt->execute([$productId]);
        $produto = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($produto && !empty($produto['imagem'])) {
            $filePath = '../uploads/produtos/' . $produto['imagem'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
        // Excluir do banco
        $stmt = $pdo->prepare('DELETE FROM produtos WHERE id = ?');
        $stmt->execute([$productId]);
        $_SESSION['form_message'] = 'success:Produto excluído com sucesso!';
    } catch (PDOException $e) {
        $_SESSION['form_message'] = 'error:Erro ao excluir produto: ' . $e->getMessage();
    }
} else {
    $_SESSION['form_message'] = 'error:Requisição inválida.';
}
header('Location: ../admin.php');
exit(); 