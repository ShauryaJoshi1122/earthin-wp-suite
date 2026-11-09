<?php
/**
 * Search results template for Earthin Minimal
 *
 * @package Earthin_Minimal
 */

get_header();
?>

<main id="primary" class="em-site-main" role="main">
    <div class="em-container">

        <header class="em-search-header" style="margin-bottom: 2.5rem; padding-bottom: 1rem; border-bottom: 1px solid var(--em-border);">
            <h1 class="em-search-title" style="font-size: 1.85rem; font-weight: 800; margin: 0 0 0.5rem;">
                <?php printf(esc_html__('Search Results for: %s', 'earthin-minimal'), '<span>' . get_search_query() . '</span>'); ?>
            </h1>
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
                <div><?php previous_posts_link(__('← Previous', 'earthin-minimal')); ?></div>
                <div><?php next_posts_link(__('Next →', 'earthin-minimal')); ?></div>
            </div>

        <?php else : ?>

            <div class="em-empty-state">
                <h2><?php esc_html_e('Nothing Found', 'earthin-minimal'); ?></h2>
                <p><?php esc_html_e('Sorry, but nothing matched your search terms. Please try again with different keywords.', 'earthin-minimal'); ?></p>
                <?php get_search_form(); ?>
            </div>

        <?php endif; ?>

    </div>
</main>

<?php
get_footer();
