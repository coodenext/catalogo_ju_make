// Configurações
let storeConfig = {
    whatsappNumber: '5511999999999', // Número padrão, será atualizado via admin
    logo: 'images/default-logo.png'
};

// Estado do carrinho
let cart = JSON.parse(localStorage.getItem('cart')) || [];

// Elementos do DOM
const productsContainer = document.getElementById('productsContainer');
const searchInput = document.getElementById('searchInput');
const cartButton = document.getElementById('cartButton');
const cartCount = document.getElementById('cartCount');
const bannerContainer = document.getElementById('bannerContainer');
const storeLogo = document.getElementById('store-logo');

// Inicialização
document.addEventListener('DOMContentLoaded', () => {
    loadStoreConfig();
    loadProducts();
    loadBanners();
    updateCartCount();
    setupEventListeners();
});

// Carregar configurações da loja
function loadStoreConfig() {
    const config = JSON.parse(localStorage.getItem('storeConfig')) || storeConfig;
    storeConfig = config;
    storeLogo.src = config.logo;
}

// Carregar produtos
function loadProducts() {
    const products = JSON.parse(localStorage.getItem('products')) || [];
    displayProducts(products);
}

// Exibir produtos
function displayProducts(products) {
    productsContainer.innerHTML = '';
    
    products.forEach(product => {
        const productCard = createProductCard(product);
        productsContainer.appendChild(productCard);
    });
}

// Criar card de produto
function createProductCard(product) {
    const col = document.createElement('div');
    col.className = 'col-md-4 col-sm-6 mb-4';
    
    const isOutOfStock = product.stock <= 0;
    const stockClass = isOutOfStock ? 'text-danger' : 'text-success';
    const stockText = isOutOfStock ? 'Indisponível' : `Estoque: ${product.stock} unidades`;
    
    col.innerHTML = `
        <div class="card product-card ${isOutOfStock ? 'out-of-stock' : ''}">
            <img src="${product.image}" class="card-img-top product-image" alt="${product.name}">
            <div class="card-body">
                <h5 class="card-title product-title">${product.name}</h5>
                <p class="product-price">R$ ${product.price.toFixed(2)}</p>
                <p class="product-stock ${stockClass}">${stockText}</p>
                <div class="product-actions">
                    <button class="btn btn-whatsapp" onclick="buyOnWhatsApp(${product.id})" ${isOutOfStock ? 'disabled' : ''}>
                        <i class="fab fa-whatsapp"></i> Comprar
                    </button>
                    <button class="btn btn-cart" onclick="addToCart(${product.id})" ${isOutOfStock ? 'disabled' : ''}>
                        <i class="fas fa-shopping-cart"></i> Carrinho
                    </button>
                </div>
            </div>
        </div>
    `;
    
    return col;
}

// Carregar banners
function loadBanners() {
    const banners = JSON.parse(localStorage.getItem('banners')) || [];
    displayBanners(banners);
}

// Exibir banners
function displayBanners(banners) {
    bannerContainer.innerHTML = '';
    
    banners.forEach((banner, index) => {
        const div = document.createElement('div');
        div.className = `carousel-item ${index === 0 ? 'active' : ''}`;
        div.innerHTML = `<img src="${banner.image}" class="d-block w-100" alt="Banner ${index + 1}">`;
        bannerContainer.appendChild(div);
    });
}

// Configurar event listeners
function setupEventListeners() {
    // Busca de produtos
    searchInput.addEventListener('input', (e) => {
        const searchTerm = e.target.value.toLowerCase();
        const products = JSON.parse(localStorage.getItem('products')) || [];
        const filteredProducts = products.filter(product => 
            product.name.toLowerCase().includes(searchTerm)
        );
        displayProducts(filteredProducts);
    });

    // Botão do carrinho
    cartButton.addEventListener('click', () => {
        const cartModal = new bootstrap.Modal(document.getElementById('cartModal'));
        updateCartModal();
        cartModal.show();
    });
}

// Adicionar ao carrinho
function addToCart(productId) {
    const products = JSON.parse(localStorage.getItem('products')) || [];
    const product = products.find(p => p.id === productId);
    
    if (!product || product.stock <= 0) {
        alert('Produto indisponível!');
        return;
    }
    
    const cartItem = cart.find(item => item.id === productId);
    
    if (cartItem) {
        if (cartItem.quantity >= product.stock) {
            alert('Quantidade indisponível em estoque!');
            return;
        }
        cartItem.quantity++;
    } else {
        cart.push({
            id: product.id,
            name: product.name,
            price: product.price,
            image: product.image,
            quantity: 1
        });
    }
    
    saveCart();
    updateCartCount();
}

