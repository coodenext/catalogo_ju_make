<?php
// actions/save_settings.php

session_start();
require_once '../config.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    $_SESSION['form_message'] = 'error:Acesso negado. Por favor, faça login.';
    header('Location: ../admin.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['whatsapp'])) {
    $whatsapp = trim($_POST['whatsapp']);
    if (empty($whatsapp)) {
        $_SESSION['form_message'] = 'error:Informe o número do WhatsApp.';
        header('Location: ../admin.php');
        exit();
    }
    try {
        // Atualiza ou insere o número na tabela de configurações
        $stmt = $pdo->prepare('INSERT INTO configuracoes (id, whatsapp) VALUES (1, ?) ON DUPLICATE KEY UPDATE whatsapp = VALUES(whatsapp)');
        $stmt->execute([$whatsapp]);
        $_SESSION['form_message'] = 'success:Configuração salva com sucesso!';
    } catch (PDOException $e) {
        $_SESSION['form_message'] = 'error:Erro ao salvar configuração: ' . $e->getMessage();
    }
} else {
    $_SESSION['form_message'] = 'error:Requisição inválida.';
}
header('Location: ../admin.php');
exit(); 