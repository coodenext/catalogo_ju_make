<?php
// config.php

define('DB_HOST', 'localhost'); // Geralmente 'localhost'
define('DB_USER', 'catjumake'); // Nome de usuário do MySQL
define('DB_PASS', 'Jumake@776');   // Senha do usuário do MySQL
define('DB_NAME', 'catjumake'); // Nome do seu banco de dados

// Tente conectar ao banco de dados
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Define o modo de erro para exceções
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); // Define o modo de busca padrão para arrays associativos
} catch (PDOException $e) {
    // Se a conexão falhar, exibe uma mensagem de erro e interrompe o script
    die("Erro de conexão com o banco de dados: " . $e->getMessage());
}
?>