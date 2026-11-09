<?php
/**
 * Archive template for Earthin Minimal
 *
 * @package Earthin_Minimal
 */

get_header();
?>

<main id="primary" class="em-site-main" role="main">
    <div class="em-container">

        <header class="em-archive-header" style="margin-bottom: 2.5rem; padding-bottom: 1rem; border-bottom: 1px solid var(--em-border);">
            <h1 class="em-archive-title" style="font-size: 2rem; font-weight: 800; margin: 0 0 0.5rem;"><?php the_archive_title(); ?></h1>
            <?php the_archive_description('<div class="em-archive-desc" style="color: var(--em-text-muted);">', '</div>'); ?>
        </header>

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
                <h2><?php esc_html_e('No articles found in this archive', 'earthin-minimal'); ?></h2>
            </div>

        <?php endif; ?>

    </div>
</main>

<?php
get_footer();
