<?php
/**
 * Archive template for Earthin Agency
 *
 * @package Earthin_Agency
 */

get_header();
?>

<div class="ea-container" style="padding-top: 4rem; padding-bottom: 5rem;">

    <header style="margin-bottom: 3rem; text-align: center;">
        <span class="ea-section-badge"><?php esc_html_e('ARCHIVES', 'earthin-agency'); ?></span>
        <h1 style="font-size: 2.5rem; font-weight: 900; color: #ffffff; margin: 0 0 0.5rem;"><?php the_archive_title(); ?></h1>
        <?php the_archive_description('<p style="color: var(--ea-text-muted); margin: 0;">', '</p>'); ?>
    </header>

    <div class="ea-work-grid">
        <?php
        if (have_posts()) :
            while (have_posts()) : the_post();
        ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class('ea-project-card'); ?>>
                    <div class="ea-project-media">
                        <?php if (has_post_thumbnail()) : ?>
                            <a href="<?php the_permalink(); ?>"><?php the_post_thumbnail('medium_large'); ?></a>
                        <?php else : ?>
                            <span>⚡</span>
                        <?php endif; ?>
                    </div>
                    <div class="ea-project-body">
                        <h3 class="ea-project-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                        <p class="ea-project-desc"><?php echo esc_html(wp_trim_words(get_the_excerpt(), 16)); ?></p>
                        <a href="<?php the_permalink(); ?>" style="color: var(--ea-primary); font-weight: 700; text-decoration: none; font-size: 0.9rem;">
                            <?php esc_html_e('Read Article', 'earthin-agency'); ?> →
                        </a>
                    </div>
                </article>
        <?php
            endwhile;
        else :
        ?>
            <p><?php esc_html_e('No articles found in this archive.', 'earthin-agency'); ?></p>
        <?php endif; ?>
    </div>

    <div style="display: flex; justify-content: space-between; margin-top: 3rem;">
        <?php previous_posts_link(__('← Previous', 'earthin-agency')); ?>
        <?php next_posts_link(__('Next →', 'earthin-agency')); ?>
    </div>

</div>

<?php
get_footer();
