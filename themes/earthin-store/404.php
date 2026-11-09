<?php
/**
 * 404 template for Earthin Store
 *
 * @package Earthin_Store
 */

get_header();
?>

<div class="es-container" style="padding: 6rem 1.5rem; text-align: center;">
    <span style="font-size: 4.5rem; display: block; margin-bottom: 1rem;">🛒</span>
    <h1 style="font-size: 2.75rem; font-weight: 900; margin: 0 0 1rem;"><?php esc_html_e('Product Not Found', 'earthin-store'); ?></h1>
    <p style="font-size: 1.1rem; color: var(--es-text-muted); max-width: 500px; margin: 0 auto 2rem;">
        <?php esc_html_e('The item or page you are looking for may have been moved, renamed, or is currently unavailable.', 'earthin-store'); ?>
    </p>
    <a href="<?php echo esc_url(home_url('/')); ?>" class="es-btn es-btn-primary">
        <?php esc_html_e('Return to Store Catalog', 'earthin-store'); ?>
    </a>
</div>

<?php
get_footer();
