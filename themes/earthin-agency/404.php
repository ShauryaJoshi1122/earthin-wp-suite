<?php
/**
 * 404 template for Earthin Agency
 *
 * @package Earthin_Agency
 */

get_header();
?>

<div class="ea-container" style="padding: 7rem 1.5rem; text-align: center;">
    <span style="font-size: 5rem; display: block; margin-bottom: 1.5rem;">🌌</span>
    <h1 style="font-size: 3rem; font-weight: 900; color: #ffffff; margin: 0 0 1rem;"><?php esc_html_e('404: Route Not Found', 'earthin-agency'); ?></h1>
    <p style="font-size: 1.15rem; color: var(--ea-text-muted); max-width: 520px; margin: 0 auto 2.5rem; line-height: 1.6;">
        <?php esc_html_e('The requested endpoint or resource could not be found. Head back to our main agency hub or explore recent work.', 'earthin-agency'); ?>
    </p>
    <a href="<?php echo esc_url(home_url('/')); ?>" class="ea-btn ea-btn-primary">
        <?php esc_html_e('Return to Agency Homepage', 'earthin-agency'); ?> →
    </a>
</div>

<?php
get_footer();
