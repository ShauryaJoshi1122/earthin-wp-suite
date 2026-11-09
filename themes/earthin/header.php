<?php
/**
 * Header template for Earthin theme
 *
 * @package Earthin
 */

if (!defined('ABSPATH')) {
    exit;
}
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>

<body <?php body_class(is_active_sidebar('sidebar-1') ? 'has-sidebar' : 'no-sidebar'); ?>>
<?php wp_body_open(); ?>

<div id="page" class="site">
    <a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e('Skip to content', 'earthin'); ?></a>

    <header id="masthead" class="site-header">
        <div class="earthin-container site-header-inner">
            <div class="site-branding">
                <?php
                if (has_custom_logo()) {
                    the_custom_logo();
                } else {
                    ?>
                    <h1 class="site-title"><a href="<?php echo esc_url(home_url('/')); ?>" rel="home"><?php bloginfo('name'); ?></a></h1>
                    <?php
                    $description = get_bloginfo('description', 'display');
                    if ($description || is_customize_preview()) {
                        echo '<p class="site-description">' . esc_html($description) . '</p>';
                    }
                }
                ?>
            </div>

            <nav id="site-navigation" class="main-navigation" aria-label="<?php esc_attr_e('Primary Menu', 'earthin'); ?>">
                <button class="menu-toggle" aria-controls="primary-menu" aria-expanded="false">
                    ☰ <span class="screen-reader-text"><?php esc_html_e('Primary Menu', 'earthin'); ?></span>
                </button>
                <?php
                wp_nav_menu([
                    'theme_location' => 'primary',
                    'menu_id'        => 'primary-menu',
                    'fallback_cb'    => false,
                    'container'      => false,
                ]);
                ?>
            </nav>
        </div>
    </header>

    <div id="content" class="site-content">
        <div class="earthin-container">
            <div class="content-area-wrap">
