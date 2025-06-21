<?php
// index.php

// Inclui o arquivo de conexão com o banco de dados
require_once 'config.php'; // Certifique-se de que config.php está no mesmo nível ou ajuste o caminho

// --- Função para buscar produtos do banco de dados ---
function getProducts($pdo) {
    try {
        $stmt = $pdo->query("SELECT id, nome, preco, estoque, descricao, imagem FROM produtos ORDER BY id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Em um ambiente de produção, logue o erro em vez de exibi-lo diretamente
        error_log("Erro ao buscar produtos: " . $e->getMessage());
        return []; // Retorna um array vazio em caso de erro
    }
}

// --- Função para buscar banners do banco de dados ---
function getBanners($pdo) {
    try {
        // Supondo que você terá uma tabela 'banners' similar à de 'produtos'
        // Por enquanto, vamos retornar um array vazio se a tabela 'banners' ainda não existir
        // Ou você pode criar uma tabela 'banners' com id, imagem, link, ativo, etc.
        // Exemplo: CREATE TABLE banners (id INT AUTO_INCREMENT PRIMARY KEY, imagem VARCHAR(255) NOT NULL, link VARCHAR(255), ativo BOOLEAN DEFAULT TRUE);
        
        // Se a tabela banners não existe ou não tem dados, retorne alguns placeholders
        // Em produção, você faria uma consulta SQL real aqui
        $stmt = $pdo->query("SELECT id, imagem FROM banners ORDER BY id ASC"); // Supondo uma tabela 'banners'
        $banners = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($banners)) {
            return [
                ['imagem' => 'banner1.jpg'], // Certifique-se de que estas imagens existem em uploads/banners/
                ['imagem' => 'banner2.jpg'],
                ['imagem' => 'banner3.jpg'],
            ];
        }
        return $banners;

    } catch (PDOException $e) {
        error_log("Erro ao buscar banners: " . $e->getMessage());
        return [
            ['imagem' => 'banner1.jpg'], // Fallback com placeholders
            ['imagem' => 'banner2.jpg'],
            ['imagem' => 'banner3.jpg'],
        ];
    }
}

// --- Busca os dados do banco de dados ---
$produtos = getProducts($pdo);
$banners = getBanners($pdo);

// --- Exemplo de Configurações da Loja (também poderiam vir do BD) ---
$store_logo = 'images/default-logo.png'; // Caminho para a logo padrão
$whatsapp_number = '5511999999999'; // Número do WhatsApp para finalizar compra
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo Ju Make</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.12.2/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.12.2/dist/sweetalert2.all.min.js"></script>
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="logo-container">
                <img id="store-logo" src="<?php echo htmlspecialchars($store_logo); ?>" alt="Logo da Loja">
            </div>
        </div>
    </header>

    <div id="bannerCarousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner" id="bannerContainer">
            <?php if (!empty($banners)): ?>
                <?php foreach ($banners as $index => $banner): ?>
                    <div class="carousel-item <?php echo ($index === 0) ? 'active' : ''; ?>">
                        <img src="uploads/banners/<?php echo htmlspecialchars($banner['imagem']); ?>" class="d-block w-100" alt="Banner <?php echo $index + 1; ?>">
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="carousel-item active">
                    <img src="images/placeholder-banner.png" class="d-block w-100" alt="Banner Placeholder">
                </div>
            <?php endif; ?>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#bannerCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#bannerCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
        </button>
    </div>

    <div class="container mt-4">
        <div class="search-container">
            <input type="text" id="searchInput" class="form-control" placeholder="Buscar produtos...">
        </div>
    </div>

    <div class="container mt-4">
        <div class="row" id="productsContainer">
            <?php if (!empty($produtos)): ?>
                <?php foreach ($produtos as $produto): ?>
                    <?php
                        $imagePath = !empty($produto['imagem']) ? "uploads/produtos/" . htmlspecialchars($produto['imagem']) : "images/placeholder-product.png";
                        $productDescription = nl2br(htmlspecialchars($produto['descricao'])); // Converte quebras de linha em <br>
                    ?>
                    <div class="col-6 col-md-4 col-lg-3 mb-4 product-card" 
                         data-id="<?php echo htmlspecialchars($produto['id']); ?>" 
                         data-name="<?php echo htmlspecialchars($produto['nome']); ?>" 
                         data-price="<?php echo htmlspecialchars($produto['preco']); ?>" 
                         data-stock="<?php echo htmlspecialchars($produto['estoque']); ?>"
                         data-description="<?php echo htmlspecialchars($produto['descricao']); ?>"
                         data-image="<?php echo htmlspecialchars($imagePath); ?>">
                        <div class="card h-100">
                            <img src="<?php echo $imagePath; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($produto['nome']); ?>">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?php echo htmlspecialchars($produto['nome']); ?></h5>
                                <p class="card-text price">R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></p>
                                <p class="card-text stock <?php echo ($produto['estoque'] > 0) ? 'text-success' : 'text-danger'; ?>">
                                    Estoque: <?php echo htmlspecialchars($produto['estoque']); ?>
                                </p>
                                <button class="btn btn-primary mt-auto add-to-cart-btn" 
                                    data-id="<?php echo htmlspecialchars($produto['id']); ?>" 
                                    data-name="<?php echo htmlspecialchars($produto['nome']); ?>" 
                                    data-price="<?php echo htmlspecialchars($produto['preco']); ?>" 
                                    <?php echo ($produto['estoque'] <= 0) ? 'disabled' : ''; ?>>
                                    <?php echo ($produto['estoque'] <= 0) ? 'Sem Estoque' : 'Adicionar ao Carrinho'; ?>
                                </button>
                                <button class="btn btn-outline-info btn-sm mt-2 view-details-btn" 
                                        data-bs-toggle="modal" data-bs-target="#productModal"
                                        data-id="<?php echo htmlspecialchars($produto['id']); ?>" 
                                        data-name="<?php echo htmlspecialchars($produto['nome']); ?>" 
                                        data-price="<?php echo htmlspecialchars($produto['preco']); ?>" 
                                        data-stock="<?php echo htmlspecialchars($produto['estoque']); ?>"
                                        data-description="<?php echo htmlspecialchars($produto['descricao']); ?>"
                                        data-image="<?php echo htmlspecialchars($imagePath); ?>">
                                    Ver Detalhes
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center">
                    <p>Nenhum produto encontrado no momento.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="cart-container">
        <button class="cart-button" id="cartButton">
            <i class="fas fa-shopping-cart"></i>
            <span id="cartCount">0</span>
        </button>
    </div>

    <div class="modal fade" id="productModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="productModalTitle">Detalhes do Produto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="productModalBody">
                    </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    <button type="button" class="btn btn-primary add-to-cart-from-modal-btn">Adicionar ao Carrinho</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="cartModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Carrinho de Compras</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="cartModalBody">
                    </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Continuar Comprando</button>
                    <button type="button" class="btn btn-primary" id="finishPurchaseBtn">Finalizar Compra no WhatsApp</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Variável global para o número do WhatsApp
        const whatsappNumber = "<?php echo htmlspecialchars($whatsapp_number); ?>"; 
    </script>
    <script src="js/main.js"></script>
</body>
</html>