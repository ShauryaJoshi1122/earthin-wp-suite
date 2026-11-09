<?php
/**
 * 404 template for Earthin Minimal
 *
 * @package Earthin_Minimal
 */

get_header();
?>

<main id="primary" class="em-site-main" role="main">
    <div class="em-container" style="text-align: center; padding: 5rem 1.5rem;">
        <span style="font-size: 4rem; display: block; margin-bottom: 1rem;">🍃</span>
        <h1 style="font-size: 2.5rem; font-weight: 900; margin: 0 0 1rem;"><?php esc_html_e('404: Page Not Found', 'earthin-minimal'); ?></h1>
        <p style="font-size: 1.1rem; color: var(--em-text-muted); max-width: 480px; margin: 0 auto 2rem;">
            <?php esc_html_e('It looks like nothing was found at this location. Perhaps try a search or return to the homepage?', 'earthin-minimal'); ?>
        </p>
        <a href="<?php echo esc_url(home_url('/')); ?>" style="display: inline-block; background: var(--em-primary); color: #fff; padding: 0.75rem 1.5rem; border-radius: var(--em-radius); text-decoration: none; font-weight: 700;">
            <?php esc_html_e('Return to Home', 'earthin-minimal'); ?>
        </a>
    </div>
</main>

<?php
get_footer();
