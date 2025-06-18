// Configurações de login
let ADMIN_CREDENTIALS = {
    username: 'admin',
    password: '123' // Em produção, usar autenticação mais segura
};

// Carregar credenciais do localStorage se existirem
const savedCredentials = localStorage.getItem('adminCredentials');
if (savedCredentials) {
    ADMIN_CREDENTIALS = JSON.parse(savedCredentials);
}

// Elementos do DOM
const loginScreen = document.getElementById('loginScreen');
const adminPanel = document.getElementById('adminPanel');
const loginForm = document.getElementById('loginForm');
const logoutBtn = document.getElementById('logoutBtn');
const productsTableBody = document.getElementById('productsTableBody');
const bannersGrid = document.getElementById('bannersGrid');
const currentLogo = document.getElementById('currentLogo');
const whatsappNumber = document.getElementById('whatsappNumber');
const saveSettingsBtn = document.getElementById('saveSettings');

// Inicialização
document.addEventListener('DOMContentLoaded', () => {
    checkAuth();
    setupEventListeners();
    loadStoreConfig();
});

// Verificar autenticação
function checkAuth() {
    const isAuthenticated = localStorage.getItem('isAuthenticated') === 'true';
    if (isAuthenticated) {
        showAdminPanel();
    } else {
        showLoginScreen();
    }
}

// Configurar event listeners
function setupEventListeners() {
    // Login
    loginForm.addEventListener('submit', handleLogin);
    
    // Logout
    logoutBtn.addEventListener('click', handleLogout);
    
    // Navegação
    document.querySelectorAll('.nav-link[data-section]').forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const section = e.target.dataset.section;
            showSection(section);
        });
    });
    
    // Salvar produto
    document.getElementById('saveProductBtn').addEventListener('click', saveProduct);
    
    // Salvar banner
    document.getElementById('saveBannerBtn').addEventListener('click', saveBanner);
    
    // Salvar configurações
    saveSettingsBtn.addEventListener('click', saveStoreConfig);

    // Salvar credenciais
    document.getElementById('saveCredentialsBtn').addEventListener('click', handleChangeCredentials);

    // Atualizar produto
    document.getElementById('updateProductBtn').addEventListener('click', updateProduct);
}

// Mostrar tela de login
function showLoginScreen() {
    loginScreen.style.display = 'flex';
    adminPanel.style.display = 'none';
}

// Mostrar painel admin
function showAdminPanel() {
    loginScreen.style.display = 'none';
    adminPanel.style.display = 'block';
    loadProducts();
    loadBanners();
}

// Mostrar seção específica
function showSection(sectionName) {
    document.querySelectorAll('.admin-section').forEach(section => {
        section.style.display = 'none';
    });
    document.getElementById(`${sectionName}Section`).style.display = 'block';
}

// Manipular login
function handleLogin(e) {
    e.preventDefault();
    
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    
    if (username === ADMIN_CREDENTIALS.username && password === ADMIN_CREDENTIALS.password) {
        localStorage.setItem('isAuthenticated', 'true');
        showAdminPanel();
    } else {
        alert('Usuário ou senha incorretos!');
    }
}

// Manipular logout
function handleLogout(e) {
    e.preventDefault();
    localStorage.removeItem('isAuthenticated');
    showLoginScreen();
}

// Carregar produtos
function loadProducts() {
    const products = JSON.parse(localStorage.getItem('products')) || [];
    displayProducts(products);
}

// Exibir produtos
function displayProducts(products) {
    productsTableBody.innerHTML = '';
    
    products.forEach(product => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td><img src="${product.image}" class="product-image-preview" alt="${product.name}"></td>
            <td>${product.name}</td>
            <td>R$ ${product.price.toFixed(2)}</td>
            <td>${product.stock}</td>
            <td>
                <button class="btn btn-primary btn-sm me-2" onclick="editProduct(${product.id})">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-danger btn-sm" onclick="deleteProduct(${product.id})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        productsTableBody.appendChild(row);
    });
}

// Salvar produto
function saveProduct() {
    const form = document.getElementById('addProductForm');
    const formData = new FormData(form);
    
    const product = {
        id: Date.now(),
        name: formData.get('name'),
        price: parseFloat(formData.get('price')),
        stock: parseInt(formData.get('stock')),
        description: formData.get('description'),
        image: URL.createObjectURL(formData.get('image'))
    };
    
    const products = JSON.parse(localStorage.getItem('products')) || [];
    products.push(product);
    localStorage.setItem('products', JSON.stringify(products));
    
    displayProducts(products);
    bootstrap.Modal.getInstance(document.getElementById('addProductModal')).hide();
    form.reset();
}

// Excluir produto
function deleteProduct(productId) {
    if (!confirm('Tem certeza que deseja excluir este produto?')) return;
    
    const products = JSON.parse(localStorage.getItem('products')) || [];
    const updatedProducts = products.filter(p => p.id !== productId);
    localStorage.setItem('products', JSON.stringify(updatedProducts));
    
    displayProducts(updatedProducts);
}

// Carregar banners
function loadBanners() {
    const banners = JSON.parse(localStorage.getItem('banners')) || [];
    displayBanners(banners);
}

