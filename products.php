<?php
/**
 * Single source of truth for product data.
 * Both shop.php (to render the page + hand data to JS) and
 * cart_actions.php (to validate prices server-side) include this file,
 * so prices can never be tampered with from the browser.
 *
 * Swap this out for a database query later — the array shape is all
 * that matters to the rest of the code.
 */

return [
    1 => [
        'id'          => 1,
        'name'        => 'Cream Moisturizer',
        'image'       => 'images/first-png.svg',
        'description' => 'A lightweight, fast-absorbing moisturizer built around clean botanical actives. Softens and hydrates without leaving a heavy or greasy after-feel — made for daily use, morning and night.',
        'sizes'       => [
            '30ml'  => 149,
            '50ml'  => 199,
            '100ml' => 329,
        ],
    ],
    2 => [
        'id'          => 2,
        'name'        => 'Shea Butter Scrub',
        'image'       => 'images/second-png.svg',
        'description' => 'A rich exfoliating scrub blending raw shea butter with fine botanical granules. Buffs away dry, dull skin while leaving behind a soft, conditioned finish.',
        'sizes'       => [
            '100ml' => 299,
            '200ml' => 499,
        ],
    ],
    3 => [
        'id'          => 3,
        'name'        => 'Whitening Lotion',
        'image'       => 'images/third-png.svg',
        'description' => 'A brightening body lotion formulated with plant-based actives to even out tone and texture over time. Non-sticky, fast-absorbing, and safe for daily application.',
        'sizes'       => [
            '100ml' => 399,
            '250ml' => 749,
        ],
    ],
    4 => [
        'id'          => 4,
        'name'        => 'Rejuvinating Spray',
        'image'       => 'images/fouth-png.svg',
        'description' => 'A fine botanical mist that refreshes and revives skin throughout the day. Mist onto clean or made-up skin for an instant boost of hydration and radiance.',
        'sizes'       => [
            '50ml'  => 249,
            '100ml' => 429,
        ],
    ],
];