<?php
/**
 * Footer template for Earthin Store
 *
 * @package Earthin_Store
 */
?>

<footer class="es-footer" role="contentinfo">
    <div class="es-container">
        <div class="es-footer-grid">
            <div class="es-footer-col">
                <h4><?php bloginfo('name'); ?></h4>
                <p style="color: #94a3b8; line-height: 1.6; margin: 0 0 1rem;">
                    <?php esc_html_e('Premium digital software, themes, and extensions crafted for maximum performance and conversions.', 'earthin-store'); ?>
                </p>
                <div style="display: flex; gap: 0.5rem; color: #64748b; font-size: 0.85rem;">
                    <span>🛡️ SSL Secured</span> • <span>⚡ Instant UPI</span>
                </div>
            </div>

            <div class="es-footer-col">
                <h4><?php esc_html_e('Quick Links', 'earthin-store'); ?></h4>
                <ul>
                    <li><a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('All Products', 'earthin-store'); ?></a></li>
                    <li><a href="#featured"><?php esc_html_e('Featured Releases', 'earthin-store'); ?></a></li>
                    <li><a href="#bundles"><?php esc_html_e('Value Bundles', 'earthin-store'); ?></a></li>
                    <li><a href="#documentation"><?php esc_html_e('Licensing & FAQ', 'earthin-store'); ?></a></li>
                </ul>
            </div>

            <div class="es-footer-col">
                <h4><?php esc_html_e('Developer & Support', 'earthin-store'); ?></h4>
                <ul>
                    <li><a href="https://plugins.earthin.online" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Earthin Hub', 'earthin-store'); ?></a></li>
                    <li><a href="https://github.com/ShauryaJoshi1122" target="_blank" rel="noopener noreferrer"><?php esc_html_e('GitHub Repository', 'earthin-store'); ?></a></li>
                    <li><a href="mailto:support@earthin.online"><?php esc_html_e('Help Desk Ticket', 'earthin-store'); ?></a></li>
                </ul>
            </div>
        </div>

        <div class="es-footer-bottom">
            <p>
                &copy; <?php echo esc_html(date('Y')); ?> <a href="<?php echo esc_url(home_url('/')); ?>" style="color: #fff; text-decoration: none;"><?php bloginfo('name'); ?></a>.
                <?php esc_html_e('Powered by', 'earthin-store'); ?> <a href="https://plugins.earthin.online" style="color: var(--es-accent); text-decoration: none;">Earthin Store Theme</a>.
            </p>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