// Atualizar contador do carrinho
function updateCartCount() {
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    cartCount.textContent = totalItems;
}

// Salvar carrinho
function saveCart() {
    localStorage.setItem('cart', JSON.stringify(cart));
}

// Atualizar modal do carrinho
function updateCartModal() {
    const cartModalBody = document.getElementById('cartModalBody');
    let total = 0;
    
    cartModalBody.innerHTML = cart.map(item => {
        const itemTotal = item.price * item.quantity;
        total += itemTotal;
        
        return `
            <div class="cart-item">
                <img src="${item.image}" class="cart-item-image" alt="${item.name}">
                <div class="cart-item-details">
                    <h6>${item.name}</h6>
                    <div class="quantity-controls">
                        <button class="btn btn-sm btn-outline-secondary" onclick="updateCartQuantity(${item.id}, ${item.quantity - 1})">-</button>
                        <span class="mx-2">${item.quantity}</span>
                        <button class="btn btn-sm btn-outline-secondary" onclick="updateCartQuantity(${item.id}, ${item.quantity + 1})">+</button>
                    </div>
                    <p class="cart-item-price">R$ ${itemTotal.toFixed(2)}</p>
                </div>
                <button class="btn btn-danger btn-sm" onclick="removeFromCart(${item.id})">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
    }).join('') + `
        <div class="cart-total">
            Total: R$ ${total.toFixed(2)}
        </div>
    `;
}

// Atualizar quantidade no carrinho
function updateCartQuantity(productId, newQuantity) {
    if (newQuantity < 1) {
        removeFromCart(productId);
        return;
    }
    
    const products = JSON.parse(localStorage.getItem('products')) || [];
    const product = products.find(p => p.id === productId);
    
    if (!product || newQuantity > product.stock) {
        alert('Quantidade indisponível em estoque!');
        return;
    }
    
    const cartItem = cart.find(item => item.id === productId);
    if (cartItem) {
        cartItem.quantity = newQuantity;
        saveCart();
        updateCartCount();
        updateCartModal();
    }
}

// Remover do carrinho
function removeFromCart(productId) {
    cart = cart.filter(item => item.id !== productId);
    saveCart();
    updateCartCount();
    updateCartModal();
}

// Comprar pelo WhatsApp
function buyOnWhatsApp(productId) {
    const products = JSON.parse(localStorage.getItem('products')) || [];
    const product = products.find(p => p.id === productId);
    
    if (!product || product.stock <= 0) {
        alert('Produto indisponível!');
        return;
    }
    
    const message = `Olá! Gostaria de comprar o produto:\n\n${product.name}\nR$ ${product.price.toFixed(2)}\n\n${window.location.origin}/product/${product.id}`;
    const whatsappUrl = `https://wa.me/${storeConfig.whatsappNumber}?text=${encodeURIComponent(message)}`;
    
    window.open(whatsappUrl, '_blank');
}

// Finalizar compra no WhatsApp
document.getElementById('finishPurchaseBtn').addEventListener('click', () => {
    if (cart.length === 0) return;
    
    let message = 'Olá! Quero finalizar a compra com os seguintes itens:\n\n';
    
    // Atualizar estoque e preparar mensagem
    const products = JSON.parse(localStorage.getItem('products')) || [];
    let updatedProducts = [...products];
    
    cart.forEach(item => {
        message += `- ${item.name} - R$${item.price.toFixed(2)} - ${item.quantity} unidade(s)\n`;
        
        // Atualizar estoque
        const productIndex = updatedProducts.findIndex(p => p.id === item.id);
        if (productIndex !== -1) {
            updatedProducts[productIndex].stock -= item.quantity;
        }
    });
    
    const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    message += `\nTotal: R$${total.toFixed(2)}`;
    
    // Salvar produtos atualizados
    localStorage.setItem('products', JSON.stringify(updatedProducts));
    
    // Esvaziar carrinho
    cart = [];
    saveCart();
    updateCartCount();
    
    // Fechar modal do carrinho
    bootstrap.Modal.getInstance(document.getElementById('cartModal')).hide();
    
    // Abrir WhatsApp
    const whatsappUrl = `https://wa.me/${storeConfig.whatsappNumber}?text=${encodeURIComponent(message)}`;
    window.open(whatsappUrl, '_blank');
    
    // Recarregar produtos na página
    loadProducts();
}); 