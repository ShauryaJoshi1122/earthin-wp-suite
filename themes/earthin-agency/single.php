<?php
/**
 * Single post template for Earthin Agency
 *
 * @package Earthin_Agency
 */

get_header();
?>

<div class="ea-container" style="padding-top: 4rem; padding-bottom: 5rem;">

    <?php while (have_posts()) : the_post(); ?>

        <article id="post-<?php the_ID(); ?>" <?php post_class('ea-single-article'); ?>>

            <header style="margin-bottom: 2.5rem; text-align: center; max-width: 820px; margin-left: auto; margin-right: auto;">
                <span class="ea-section-badge"><?php esc_html_e('AGENCY DISPATCH', 'earthin-agency'); ?></span>
                <h1 style="font-size: 2.75rem; font-weight: 900; line-height: 1.2; margin: 0.5rem 0 1rem; color: #ffffff;"><?php the_title(); ?></h1>
                <div style="color: var(--ea-text-muted); font-size: 0.9rem;">
                    <time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date()); ?></time>
                    <span>•</span>
                    <span><?php the_author(); ?></span>
                </div>
            </header>

            <?php if (has_post_thumbnail()) : ?>
                <div style="border-radius: var(--ea-radius); overflow: hidden; margin-bottom: 3rem; max-width: 900px; margin-left: auto; margin-right: auto;">
                    <?php the_post_thumbnail('large', ['style' => 'width: 100%; height: auto; display: block;']); ?>
                </div>
            <?php endif; ?>

            <div class="ea-entry-content" style="max-width: 800px; margin: 0 auto 4rem; background: var(--ea-surface); border: 1px solid var(--ea-border); border-radius: var(--ea-radius); padding: 3rem; line-height: 1.85; font-size: 1.05rem;">
                <?php
                the_content();

                wp_link_pages([
                    'before' => '<div class="page-links">' . esc_html__('Pages:', 'earthin-agency'),
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

<?php
get_footer();
