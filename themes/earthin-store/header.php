<?php
/**
 * Header template for Earthin Store
 *
 * @package Earthin_Store
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<!-- Top Announcement -->
<div class="es-announcement-bar">
    <div class="es-container">
        <span><?php esc_html_e('SPECIAL PROMO', 'earthin-store'); ?></span>
        <?php esc_html_e('Use code EARTHIN30 for 30% off any plugin or bundle license today.', 'earthin-store'); ?>
    </div>
</div>

<header class="es-header" role="banner">
    <div class="es-container">
        <div class="es-header-inner">
            <div class="es-logo-title">
                <?php if (has_custom_logo()) : the_custom_logo(); else : ?>
                    <a href="<?php echo esc_url(home_url('/')); ?>"><?php bloginfo('name'); ?><span>.store</span></a>
                <?php endif; ?>
            </div>

            <nav class="es-nav" role="navigation" aria-label="<?php esc_attr_e('Store Navigation', 'earthin-store'); ?>">
                <?php
                wp_nav_menu([
                    'theme_location' => 'primary',
                    'container'      => false,
                    'fallback_cb'    => false,
                ]);
                ?>
            </nav>

            <div class="es-header-actions">
                <div class="es-upi-badge" title="<?php esc_attr_e('Instant zero-fee checkout available', 'earthin-store'); ?>">
                    <span>⚡</span> <?php esc_html_e('UPI QR Ready', 'earthin-store'); ?>
                </div>
                <button type="button" class="es-cart-btn" onclick="alert('<?php echo esc_js(__('Cart is currently empty. Browse our store items below!', 'earthin-store')); ?>')">
                    <span>🛒</span> <?php esc_html_e('Cart (0)', 'earthin-store'); ?>
                </button>
            </div>
        </div>
    </div>
</header>
