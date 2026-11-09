<?php
/**
 * Archive template for Earthin theme
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
        <header class="page-header">
            <?php
            the_archive_title('<h1 class="page-title entry-title">', '</h1>');
            the_archive_description('<div class="archive-description">', '</div>');
            ?>
        </header>

        <?php
        while (have_posts()) :
            the_post();
            ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <header class="entry-header">
                    <?php the_title('<h2 class="entry-title"><a href="' . esc_url(get_permalink()) . '" rel="bookmark">', '</a></h2>'); ?>
                    <div class="entry-meta">
                        <?php
                        earthin_posted_on();
                        earthin_posted_by();
                        ?>
                    </div>
                </header>

                <?php if (has_post_thumbnail()) : ?>
                    <div class="post-thumbnail">
                        <a href="<?php the_permalink(); ?>">
                            <?php the_post_thumbnail('large'); ?>
                        </a>
                    </div>
                <?php endif; ?>

                <div class="entry-content">
                    <?php the_excerpt(); ?>
                </div>

                <footer class="entry-footer" style="margin-top: 1rem;">
                    <a href="<?php the_permalink(); ?>" class="earthin-read-more">
                        <?php esc_html_e('Read More &rarr;', 'earthin'); ?>
                    </a>
                </footer>
            </article>
            <?php
        endwhile;

        the_posts_pagination([
            'prev_text' => esc_html__('&larr; Previous', 'earthin'),
            'next_text' => esc_html__('Next &rarr;', 'earthin'),
        ]);

    else :
        ?>
        <section class="no-results not-found">
            <header class="page-header">
                <h1 class="page-title"><?php esc_html_e('Nothing Found', 'earthin'); ?></h1>
            </header>
            <div class="page-content">
                <p><?php esc_html_e('It seems we cannot find what you are looking for.', 'earthin'); ?></p>
            </div>
        </section>
        <?php
    endif;
    ?>
</main>

<?php
get_sidebar();
get_footer();
