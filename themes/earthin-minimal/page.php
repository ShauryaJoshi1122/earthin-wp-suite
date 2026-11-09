<?php
/**
 * Page template for Earthin Minimal
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
