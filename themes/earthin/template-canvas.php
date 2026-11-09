<?php
/**
 * Template Name: Blank Canvas (Landing Page)
 * Template Post Type: page
 *
 * @package Earthin
 * @author Shaurya Joshi
 * @link https://plugins.earthin.online
 */

if (!defined('ABSPATH')) {
    exit;
}
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>

<body <?php body_class('earthin-canvas-page no-sidebar'); ?>>
<?php wp_body_open(); ?>

<div id="page" class="site earthin-canvas-site">
    <a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e('Skip to content', 'earthin'); ?></a>

    <main id="primary" class="site-main earthin-canvas-main">
        <?php
        while (have_posts()) :
            the_post();
            ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class('earthin-canvas-article'); ?>>
                <div class="entry-content earthin-canvas-content">
                    <?php
                    the_content();

                    wp_link_pages([
                        'before' => '<div class="page-links">' . esc_html__('Pages:', 'earthin'),
                        'after'  => '</div>',
                    ]);
                    ?>
                </div>
            </article>
            <?php
        endwhile;
        ?>
    </main>
</div><!-- #page -->

<?php wp_footer(); ?>
</body>
</html>
