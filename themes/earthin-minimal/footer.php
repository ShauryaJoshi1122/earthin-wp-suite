<?php
/**
 * Footer template for Earthin Minimal
 *
 * @package Earthin_Minimal
 */
?>

<footer class="em-footer" role="contentinfo">
    <div class="em-container">
        <p>
            &copy; <?php echo esc_html(date('Y')); ?> <a href="<?php echo esc_url(home_url('/')); ?>"><?php bloginfo('name'); ?></a>.
            <?php esc_html_e('Crafted with', 'earthin-minimal'); ?> <a href="https://plugins.earthin.online" target="_blank" rel="noopener noreferrer">Earthin Minimal</a>.
        </p>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