// Exibir banners
function displayBanners(banners) {
    bannersGrid.innerHTML = '';
    
    banners.forEach(banner => {
        const col = document.createElement('div');
        col.className = 'col-md-4';
        col.innerHTML = `
            <div class="banner-card">
                <img src="${banner.image}" class="banner-image" alt="Banner">
                <div class="banner-actions">
                    <button class="btn btn-danger btn-sm" onclick="deleteBanner(${banner.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
        bannersGrid.appendChild(col);
    });
}

// Salvar banner
function saveBanner() {
    const form = document.getElementById('addBannerForm');
    const formData = new FormData(form);
    
    const banner = {
        id: Date.now(),
        image: URL.createObjectURL(formData.get('bannerImage'))
    };
    
    const banners = JSON.parse(localStorage.getItem('banners')) || [];
    banners.push(banner);
    localStorage.setItem('banners', JSON.stringify(banners));
    
    displayBanners(banners);
    bootstrap.Modal.getInstance(document.getElementById('addBannerModal')).hide();
    form.reset();
}

// Excluir banner
function deleteBanner(bannerId) {
    if (!confirm('Tem certeza que deseja excluir este banner?')) return;
    
    const banners = JSON.parse(localStorage.getItem('banners')) || [];
    const updatedBanners = banners.filter(b => b.id !== bannerId);
    localStorage.setItem('banners', JSON.stringify(updatedBanners));
    
    displayBanners(updatedBanners);
}

// Carregar configurações da loja
function loadStoreConfig() {
    const config = JSON.parse(localStorage.getItem('storeConfig')) || {
        logo: 'images/default-logo.png',
        whatsappNumber: '5511999999999'
    };
    
    currentLogo.src = config.logo;
    whatsappNumber.value = config.whatsappNumber;
}

// Salvar configurações da loja
function saveStoreConfig() {
    const logoFile = document.getElementById('logoUpload').files[0];
    const config = {
        whatsappNumber: whatsappNumber.value
    };
    
    if (logoFile) {
        config.logo = URL.createObjectURL(logoFile);
    } else {
        config.logo = currentLogo.src;
    }
    
    localStorage.setItem('storeConfig', JSON.stringify(config));
    alert('Configurações salvas com sucesso!');
}

// Função para alterar credenciais
function changeCredentials(newUsername, newPassword) {
    if (!newUsername || !newPassword) {
        alert('Nome de usuário e senha são obrigatórios!');
        return false;
    }
    
    ADMIN_CREDENTIALS = {
        username: newUsername,
        password: newPassword
    };
    
    localStorage.setItem('adminCredentials', JSON.stringify(ADMIN_CREDENTIALS));
    return true;
}

// Manipular alteração de credenciais
function handleChangeCredentials() {
    const newUsername = document.getElementById('newUsername').value;
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    
    if (!newUsername || !newPassword || !confirmPassword) {
        alert('Todos os campos são obrigatórios!');
        return;
    }
    
    if (newPassword !== confirmPassword) {
        alert('As senhas não coincidem!');
        return;
    }
    
    if (changeCredentials(newUsername, newPassword)) {
        alert('Credenciais alteradas com sucesso! Você será desconectado.');
        handleLogout();
        bootstrap.Modal.getInstance(document.getElementById('changeCredentialsModal')).hide();
        document.getElementById('changeCredentialsForm').reset();
    }
}

// Editar produto
function editProduct(productId) {
    const products = JSON.parse(localStorage.getItem('products')) || [];
    const product = products.find(p => p.id === productId);
    
    if (!product) return;
    
    // Preencher formulário
    document.getElementById('editProductId').value = product.id;
    document.getElementById('editProductName').value = product.name;
    document.getElementById('editProductPrice').value = product.price;
    document.getElementById('editProductStock').value = product.stock;
    document.getElementById('editProductDescription').value = product.description;
    
    // Mostrar modal
    const editModal = new bootstrap.Modal(document.getElementById('editProductModal'));
    editModal.show();
}

// Atualizar produto
function updateProduct() {
    const productId = parseInt(document.getElementById('editProductId').value);
    const products = JSON.parse(localStorage.getItem('products')) || [];
    const productIndex = products.findIndex(p => p.id === productId);
    
    if (productIndex === -1) return;
    
    const updatedProduct = {
        ...products[productIndex],
        name: document.getElementById('editProductName').value,
        price: parseFloat(document.getElementById('editProductPrice').value),
        stock: parseInt(document.getElementById('editProductStock').value),
        description: document.getElementById('editProductDescription').value
    };
    
    // Atualizar imagem se uma nova foi selecionada
    const newImage = document.getElementById('editProductImage').files[0];
    if (newImage) {
        updatedProduct.image = URL.createObjectURL(newImage);
    }
    
    products[productIndex] = updatedProduct;
    localStorage.setItem('products', JSON.stringify(products));
    
    // Atualizar tabela
    displayProducts(products);
    
    // Fechar modal
    bootstrap.Modal.getInstance(document.getElementById('editProductModal')).hide();
    
    // Limpar formulário
    document.getElementById('editProductForm').reset();
} 