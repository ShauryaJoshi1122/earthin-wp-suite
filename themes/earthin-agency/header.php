<?php
/**
 * Header template for Earthin Agency
 *
 * @package Earthin_Agency
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

<header class="ea-header" role="banner">
    <div class="ea-container">
        <div class="ea-header-inner">
            <div class="ea-logo">
                <?php if (has_custom_logo()) : the_custom_logo(); else : ?>
                    <a href="<?php echo esc_url(home_url('/')); ?>"><?php bloginfo('name'); ?><span>.agency</span></a>
                <?php endif; ?>
            </div>

            <nav class="ea-nav" role="navigation" aria-label="<?php esc_attr_e('Agency Navigation', 'earthin-agency'); ?>">
                <?php
                wp_nav_menu([
                    'theme_location' => 'primary',
                    'container'      => false,
                    'fallback_cb'    => false,
                ]);
                ?>
            </nav>

            <div>
                <a href="#contact" class="ea-header-cta"><?php esc_html_e('Start a Project', 'earthin-agency'); ?> →</a>
            </div>
        </div>
    </div>
</header>
