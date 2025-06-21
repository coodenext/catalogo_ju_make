// js/main.js

document.addEventListener('DOMContentLoaded', function() {
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    const cartCountSpan = document.getElementById('cartCount');
    const productsContainer = document.getElementById('productsContainer');
    const searchInput = document.getElementById('searchInput');
    const productModal = new bootstrap.Modal(document.getElementById('productModal'));
    const productModalBody = document.getElementById('productModalBody');
    const cartModal = new bootstrap.Modal(document.getElementById('cartModal'));
    const cartModalBody = document.getElementById('cartModalBody');
    const finishPurchaseBtn = document.getElementById('finishPurchaseBtn');
    const cartButton = document.getElementById('cartButton');

    // --- Funções de Carrinho ---

    function updateCartCount() {
        const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
        cartCountSpan.textContent = totalItems;
    }

    function addToCart(product) {
        const existingItem = cart.find(item => item.id === product.id);

        if (existingItem) {
            // Verificar estoque antes de adicionar mais
            const productElement = productsContainer.querySelector(`.product-card[data-id="${product.id}"]`);
            const stock = parseInt(productElement ? productElement.dataset.stock : '0');

            if (existingItem.quantity < stock) {
                existingItem.quantity++;
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'Estoque Esgotado!',
                    text: 'Não há mais unidades disponíveis deste produto no estoque.',
                    showConfirmButton: true
                });
                return;
            }
        } else {
            cart.push({ ...product, quantity: 1 });
        }
        localStorage.setItem('cart', JSON.stringify(cart));
        updateCartCount();
        Swal.fire({
            icon: 'success',
            title: 'Adicionado ao Carrinho!',
            showConfirmButton: false,
            timer: 1000
        });
    }

    function removeFromCart(productId) {
        cart = cart.filter(item => item.id !== productId);
        localStorage.setItem('cart', JSON.stringify(cart));
        updateCartCount();
        renderCartModal(); // Atualiza o modal do carrinho
    }

    function updateCartItemQuantity(productId, newQuantity) {
        const itemIndex = cart.findIndex(item => item.id === productId);
        if (itemIndex > -1) {
            const productElement = productsContainer.querySelector(`.product-card[data-id="${productId}"]`);
            const stock = parseInt(productElement ? productElement.dataset.stock : '0');

            if (newQuantity > stock) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Estoque Esgotado!',
                    text: `Você só pode adicionar ${stock} unidades deste produto.`,
                    showConfirmButton: true
                });
                cart[itemIndex].quantity = stock; // Limita à quantidade em estoque
            } else if (newQuantity > 0) {
                cart[itemIndex].quantity = newQuantity;
            } else {
                removeFromCart(productId);
            }
            localStorage.setItem('cart', JSON.stringify(cart));
            updateCartCount();
            renderCartModal();
        }
    }

    function renderCartModal() {
        if (cart.length === 0) {
            cartModalBody.innerHTML = '<p class="text-center">Seu carrinho está vazio.</p>';
            finishPurchaseBtn.disabled = true;
            return;
        }

        let cartHtml = '<ul class="list-group">';
        let total = 0;

        cart.forEach(item => {
            const itemTotal = item.price * item.quantity;
            total += itemTotal;
            cartHtml += `
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <strong>${item.name}</strong><br>
                        <small>R$ ${item.price.toFixed(2).replace('.', ',')} x ${item.quantity}</small>
                    </div>
                    <div class="d-flex align-items-center">
                        <button class="btn btn-sm btn-outline-secondary me-2 quantity-decrease" data-id="${item.id}">-</button>
                        <span>${item.quantity}</span>
                        <button class="btn btn-sm btn-outline-secondary ms-2 quantity-increase" data-id="${item.id}">+</button>
                        <span class="ms-3">R$ ${itemTotal.toFixed(2).replace('.', ',')}</span>
                        <button class="btn btn-danger btn-sm ms-3 remove-from-cart-btn" data-id="${item.id}">Remover</button>
                    </div>
                </li>
            `;
        });
        cartHtml += '</ul>';
        cartHtml += `<h4 class="mt-3 text-end">Total: R$ ${total.toFixed(2).replace('.', ',')}</h4>`;
        cartModalBody.innerHTML = cartHtml;
        finishPurchaseBtn.disabled = false;

        // Adiciona event listeners para os botões de remover e ajustar quantidade
        cartModalBody.querySelectorAll('.remove-from-cart-btn').forEach(button => {
            button.addEventListener('click', function() {
                const productId = parseInt(this.dataset.id);
                removeFromCart(productId);
            });
        });

        cartModalBody.querySelectorAll('.quantity-decrease').forEach(button => {
            button.addEventListener('click', function() {
                const productId = parseInt(this.dataset.id);
                const item = cart.find(i => i.id === productId);
                if (item && item.quantity > 1) {
                    updateCartItemQuantity(productId, item.quantity - 1);
                } else if (item && item.quantity === 1) {
                    removeFromCart(productId); // Remove se a quantidade for 1
                }
            });
        });

        cartModalBody.querySelectorAll('.quantity-increase').forEach(button => {
            button.addEventListener('click', function() {
                const productId = parseInt(this.dataset.id);
                const item = cart.find(i => i.id === productId);
                if (item) {
                    updateCartItemQuantity(productId, item.quantity + 1);
                }
            });
        });
    }

    // --- Event Listeners ---

    // Adiciona ao carrinho a partir dos cards de produto
    productsContainer.addEventListener('click', function(event) {
        if (event.target.classList.contains('add-to-cart-btn')) {
            const card = event.target.closest('.product-card');
            const product = {
                id: parseInt(card.dataset.id),
                name: card.dataset.name,
                price: parseFloat(card.dataset.price),
                stock: parseInt(card.dataset.stock) // Importante para verificar estoque
            };
            addToCart(product);
        } else if (event.target.classList.contains('view-details-btn')) {
            const card = event.target.closest('.product-card');
            const product = {
                id: parseInt(card.dataset.id),
                name: card.dataset.name,
                price: parseFloat(card.dataset.price),
                stock: parseInt(card.dataset.stock),
                description: card.dataset.description,
                image: card.dataset.image
            };
            
            // Preenche o modal de detalhes do produto
            productModalBody.innerHTML = `
                <img src="${product.image}" class="img-fluid mb-3" alt="${product.name}">
                <h5>${product.name}</h5>
                <p><strong>Preço:</strong> R$ ${product.price.toFixed(2).replace('.', ',')}</p>
                <p><strong>Estoque:</strong> <span class="${product.stock > 0 ? 'text-success' : 'text-danger'}">${product.stock > 0 ? product.stock : 'Esgotado'}</span></p>
                <p><strong>Descrição:</strong> ${product.description.replace(/\n/g, '<br>') || 'Nenhuma descrição disponível.'}</p>
            `;
            // Atualiza o botão "Adicionar ao Carrinho" do modal
            const addToCartFromModalBtn = productModal.element.querySelector('.add-to-cart-from-modal-btn');
            if (product.stock > 0) {
                addToCartFromModalBtn.disabled = false;
                addToCartFromModalBtn.textContent = 'Adicionar ao Carrinho';
                addToCartFromModalBtn.onclick = () => {
                    addToCart(product);
                    productModal.hide(); // Fecha o modal após adicionar ao carrinho
                };
            } else {
                addToCartFromModalBtn.disabled = true;
                addToCartFromModalBtn.textContent = 'Sem Estoque';
            }
        }
    });

    // Filtra produtos na barra de busca
    searchInput.addEventListener('keyup', function() {
        const searchTerm = this.value.toLowerCase();
        const productCards = document.querySelectorAll('.product-card');

        productCards.forEach(card => {
            const productName = card.dataset.name.toLowerCase();
            if (productName.includes(searchTerm)) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    });

    // Abre o modal do carrinho
    cartButton.addEventListener('click', function() {
        renderCartModal();
        cartModal.show();
    });

    // Finalizar compra no WhatsApp
    finishPurchaseBtn.addEventListener('click', function() {
        if (cart.length === 0) {
            Swal.fire({
                icon: 'info',
                title: 'Carrinho Vazio',
                text: 'Por favor, adicione itens ao carrinho antes de finalizar a compra.',
                showConfirmButton: true
            });
            return;
        }

        let message = "Olá! Gostaria de fazer o seguinte pedido:\n\n";
        let total = 0;

        cart.forEach(item => {
            message += `- ${item.quantity}x ${item.name} (R$ ${item.price.toFixed(2).replace('.', ',')})\n`;
            total += item.quantity * item.price;
        });

        message += `\nTotal: R$ ${total.toFixed(2).replace('.', ',')}`;

        // whatsappNumber é uma variável global definida no PHP no index.php
        const whatsappLink = `https://wa.me/${whatsappNumber}?text=${encodeURIComponent(message)}`;
        window.open(whatsappLink, '_blank');

        // Opcional: Limpar carrinho após finalizar a compra
        // cart = [];
        // localStorage.setItem('cart', JSON.stringify(cart));
        // updateCartCount();
        // cartModal.hide();
        // Swal.fire({
        //     icon: 'success',
        //     title: 'Pedido Enviado!',
        //     text: 'Seu pedido foi enviado para o WhatsApp. Aguarde o contato!',
        //     showConfirmButton: false,
        //     timer: 2000
        // });
    });

    // Inicializa a contagem do carrinho ao carregar a página
    updateCartCount();

    // Inserir lógica para banners ou outras configurações da loja, se necessário via JS
    // Como os banners agora são carregados via PHP, esta parte pode ser removida se você não precisar de mais JS para eles.
    // Você pode ter um banner JS fallback se o PHP não conseguir carregar nenhum.
    // Exemplo: if (document.getElementById('bannerContainer').children.length === 0) { /* Adicionar banners JS */ }
});