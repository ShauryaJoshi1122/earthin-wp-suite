<?php
/**
 * Search results template for Earthin theme
 *
 * @package Earthin
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<main id="primary" class="site-main">
    <?php if (have_posts()) : ?>
        <header class="page-header" style="margin-bottom: 2.5rem; border-bottom: 1px solid var(--earthin-border); padding-bottom: 1.5rem;">
            <h1 class="page-title entry-title">
                <?php
                /* translators: %s: search query. */
                printf(esc_html__('Search Results for: %s', 'earthin'), '<span>' . get_search_query() . '</span>');
                ?>
            </h1>
        </header>

        <?php
        while (have_posts()) :
            the_post();
            ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <header class="entry-header">
                    <?php the_title('<h2 class="entry-title"><a href="' . esc_url(get_permalink()) . '" rel="bookmark">', '</a></h2>'); ?>
                </header>
                <div class="entry-content">
                    <?php the_excerpt(); ?>
                </div>
            </article>
            <?php
        endwhile;

        the_posts_pagination();

    else :
        ?>
        <section class="no-results not-found">
            <header class="page-header">
                <h1 class="page-title"><?php esc_html_e('No Matches Found', 'earthin'); ?></h1>
            </header>
            <div class="page-content">
                <p><?php esc_html_e('Sorry, but nothing matched your search terms. Please try again with some different keywords.', 'earthin'); ?></p>
                <?php get_search_form(); ?>
            </div>
        </section>
        <?php
    endif;
    ?>
</main>

<?php
get_sidebar();
get_footer();
