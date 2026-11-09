<?php
/**
 * Search results template for Earthin Store
 *
 * @package Earthin_Store
 */

get_header();
?>

<div class="es-container" style="padding-top: 3rem; padding-bottom: 5rem;">

    <header style="margin-bottom: 2.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--es-border);">
        <h1 style="font-size: 2rem; font-weight: 900; margin: 0 0 0.5rem;">
            <?php printf(esc_html__('Store Results for: %s', 'earthin-store'), '<span>' . get_search_query() . '</span>'); ?>
        </h1>
    </header>

    <div class="es-product-grid">
        <?php
        if (have_posts()) :
            while (have_posts()) : the_post();
        ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class('es-product-card'); ?>>
                    <div class="es-product-thumb">
                        <?php if (has_post_thumbnail()) : ?>
                            <a href="<?php the_permalink(); ?>"><?php the_post_thumbnail('medium'); ?></a>
                        <?php else : ?>
                            <span class="es-product-placeholder-icon">📦</span>
                        <?php endif; ?>
                    </div>
                    <div class="es-product-body">
                        <h3 class="es-product-name"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                        <p class="es-product-desc"><?php echo esc_html(wp_trim_words(get_the_excerpt(), 14)); ?></p>
                        <div class="es-product-footer">
                            <span class="es-price-current">$29</span>
                            <a href="<?php the_permalink(); ?>" class="es-buy-btn"><?php esc_html_e('View Item', 'earthin-store'); ?> →</a>
                        </div>
                    </div>
                </article>
        <?php
            endwhile;
        else :
        ?>
            <p><?php esc_html_e('No products matched your search. Try another keyword.', 'earthin-store'); ?></p>
        <?php endif; ?>
    </div>

</div>

<?php
get_footer();
