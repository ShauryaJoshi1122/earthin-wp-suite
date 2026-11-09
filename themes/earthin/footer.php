<?php
/**
 * Footer template for Earthin theme
 *
 * @package Earthin
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
            </div><!-- .content-area-wrap -->
        </div><!-- .earthin-container -->
    </div><!-- #content -->

    <footer id="colophon" class="site-footer">
        <div class="earthin-container">
            <?php if (is_active_sidebar('footer-1')) : ?>
                <div class="footer-widgets-wrap">
                    <?php dynamic_sidebar('footer-1'); ?>
                </div>
            <?php endif; ?>

            <div class="site-footer-inner">
                <div class="site-info">
                    <?php
                    $footer_text = get_theme_mod('earthin_footer_text', sprintf(__('Proudly powered by WordPress & Earthin Suite by %s', 'earthin'), 'Shaurya Joshi'));
                    echo esc_html($footer_text);
                    ?>
                </div>

                <?php if (has_nav_menu('footer')) : ?>
                    <nav class="footer-navigation" aria-label="<?php esc_attr_e('Footer Menu', 'earthin'); ?>">
                        <?php
                        wp_nav_menu([
                            'theme_location' => 'footer',
                            'menu_class'     => 'footer-menu',
                            'depth'          => 1,
                            'container'      => false,
                        ]);
                        ?>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    </footer>
</div><!-- #page -->

<?php wp_footer(); ?>
</body>
</html>
