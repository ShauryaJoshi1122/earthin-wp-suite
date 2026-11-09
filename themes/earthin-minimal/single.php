<?php
/**
 * Single post template for Earthin Minimal
 *
 * @package Earthin_Minimal
 */

get_header();
?>

<main id="primary" class="em-site-main" role="main">
    <div class="em-container">

        <?php
        while (have_posts()) : the_post();
        ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class('em-single-article'); ?>>

                <header class="em-article-header">
                    <h1 class="em-article-title"><?php the_title(); ?></h1>
                    <div class="em-article-meta-bar">
                        <time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date()); ?></time>
                        <span>•</span>
                        <span><?php the_author(); ?></span>
                        <span>•</span>
                        <span><?php echo esc_html(earthin_minimal_reading_time()); ?></span>
                    </div>
                </header>

                <?php if (has_post_thumbnail()) : ?>
                    <div class="em-article-thumb">
                        <?php the_post_thumbnail('large'); ?>
                    </div>
                <?php endif; ?>

                <div class="em-entry-content">
                    <?php
                    the_content();

                    wp_link_pages([
                        'before' => '<div class="page-links">' . esc_html__('Pages:', 'earthin-minimal'),
                        'after'  => '</div>',
                    ]);
                    ?>
                </div>

                <div class="em-author-box">
                    <div class="em-author-avatar">
                        <?php echo get_avatar(get_the_author_meta('ID'), 64); ?>
                    </div>
                    <div class="em-author-info">
                        <h4 class="em-author-name"><?php the_author(); ?></h4>
                        <p class="em-author-bio"><?php echo esc_html(get_the_author_meta('description') ?: __('Writer, essayist, and independent contributor.', 'earthin-minimal')); ?></p>
                    </div>
                </div>

                <?php
                if (comments_open() || get_comments_number()) :
                    comments_template();
                endif;
                ?>

            </article>
        <?php endwhile; ?>

    </div>
</main>

<?php
get_footer();
