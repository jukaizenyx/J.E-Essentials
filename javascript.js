 const navToggle = document.getElementById('navToggle');
    const mainNav = document.getElementById('mainNav');
 
    navToggle.addEventListener('click', () => {
        const isOpen = mainNav.classList.toggle('is-open');
        navToggle.classList.toggle('is-open');
        navToggle.setAttribute('aria-expanded', isOpen);
    });
 
    // Close the menu when a link is tapped (mobile)
    document.querySelectorAll('#mainNav a').forEach(link => {
        link.addEventListener('click', () => {
            mainNav.classList.remove('is-open');
            navToggle.classList.remove('is-open');
            navToggle.setAttribute('aria-expanded', false);
        });
    });


    function closeModal() {
    document.getElementById('successModal').style.display = 'none';
}



/* =========================================================
   J.E Essentials — shop interactivity
   Sections: mobile nav, profile dropdown, product modal + zoom,
   cart (AJAX against cart_actions.php)
   ========================================================= */
 
document.addEventListener('DOMContentLoaded', () => {
 
    /* ---------- Mobile nav toggle ---------- */
    const navToggle = document.getElementById('navToggle');
    const mainNav = document.getElementById('mainNav');
    if (navToggle && mainNav) {
        navToggle.addEventListener('click', () => {
            const isOpen = mainNav.classList.toggle('is-open');
            navToggle.classList.toggle('is-open', isOpen);
            navToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });
    }
 
    /* ---------- Profile dropdown ---------- */
    const profileTrigger = document.getElementById('profileTrigger');
    const profileDropdown = document.getElementById('profileDropdown');
    if (profileTrigger && profileDropdown) {
        profileTrigger.addEventListener('click', (e) => {
            e.stopPropagation();
            const isOpen = profileDropdown.classList.toggle('is-open');
            profileTrigger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });
        document.addEventListener('click', (e) => {
            if (!profileDropdown.contains(e.target) && e.target !== profileTrigger) {
                profileDropdown.classList.remove('is-open');
                profileTrigger.setAttribute('aria-expanded', 'false');
            }
        });
    }
 
    /* ---------- Data from PHP ---------- */
    const PRODUCTS = window.JE_PRODUCTS || [];
    const LOGGED_IN = !!window.JE_LOGGED_IN;
 
    const money = (n) => '₱' + Number(n).toLocaleString('en-PH');
 
    /* =========================================================
       PRODUCT MODAL
       ========================================================= */
    const productModalOverlay = document.getElementById('productModalOverlay');
    const productModalImage = document.getElementById('productModalImage');
    const productModalTitle = document.getElementById('productModalTitle');
    const productModalPrice = document.getElementById('productModalPrice');
    const productModalDesc = document.getElementById('productModalDesc');
    const sizeOptionsEl = document.getElementById('sizeOptions');
    const qtyInput = document.getElementById('qtyInput');
    const qtyMinus = document.getElementById('qtyMinus');
    const qtyPlus = document.getElementById('qtyPlus');
    const modalAddBtn = document.getElementById('modalAddToCart');
    const modalAddError = document.getElementById('modalAddError');
    const productModalClose = document.getElementById('productModalClose');
 
    let activeProduct = null;
    let activeSize = null;
 
    function openProductModal(productId) {
        const product = PRODUCTS.find(p => String(p.id) === String(productId));
        if (!product) return;
 
        activeProduct = product;
        const sizeKeys = Object.keys(product.sizes);
        activeSize = sizeKeys[0];
 
        productModalImage.src = product.image;
        productModalImage.alt = product.name;
        productModalTitle.textContent = product.name;
        productModalDesc.textContent = product.description || '';
        qtyInput.value = 1;
        modalAddError.textContent = '';
 
        renderSizeOptions(sizeKeys);
        updatePriceDisplay();
 
        productModalOverlay.classList.add('is-open');
        document.body.classList.add('modal-open');
    }
 
    function renderSizeOptions(sizeKeys) {
        sizeOptionsEl.innerHTML = '';
        sizeKeys.forEach((size) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'size-chip' + (size === activeSize ? ' is-selected' : '');
            btn.textContent = size;
            btn.addEventListener('click', () => {
                activeSize = size;
                [...sizeOptionsEl.children].forEach(c => c.classList.remove('is-selected'));
                btn.classList.add('is-selected');
                updatePriceDisplay();
            });
            sizeOptionsEl.appendChild(btn);
        });
    }
 
    function updatePriceDisplay() {
        if (!activeProduct || !activeSize) return;
        productModalPrice.textContent = money(activeProduct.sizes[activeSize]);
    }
 
    function closeProductModal() {
        productModalOverlay.classList.remove('is-open');
        document.body.classList.remove('modal-open');
    }
 
    productModalClose.addEventListener('click', closeProductModal);
    productModalOverlay.addEventListener('click', (e) => {
        if (e.target === productModalOverlay) closeProductModal();
    });
 
    document.querySelectorAll('.btn-add[data-product-id]').forEach((btn) => {
        btn.addEventListener('click', () => openProductModal(btn.dataset.productId));
    });
 
    /* ---------- Quantity stepper ---------- */
    qtyMinus.addEventListener('click', () => {
        qtyInput.value = Math.max(1, (parseInt(qtyInput.value, 10) || 1) - 1);
    });
    qtyPlus.addEventListener('click', () => {
        qtyInput.value = Math.min(20, (parseInt(qtyInput.value, 10) || 1) + 1);
    });
    qtyInput.addEventListener('change', () => {
        let v = parseInt(qtyInput.value, 10) || 1;
        qtyInput.value = Math.max(1, Math.min(20, v));
    });
 
    /* =========================================================
       AMAZON-STYLE HOVER ZOOM
       ========================================================= */
    const zoomFrame = document.getElementById('zoomFrame');
    const zoomPane = document.getElementById('zoomPane');
    const ZOOM_LEVEL = 2.2;
 
    function setupZoom() {
        zoomPane.style.backgroundImage = `url("${productModalImage.src}")`;
        zoomPane.style.backgroundSize = `${ZOOM_LEVEL * 100}%`;
    }
 
    zoomFrame.addEventListener('mouseenter', () => {
        setupZoom();
        zoomPane.style.opacity = '1';
    });
    zoomFrame.addEventListener('mouseleave', () => {
        zoomPane.style.opacity = '0';
    });
    zoomFrame.addEventListener('mousemove', (e) => {
        const rect = zoomFrame.getBoundingClientRect();
        const xPct = Math.min(1, Math.max(0, (e.clientX - rect.left) / rect.width));
        const yPct = Math.min(1, Math.max(0, (e.clientY - rect.top) / rect.height));
        zoomPane.style.backgroundPosition = `${xPct * 100}% ${yPct * 100}%`;
    });
 
    // Touch devices: tap to toggle a fixed zoom instead of hover.
    zoomFrame.addEventListener('touchstart', () => {
        setupZoom();
        zoomPane.style.backgroundPosition = '50% 50%';
        zoomPane.style.opacity = zoomPane.style.opacity === '1' ? '0' : '1';
    });
 
    /* =========================================================
       CART (AJAX against cart_actions.php)
       ========================================================= */
    const cartModalOverlay = document.getElementById('cartModalOverlay');
    const cartModalClose = document.getElementById('cartModalClose');
    const cartItemsEl = document.getElementById('cartItems');
    const cartEmptyMsg = document.getElementById('cartEmptyMsg');
    const cartSubtotalEl = document.getElementById('cartSubtotal');
    const cartCountEl = document.getElementById('cartCount');
    const cartTrigger = document.getElementById('cartTrigger');
    const cartCheckoutBtn = document.getElementById('cartCheckoutBtn');
    const cartToast = document.getElementById('cartToast');
 
    async function cartRequest(params) {
        const body = new URLSearchParams(params);
        const res = await fetch('cart_actions.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body,
        });
        return res.json();
    }
 
    function renderCart(data) {
        cartCountEl.textContent = data.count;
        cartSubtotalEl.textContent = money(data.subtotal);
 
        cartItemsEl.innerHTML = '';
        if (!data.items.length) {
            cartEmptyMsg.style.display = 'block';
            return;
        }
        cartEmptyMsg.style.display = 'none';
 
        data.items.forEach((item) => {
            const row = document.createElement('div');
            row.className = 'cart-item';
            row.innerHTML = `
                <img src="${item.image}" alt="${item.name}" class="cart-item-img">
                <div class="cart-item-info">
                    <p class="cart-item-name">${item.name}</p>
                    <p class="cart-item-size">${item.size}</p>
                    <div class="cart-item-qty">
                        <button type="button" class="qty-btn cart-qty-minus" aria-label="Decrease quantity">−</button>
                        <span>${item.qty}</span>
                        <button type="button" class="qty-btn cart-qty-plus" aria-label="Increase quantity">+</button>
                    </div>
                </div>
                <div class="cart-item-right">
                    <p class="cart-item-price">${money(item.line_total)}</p>
                    <button type="button" class="cart-item-remove" aria-label="Remove item">Remove</button>
                </div>
            `;
 
            row.querySelector('.cart-qty-minus').addEventListener('click', async () => {
                const next = await cartRequest({ action: 'update', product_id: item.product_id, size: item.size, qty: item.qty - 1 });
                renderCart(next);
            });
            row.querySelector('.cart-qty-plus').addEventListener('click', async () => {
                const next = await cartRequest({ action: 'update', product_id: item.product_id, size: item.size, qty: item.qty + 1 });
                renderCart(next);
            });
            row.querySelector('.cart-item-remove').addEventListener('click', async () => {
                const next = await cartRequest({ action: 'remove', product_id: item.product_id, size: item.size });
                renderCart(next);
            });
 
            cartItemsEl.appendChild(row);
        });
    }
 
    function openCartModal() {
        cartModalOverlay.classList.add('is-open');
        document.body.classList.add('modal-open');
    }
    function closeCartModal() {
        cartModalOverlay.classList.remove('is-open');
        document.body.classList.remove('modal-open');
    }
 
    cartModalClose.addEventListener('click', closeCartModal);
    cartModalOverlay.addEventListener('click', (e) => {
        if (e.target === cartModalOverlay) closeCartModal();
    });
 
    cartTrigger.addEventListener('click', async (e) => {
        e.preventDefault();
        const data = await cartRequest({ action: 'get' });
        renderCart(data);
        openCartModal();
    });
 
    cartCheckoutBtn.addEventListener('click', () => {
        window.location.href = LOGGED_IN ? 'checkout.php' : 'login.php?redirect=checkout.php';
    });
 
    function showToast() {
        cartToast.classList.add('is-visible');
        setTimeout(() => cartToast.classList.remove('is-visible'), 1800);
    }
 
    modalAddBtn.addEventListener('click', async () => {
        if (!activeProduct || !activeSize) return;
        modalAddError.textContent = '';
        modalAddBtn.disabled = true;
 
        try {
            const data = await cartRequest({
                action: 'add',
                product_id: activeProduct.id,
                size: activeSize,
                qty: qtyInput.value,
            });
 
            if (!data.ok) {
                modalAddError.textContent = data.error || 'Could not add to cart.';
                return;
            }
 
            renderCart(data);
            showToast();
            closeProductModal();
            openCartModal(); // "just a modal window would pop up" — the cart, showing the new item
        } catch (err) {
            modalAddError.textContent = 'Something went wrong. Please try again.';
        } finally {
            modalAddBtn.disabled = false;
        }
    });
 
    /* ---------- Keyboard: Esc closes whichever modal is open ---------- */
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeProductModal();
            closeCartModal();
        }
    });
});
 