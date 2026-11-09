<?php
/**
 * Header template for Earthin Minimal
 *
 * @package Earthin_Minimal
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

<?php if (is_singular('post')) : ?>
    <div class="em-reading-progress-bar" aria-hidden="true"></div>
<?php endif; ?>

<header class="em-header" role="banner">
    <div class="em-container">
        <div class="em-header-inner">
            <div class="em-site-branding">
                <h1 class="em-site-title"><a href="<?php echo esc_url(home_url('/')); ?>"><?php bloginfo('name'); ?></a></h1>
                <?php $desc = get_bloginfo('description', 'display'); if ($desc) : ?>
                    <p class="em-site-description"><?php echo esc_html($desc); ?></p>
                <?php endif; ?>
            </div>

            <div class="em-nav-wrap">
                <nav class="em-primary-nav" role="navigation" aria-label="<?php esc_attr_e('Main Navigation', 'earthin-minimal'); ?>">
                    <?php
                    wp_nav_menu([
                        'theme_location' => 'primary',
                        'container'      => false,
                        'fallback_cb'    => false,
                    ]);
                    ?>
                </nav>

                <button type="button" class="em-theme-toggle" onclick="toggleEarthinTheme()" aria-label="<?php esc_attr_e('Toggle Theme Mode', 'earthin-minimal'); ?>">
                    <span id="emThemeIcon">🌓</span>
                </button>
            </div>
        </div>
    </div>
</header>

<script>
function toggleEarthinTheme() {
    var cur = document.documentElement.getAttribute('data-theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
    var nxt = cur === 'dark' ? 'light' : 'dark';
    document.documentElement.setAttribute('data-theme', nxt);
    try { localStorage.setItem('em_theme_pref', nxt); } catch (e) {}
}
(function() {
    try {
        var saved = localStorage.getItem('em_theme_pref');
        if (saved) document.documentElement.setAttribute('data-theme', saved);
    } catch(e) {}
})();
</script>
