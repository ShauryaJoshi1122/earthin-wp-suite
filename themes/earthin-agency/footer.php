<?php
/**
 * Footer template for Earthin Agency
 *
 * @package Earthin_Agency
 */
?>

<footer class="ea-footer" role="contentinfo">
    <div class="ea-container">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 2.5rem; margin-bottom: 3rem;">
            <div>
                <h4 style="color: #ffffff; font-size: 1.15rem; font-weight: 800; margin: 0 0 1rem;"><?php bloginfo('name'); ?>.agency</h4>
                <p style="color: var(--ea-text-muted); line-height: 1.6; margin: 0 0 1rem;">
                    <?php esc_html_e('Next-generation digital studio architecting custom software, hyper-scaled WordPress platforms, and modern headless web infrastructure.', 'earthin-agency'); ?>
                </p>
            </div>

            <div>
                <h4 style="color: #ffffff; font-size: 1.15rem; font-weight: 800; margin: 0 0 1rem;"><?php esc_html_e('Core Capabilities', 'earthin-agency'); ?></h4>
                <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.6rem;">
                    <li><a href="#services" style="color: var(--ea-text-muted); text-decoration: none;"><?php esc_html_e('Enterprise WordPress Engineering', 'earthin-agency'); ?></a></li>
                    <li><a href="#services" style="color: var(--ea-text-muted); text-decoration: none;"><?php esc_html_e('Performance Caching Architecture', 'earthin-agency'); ?></a></li>
                    <li><a href="#services" style="color: var(--ea-text-muted); text-decoration: none;"><?php esc_html_e('UPI & Payment Gateway Integration', 'earthin-agency'); ?></a></li>
                    <li><a href="#services" style="color: var(--ea-text-muted); text-decoration: none;"><?php esc_html_e('Custom Theme & Plugin Design', 'earthin-agency'); ?></a></li>
                </ul>
            </div>

            <div>
                <h4 style="color: #ffffff; font-size: 1.15rem; font-weight: 800; margin: 0 0 1rem;"><?php esc_html_e('Offices & Contact', 'earthin-agency'); ?></h4>
                <p style="color: var(--ea-text-muted); line-height: 1.6; margin: 0 0 0.5rem;">
                    <strong><?php esc_html_e('Inquiries:', 'earthin-agency'); ?></strong> <a href="mailto:hello@earthin.online" style="color: var(--ea-primary); text-decoration: none;">hello@earthin.online</a>
                </p>
                <p style="color: var(--ea-text-muted); line-height: 1.6; margin: 0;">
                    <strong><?php esc_html_e('Hub:', 'earthin-agency'); ?></strong> <a href="https://plugins.earthin.online" target="_blank" rel="noopener noreferrer" style="color: #ffffff; text-decoration: none;">plugins.earthin.online</a>
                </p>
            </div>
        </div>

        <div class="ea-footer-bottom">
            <p>
                &copy; <?php echo esc_html(date('Y')); ?> <a href="<?php echo esc_url(home_url('/')); ?>"><?php bloginfo('name'); ?></a>.
                <?php esc_html_e('All rights reserved. Powered by', 'earthin-agency'); ?> <a href="https://plugins.earthin.online" style="color: var(--ea-primary);">Earthin Agency Theme</a>.
            </p>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
