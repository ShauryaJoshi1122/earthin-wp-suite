<?php
/**
 * Page template for Earthin Store
 *
 * @package Earthin_Store
 */

get_header();
?>

<div class="es-container" style="padding-top: 3rem; padding-bottom: 5rem;">

    <?php while (have_posts()) : the_post(); ?>

        <article id="post-<?php the_ID(); ?>" <?php post_class('es-page'); ?>>
            <header style="margin-bottom: 2rem;">
                <h1 style="font-size: 2.25rem; font-weight: 900; margin: 0;"><?php the_title(); ?></h1>
            </header>

            <div class="es-page-content" style="background: var(--es-surface); border: 1px solid var(--es-border); border-radius: var(--es-radius); padding: 2.5rem; line-height: 1.8;">
                <?php
                the_content();

                wp_link_pages([
                    'before' => '<div class="page-links">' . esc_html__('Pages:', 'earthin-store'),
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
