<?php
/**
 * Single post template for Earthin theme
 *
 * @package Earthin
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<main id="primary" class="site-main">
    <?php
    while (have_posts()) :
        the_post();
        ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <header class="entry-header">
                <?php the_title('<h1 class="entry-title">', '</h1>'); ?>
                <div class="entry-meta">
                    <?php
                    earthin_posted_on();
                    earthin_posted_by();
                    ?>
                </div>
            </header>

            <?php if (has_post_thumbnail()) : ?>
                <div class="post-thumbnail">
                    <?php the_post_thumbnail('large'); ?>
                </div>
            <?php endif; ?>

            <div class="entry-content">
                <?php
                the_content();

                wp_link_pages([
                    'before' => '<div class="page-links">' . esc_html__('Pages:', 'earthin'),
                    'after'  => '</div>',
                ]);
                ?>
            </div>

            <footer class="entry-footer" style="margin-top: 2rem; padding-top: 1rem; border-top: 1px solid var(--earthin-border);">
                <?php earthin_entry_footer(); ?>
            </footer>
        </article>

        <?php
        the_post_navigation([
            'prev_text' => '<span class="nav-subtitle">' . esc_html__('Previous:', 'earthin') . '</span> <span class="nav-title">%title</span>',
            'next_text' => '<span class="nav-subtitle">' . esc_html__('Next:', 'earthin') . '</span> <span class="nav-title">%title</span>',
        ]);

        if (comments_open() || get_comments_number()) :
            comments_template();
        endif;

    endwhile;
    ?>
</main>

<?php
get_sidebar();
get_footer();
