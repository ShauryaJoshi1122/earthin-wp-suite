<?php
/**
 * Page template for Earthin Agency
 *
 * @package Earthin_Agency
 */

get_header();
?>

<div class="ea-container" style="padding-top: 4rem; padding-bottom: 5rem;">

    <?php while (have_posts()) : the_post(); ?>

        <article id="post-<?php the_ID(); ?>" <?php post_class('ea-page'); ?>>

            <header style="margin-bottom: 2.5rem; text-align: center; max-width: 820px; margin-left: auto; margin-right: auto;">
                <h1 style="font-size: 2.75rem; font-weight: 900; line-height: 1.2; margin: 0; color: #ffffff;"><?php the_title(); ?></h1>
            </header>

            <div class="ea-page-content" style="max-width: 900px; margin: 0 auto; background: var(--ea-surface); border: 1px solid var(--ea-border); border-radius: var(--ea-radius); padding: 3rem; line-height: 1.85; font-size: 1.05rem;">
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
