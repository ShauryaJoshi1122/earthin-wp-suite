<?php
/**
 * Template Name: Longform / Distraction-Free Reading
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

<main id="primary" class="site-main earthin-reading-main">
    <?php
    while (have_posts()) :
        the_post();
        $content = get_the_content();
        $word_count = str_word_count(strip_tags($content));
        $reading_time = max(1, ceil($word_count / 200));
        ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class('earthin-reading-article'); ?>>
            <header class="entry-header earthin-reading-header">
                <div class="earthin-reading-meta-top">
                    <span class="earthin-reading-badge">📖 <?php printf(esc_html__('%d min read', 'earthin'), $reading_time); ?></span>
                    <span class="earthin-reading-date"><?php echo esc_html(get_the_date()); ?></span>
                </div>

                <?php the_title('<h1 class="entry-title earthin-reading-title">', '</h1>'); ?>

                <div class="earthin-reading-byline">
                    <span class="byline-avatar"><?php echo get_avatar(get_the_author_meta('ID'), 44); ?></span>
                    <div class="byline-details">
                        <span class="author-name"><?php the_author_posts_link(); ?></span>
                        <span class="author-title"><?php esc_html_e('Published in Earthin Publication', 'earthin'); ?></span>
                    </div>
                </div>
            </header>

            <?php if (has_post_thumbnail()) : ?>
                <div class="post-thumbnail earthin-reading-thumbnail">
                    <?php the_post_thumbnail('full'); ?>
                    <?php if (get_the_post_thumbnail_caption()) : ?>
                        <figcaption class="wp-caption-text"><?php echo esc_html(get_the_post_thumbnail_caption()); ?></figcaption>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="entry-content earthin-reading-content">
                <?php
                the_content();

                wp_link_pages([
                    'before' => '<div class="page-links">' . esc_html__('Pages:', 'earthin'),
                    'after'  => '</div>',
                ]);
                ?>
            </div>

            <!-- Author Card & Article Footer -->
            <footer class="entry-footer earthin-reading-footer">
                <div class="earthin-author-box">
                    <div class="author-avatar"><?php echo get_avatar(get_the_author_meta('ID'), 64); ?></div>
                    <div class="author-info">
                        <h3 class="author-heading"><?php printf(esc_html__('Written by %s', 'earthin'), get_the_author()); ?></h3>
                        <p class="author-bio">
                            <?php
                            $author_bio = get_the_author_meta('description');
                            echo !empty($author_bio) ? esc_html($author_bio) : esc_html__('Writer, creator, and contributor to this site.', 'earthin');
                            ?>
                        </p>
                    </div>
                </div>
            </footer>
        </article>

        <?php
        if (comments_open() || get_comments_number()) :
            ?>
            <div class="earthin-reading-comments-wrap">
                <?php comments_template(); ?>
            </div>
            <?php
        endif;

    endwhile;
    ?>
</main>

<?php
get_footer();
