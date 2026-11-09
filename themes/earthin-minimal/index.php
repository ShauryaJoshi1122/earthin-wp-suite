<?php
/**
 * Index template for Earthin Minimal
 *
 * @package Earthin_Minimal
 */

get_header();
?>

<main id="primary" class="em-site-main" role="main">
    <div class="em-container">

        <?php if (have_posts()) : ?>

            <div class="em-post-list">
                <?php
                while (have_posts()) : the_post();
                ?>
                    <article id="post-<?php the_ID(); ?>" <?php post_class('em-post-card'); ?>>
                        <div class="em-post-meta">
                            <time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date()); ?></time>
                            <span>•</span>
                            <span><?php echo esc_html(earthin_minimal_reading_time()); ?></span>
                        </div>
                        <h2 class="em-post-title">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h2>
                        <div class="em-post-excerpt">
                            <?php the_excerpt(); ?>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>

            <div class="em-pagination">
                <div><?php previous_posts_link(__('← Newer Articles', 'earthin-minimal')); ?></div>
                <div><?php next_posts_link(__('Older Articles →', 'earthin-minimal')); ?></div>
            </div>

        <?php else : ?>

            <div class="em-empty-state">
                <h2><?php esc_html_e('No articles published yet', 'earthin-minimal'); ?></h2>
                <p><?php esc_html_e('Check back soon for upcoming essays and updates.', 'earthin-minimal'); ?></p>
            </div>

        <?php endif; ?>

    </div>
</main>

<?php
get_footer();
