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

        document.querySelectorAll('#mainNav a').forEach(link => {
            link.addEventListener('click', () => {
                mainNav.classList.remove('is-open');
                navToggle.classList.remove('is-open');
                navToggle.setAttribute('aria-expanded', 'false');
            });
        });
    }

    /* ---------- Success modal (registration page) ---------- */
    const successModal = document.getElementById('successModal');
    if (successModal) {
        window.closeModal = function () {
            successModal.style.display = 'none';
        };
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

    const PRODUCTS = window.JE_PRODUCTS || [];
    const LOGGED_IN = !!window.JE_LOGGED_IN;
    const money = (n) => '₱' + Number(n).toLocaleString('en-PH');

    /* =========================================================
       CART (AJAX against cart_actions.php)
       Independent of the product modal — this needs to work on
       ANY page that has the cart icon + cart modal in its HTML
       (shop.php, dashboard.php, profile.php, settings.php, etc.),
       not just pages that also have the product-detail modal.
       ========================================================= */
    const cartModalOverlay = document.getElementById('cartModalOverlay');

    let renderCart = () => {}; // no-op fallback if cart modal isn't on this page

    if (cartModalOverlay) {
        const cartModalClose = document.getElementById('cartModalClose');
        const cartItemsEl = document.getElementById('cartItems');
        const cartEmptyMsg = document.getElementById('cartEmptyMsg');
        const cartSubtotalEl = document.getElementById('cartSubtotal');
        const cartCountEl = document.getElementById('cartCount');
        const cartTrigger = document.getElementById('cartTrigger');
        const cartCheckoutBtn = document.getElementById('cartCheckoutBtn');
        const cartToast = document.getElementById('cartToast');
        const checkoutMessage = document.getElementById('checkoutMessage');
        

        async function cartRequest(params) {
            const body = new URLSearchParams(params);
            const res = await fetch('cart_actions.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body,
            });
            return res.json();
        }

        renderCart = function (data) {
            if (cartCountEl) cartCountEl.textContent = data.count;
            if (cartSubtotalEl) cartSubtotalEl.textContent = money(data.subtotal);
            if (!cartItemsEl) return;

            cartItemsEl.innerHTML = '';
            if (!data.items.length) {
                if (cartEmptyMsg) {
                    cartItemsEl.appendChild(cartEmptyMsg);
                    cartEmptyMsg.style.display = 'block';
                }
                return;
            }
            if (cartEmptyMsg) cartEmptyMsg.style.display = 'none';

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
        };

        function openCartModal() {
            cartModalOverlay.classList.add('is-open');
            document.body.classList.add('modal-open');
        }
        function closeCartModal() {
            cartModalOverlay.classList.remove('is-open');
            document.body.classList.remove('modal-open');
        }
           

        function closeCartModal() { 
            cartModalOverlay.classList.remove( 'is-open' ); 
            document.body.classList.remove( 'modal-open' ); 
            
            /* ---------- Clear checkout error ---------- */ 
            if (checkoutMessage) { 
                checkoutMessage.style.display = 'none'; 
                checkoutMessage.textContent = ''; 
            } 
        }

        if (cartModalClose) cartModalClose.addEventListener('click', closeCartModal);
        cartModalOverlay.addEventListener('click', (e) => {
            if (e.target === cartModalOverlay) closeCartModal();
        });

        if (cartTrigger) {
            cartTrigger.addEventListener('click', async (e) => {
                e.preventDefault();
                const data = await cartRequest({ action: 'get' });
                renderCart(data);
                openCartModal();
            });
        }

        if (cartCheckoutBtn) {
    cartCheckoutBtn.addEventListener('click', function () {

        const cartItems = document.querySelectorAll(
            '#cartItems .cart-item'
        );

        if (cartItems.length === 0) {
            checkoutMessage.textContent =
                'Please add at least one product to your cart before checking out.';
            checkoutMessage.style.display = 'block';
            return;
        }

        window.location.href = 'checkout.php';
    });
}

        window.__jeCart = { cartRequest, renderCart, openCartModal, closeCartModal };

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeCartModal();
        });
    }

    /* =========================================================
       PRODUCT MODAL (only exists on shop.php)
       ========================================================= */
    const productModalOverlay = document.getElementById('productModalOverlay');

    if (productModalOverlay) {
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
        const cartToast = document.getElementById('cartToast');

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

        /* ---------- Amazon-style hover zoom ---------- */
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
        zoomFrame.addEventListener('touchstart', () => {
            setupZoom();
            zoomPane.style.backgroundPosition = '50% 50%';
            zoomPane.style.opacity = zoomPane.style.opacity === '1' ? '0' : '1';
        });

        /* ---------- Add to cart, from inside the product modal ---------- */
        modalAddBtn.addEventListener('click', async () => {
            if (!activeProduct || !activeSize || !window.__jeCart) return;
            modalAddError.textContent = '';
            modalAddBtn.disabled = true;

            try {
                const data = await window.__jeCart.cartRequest({
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
                if (cartToast) {
                    cartToast.classList.add('is-visible');
                    setTimeout(() => cartToast.classList.remove('is-visible'), 1800);
                }
                closeProductModal();
                window.__jeCart.openCartModal(); // "just a modal window would pop up" — the cart, showing the new item
            } catch (err) {
                modalAddError.textContent = 'Something went wrong. Please try again.';
            } finally {
                modalAddBtn.disabled = false;
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeProductModal();
        });
    }

    const addressOptions = document.querySelectorAll('input[name="address_type"]');
const newAddressFields = document.getElementById('new-address-fields');
const newAddressInputs = newAddressFields.querySelectorAll('input');

function updateAddressFields() {
    const selectedAddress = document.querySelector('input[name="address_type"]:checked');

    if (!selectedAddress) return;

    const useNewAddress = selectedAddress.value === 'new';

    newAddressFields.style.display = useNewAddress ? 'grid' : 'none';

    newAddressInputs.forEach(input => {
        input.required = useNewAddress;
    });
}

addressOptions.forEach(option => {
    option.addEventListener('change', updateAddressFields);
});

updateAddressFields();

addressOptions.forEach(option => {
    option.addEventListener('change', updateAddressFields);
});

updateAddressFields();


/* ---------- GCash Payment ---------- */

const paymentOptions = document.querySelectorAll(
    'input[name="payment_method"]'
);

const gcashPayment = document.getElementById('gcash-payment');

if (paymentOptions.length && gcashPayment) {

    function updatePaymentMethod() {

        const selectedPayment = document.querySelector(
            'input[name="payment_method"]:checked'
        );

        if (!selectedPayment) return;

        const isGCash = selectedPayment.value === 'GCash';

        gcashPayment.style.display = isGCash ? 'block' : 'none';
    }

    paymentOptions.forEach(option => {
        option.addEventListener('change', updatePaymentMethod);
    });

    updatePaymentMethod();
}


// FOR ADMIN //


});




