<?php
// actions/change_credentials.php

session_start();
require_once '../config.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    $_SESSION['form_message'] = 'error:Acesso negado. Por favor, faça login.';
    header('Location: ../admin.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newUsername = trim($_POST['newUsername'] ?? '');
    $newPassword = trim($_POST['newPassword'] ?? '');
    $confirmPassword = trim($_POST['confirmPassword'] ?? '');

    if (empty($newUsername) || empty($newPassword) || empty($confirmPassword)) {
        $_SESSION['form_message'] = 'error:Todos os campos são obrigatórios.';
        header('Location: ../admin.php');
        exit();
    }
    if ($newPassword !== $confirmPassword) {
        $_SESSION['form_message'] = 'error:As senhas não coincidem.';
        header('Location: ../admin.php');
        exit();
    }
    try {
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        // Atualiza ou insere o admin na tabela
        $stmt = $pdo->prepare('INSERT INTO admin_users (id, username, password) VALUES (1, ?, ?) ON DUPLICATE KEY UPDATE username = VALUES(username), password = VALUES(password)');
        $stmt->execute([$newUsername, $hash]);
        $_SESSION['form_message'] = 'success:Credenciais alteradas com sucesso! Faça login novamente.';
        session_unset();
        session_destroy();
    } catch (PDOException $e) {
        $_SESSION['form_message'] = 'error:Erro ao alterar credenciais: ' . $e->getMessage();
    }
} else {
    $_SESSION['form_message'] = 'error:Requisição inválida.';
}
header('Location: ../admin.php');
exit(); 