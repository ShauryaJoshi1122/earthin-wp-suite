<?php
/**
 * Template Name: Left Sidebar
 * Template Post Type: page, post
 *
 * @package Earthin
 * @author Shaurya Joshi
 * @link https://plugins.earthin.online
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<!-- Render Sidebar on the left -->
<?php get_sidebar(); ?>

<main id="primary" class="site-main earthin-left-sidebar-main">
    <?php
    while (have_posts()) :
        the_post();
        ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class('earthin-left-sidebar-article'); ?>>
            <header class="entry-header">
                <?php the_title('<h1 class="entry-title">', '</h1>'); ?>
            </header>

            <?php if (has_post_thumbnail()) : ?>
                <div class="post-thumbnail" style="margin-bottom: 2rem;">
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

            <?php if (get_edit_post_link()) : ?>
                <footer class="entry-footer" style="margin-top: 2rem; padding-top: 1rem; border-top: 1px solid var(--earthin-border);">
                    <?php
                    edit_post_link(
                        sprintf(
                            wp_kses(
                                __('Edit <span class="screen-reader-text">%s</span>', 'earthin'),
                                [
                                    'span' => [
                                        'class' => [],
                                    ],
                                ]
                            ),
                            wp_kses_post(get_the_title())
                        ),
                        '<span class="edit-link">',
                        '</span>'
                    );
                    ?>
                </footer>
            <?php endif; ?>
        </article>

        <?php
        if (comments_open() || get_comments_number()) :
            comments_template();
        endif;

    endwhile;
    ?>
</main>

<?php
get_footer();
