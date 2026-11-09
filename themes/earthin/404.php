<?php
/**
 * 404 template for Earthin theme
 *
 * @package Earthin
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<main id="primary" class="site-main" style="text-align:center; padding: 3rem 0;">
    <section class="error-404 not-found">
        <header class="page-header">
            <h1 class="page-title entry-title" style="font-size: 4rem; color: var(--earthin-primary); margin-bottom: 0.5rem;">404</h1>
            <h2 style="margin-top: 0; color: var(--earthin-dark);"><?php esc_html_e('Oops! That page can’t be found.', 'earthin'); ?></h2>
        </header>

        <div class="page-content" style="max-width: 500px; margin: 1.5rem auto;">
            <p><?php esc_html_e('It looks like nothing was found at this location. Maybe try searching for what you need?', 'earthin'); ?></p>
            <?php get_search_form(); ?>
        </div>
    </section>
</main>

<?php
get_footer();
