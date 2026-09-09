<?php
require_once 'cart_functions.php';
$products = je_get_products();

// Nav data (previously in includes/nav.php, now inlined below)
$je_user = je_current_user();
$je_cart_count = 0;
foreach ($_SESSION['cart'] as $entry) {
    $je_cart_count += $entry['qty'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>J.E Essentials Shop</title>
    <link rel="stylesheet" href="./shop_css/shop.css">
</head>
<body>

<header class="site-nav">
    <div class="site-nav-inner">
        <a href="index.php" class="logo">
          <img src="images/je_logo.svg" alt="">
        </a>

        <nav class="main-nav" id="mainNav">
            <ul>
                <li><a href="index.php#top">HOME</a></li>
                <li><a href="index.php#about">ABOUT US</a></li>
                <li><a href="index.php#app">OUR APP</a></li>
                <li><a href="index.php#app">CONTACT US</a></li>
                <li class="nav-mobile-contact"><a href="index.php#contact">CONTACT US</a></li>
            </ul>
        </nav>

        <div class="shop-cart-nav">
            <a href="#" class="cart-btn" id="cartTrigger" aria-label="View cart">
                <img src="./images/cart-plus-svgrepo-com.svg" alt="">
                <span class="cart-count" id="cartCount"><?= (int)$je_cart_count ?></span>
            </a>

            <?php if ($je_user): ?>
                <div class="profile-nav" id="profileNav">
                    <button class="profile-btn" id="profileTrigger" aria-haspopup="true" aria-expanded="false">
                        <span class="profile-avatar"><?= htmlspecialchars(strtoupper(substr($je_user['username'], 0, 1))) ?></span>
                        <span class="profile-name"><?= htmlspecialchars($je_user['username']) ?></span>
                    </button>
                    <div class="profile-dropdown" id="profileDropdown">
                        <a href="dashboard.php">Dashboard</a>
                        <a href="profile.php">Profile</a>
                        <a href="settings.php">Settings</a>
                        <hr>
                        <a href="logout.php">Log Out</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="login.php?redirect=<?= urlencode(basename($_SERVER['PHP_SELF'])) ?>" class="account-link">Log In</a>
            <?php endif; ?>

          
            <button class="nav-toggle" id="navToggle" aria-label="Toggle menu" aria-expanded="false">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </div>
</header>

 <main id="top">

        <section class="hero">
            <div class="hero-copy">
                <p class="hero-kicker">New Products</p>
                <h1>Joy and Elegance in Every Touch</h1>
                <p class="hero-lede">
                    A minimalist skincare approach engineered with clean, powerful plant-based
                    botanical complexes to reveal your skin’s raw, authentic radiance.
                </p>
                <a href="#products-heading" class="btn-primary">See our products</a>
            </div>
            <div class="hero-visual" aria-hidden="true">
                <div class="hero-card hero-card-1"><img class="hero-card hero-card-1" src="images/rectangle_3.svg" alt=""></div>
                <div class="hero-card hero-card-2"><img class="hero-card hero-card-1" src="images/rectangle_66.svg" alt=""></div>
                <div class="hero-card hero-card-3"><img class="hero-card hero-card-1" src="images/reactangle_2.svg" alt=""></div>
            </div>
        </section>

        <section class="products" aria-labelledby="products-heading">
            <div class="section-heading-row">
                <h2 id="products-heading">Skincare Products</h2>
            </div>

            <div class="product-grid">
                <?php foreach ($products as $i => $product): ?>
                    <?php
                        $firstSize = array_key_first($product['sizes']);
                        $firstPrice = $product['sizes'][$firstSize];
                    ?>
                    <article class="product-card">
                        <figure class="product-image product-image-<?= $i ?>"><img src="<?= htmlspecialchars($product['image']) ?>" alt=""></figure>
                        <div class="product-info">
                            <h3><?= htmlspecialchars($product['name']) ?></h3>
                            <p class="product-price">₱<?= number_format($firstPrice) ?></p>
                        </div>
                        <button class="btn-add" data-product-id="<?= $product['id'] ?>" aria-label="Add <?= htmlspecialchars($product['name']) ?> to cart">Add to cart</button>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="story" id="story" aria-labelledby="story-heading">
            <div class="story-visual" aria-hidden="true"><img src="images/rectangle_5.svg" alt=""></div>
            <div class="story-copy">
                <h2 id="story-heading">Made to be used, not displayed</h2>
                <p>
                    We started J.E Essentials because we kept buying things
                    that looked good in photos and felt disappointing at home.
                    Everything here is tested in our own kitchens and bathrooms
                    first — if it doesn't earn its place on the counter, it
                    doesn't make it to the shop.
                </p>
                <p>
                    Every piece is sourced from small workshops we've visited
                    ourselves, working with clay, cotton, and soy wax rather
                    than synthetics.
                </p>
            </div>
        </section>

        <section class="newsletter" id="contact" aria-labelledby="newsletter-heading">
            <h2 id="newsletter-heading">Get first look at new drops</h2>
            <p>One email a month. No noise, just what's new and what's back in stock.</p>
            <form class="newsletter-form">
                <label for="newsletter-email" class="visually-hidden">Email address</label>
                <input type="email" id="newsletter-email" name="email" placeholder="you@email.com" required>
                <button type="submit">Subscribe</button>
            </form>
        </section>

    </main>

<!-- ============ Product detail modal ============ -->
<div class="modal-overlay" id="productModalOverlay">
    <div class="modal product-modal" role="dialog" aria-modal="true" aria-labelledby="productModalTitle">
        <button class="modal-close" id="productModalClose" aria-label="Close">&times;</button>
        <div class="product-modal-body">
            <div class="zoom-wrap" id="zoomWrap">
                <div class="zoom-frame" id="zoomFrame">
                    <img id="productModalImage" src="" alt="">
                    <div class="zoom-pane" id="zoomPane"></div>
                </div>
                <p class="zoom-hint">Hover image to zoom</p>
            </div>

            <div class="product-modal-details">
                <h2 id="productModalTitle"></h2>
                <p class="product-modal-price" id="productModalPrice"></p>
                <p class="product-modal-desc" id="productModalDesc"></p>

                <div class="option-group">
                    <span class="option-label">Size</span>
                    <div class="size-options" id="sizeOptions"></div>
                </div>

                <div class="option-group">
                    <span class="option-label">Quantity</span>
                    <div class="qty-stepper">
                        <button type="button" class="qty-btn" id="qtyMinus" aria-label="Decrease quantity">−</button>
                        <input type="number" id="qtyInput" value="1" min="1" max="20" inputmode="numeric">
                        <button type="button" class="qty-btn" id="qtyPlus" aria-label="Increase quantity">+</button>
                    </div>
                </div>

                <button type="button" class="btn-primary modal-add-btn" id="modalAddToCart">Add to Cart</button>
                <p class="modal-add-error" id="modalAddError"></p>
            </div>
        </div>
    </div>
</div>

<!-- ============ Cart modal ============ -->
<div class="modal-overlay" id="cartModalOverlay">
    <div class="modal cart-modal" role="dialog" aria-modal="true" aria-labelledby="cartModalTitle">
        <button class="modal-close" id="cartModalClose" aria-label="Close">&times;</button>
        <h2 id="cartModalTitle">Your Cart</h2>

        <div class="cart-items" id="cartItems">
            <p class="cart-empty" id="cartEmptyMsg">Your cart is empty.</p>
        </div>

        <div class="cart-summary">
            <div class="cart-subtotal-row">
                <span>Subtotal</span>
                <span id="cartSubtotal">₱0</span>
            </div>
            <button type="button" class="btn-primary cart-checkout-btn" id="cartCheckoutBtn">Checkout</button>
        </div>
    </div>
</div>

<!-- ============ Tiny "added to cart" toast ============ -->
<div class="cart-toast" id="cartToast">Added to cart</div>

<footer class="site-footer">
    <div class="footer-inner">
        <div class=" footer-about">
            <div class="brand-footer">
            <img src="images/footer_icon.svg" alt="">
            <p class="logo-text footer-logo">J.E EULLARAN ESSENTIALS</p>
            </div>
            <p class="brand-footer-subtxt">A soft-luxe approach to skincare rituals, aligning <br> active botanicals with clean
            modern dermatologist <br>science to celebrate your skin&rsquo;s raw radiance.</p>
            <ul class="social-list" >
                <li><a href="#top" ><img src="images/insta-icon.svg" alt=""></a></li>
                <li><a href="#top" ><img src="images/fb-icon.svg" alt=""></a></li>
                <li><a href="#top" ><img src="images/yt1.svg" alt=""></a></li>
                <li><a href="#top"><img src="images/x-icon.svg" alt=""></a></li>
            </ul>
        </div>

        <nav class="footer-links">
            <h3>QUICK LINKS</h3>
            <ul>
                <li><a href="#top">Home</a></li>
                <li><a href="#about">About Us</a></li>
                <li><a href="#featured">Featured Products</a></li>
                <li><a href="#app">Our App</a></li>
            </ul>
        </nav>

        <nav class="footer-links" >
            <h3>HOUSE OF J.E</h3>
            <ul>
                <li><a href="#about">Our Journey</a></li>
                <li><a href="#top">Testimonials</a></li>
                <li><a href="shop.php">Our Shop</a></li>
            </ul>
        </nav>

        <address class=" footer-contact">
            <h3>CONTACT DETAILS</h3>
            <p>Calindagan, Dumaguete City, Philippines</p>
            <p><a href="mailto:jujilleullaranessentials@gmail.com">jujilleullaranessentials@gmail.com</a></p>
            <p><a href="tel:+639929858661">(+63) 992 985 8661</a></p>
        </address>
    </div>

    <hr>

    <div class="footer-bottom">
        <p>&copy; 2026 J.E Eullaran Essentials. All Rights Reserved.</p>
        <p>Designed for Quiet Luxury Skincare.</p>
    </div>
</footer>

<script>
    // Server-sourced product + auth data, handed to javascript.js.
    // Prices always come from PHP — the JS never invents a price.
    window.JE_PRODUCTS = <?= json_encode(array_values($products), JSON_UNESCAPED_SLASHES) ?>;
    window.JE_LOGGED_IN = <?= je_is_logged_in() ? 'true' : 'false' ?>;
</script>
<script src="javascript.js"></script>
</body>
</html>