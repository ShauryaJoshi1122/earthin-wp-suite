<?php
/**
 * Template Name: Services & Features Showcase
 * Template Post Type: page
 *
 * @package Earthin
 * @author Shaurya Joshi
 * @link https://plugins.earthin.online
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<main id="primary" class="site-main earthin-services-main">
    <?php
    while (have_posts()) :
        the_post();
        ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class('earthin-services-article'); ?>>
            <!-- Services Header Hero -->
            <header class="entry-header earthin-services-header">
                <span class="earthin-badge"><?php esc_html_e('Solutions & Capabilities', 'earthin'); ?></span>
                <?php the_title('<h1 class="entry-title earthin-services-title">', '</h1>'); ?>
                <p class="earthin-services-lead">
                    <?php esc_html_e('Comprehensive, modular web engineering built for modern businesses, developers, and creators.', 'earthin'); ?>
                </p>
            </header>

            <!-- Main Page Intro Content -->
            <?php
            $intro_content = get_the_content();
            if (!empty(trim($intro_content))) :
                ?>
                <div class="entry-content earthin-services-intro">
                    <?php the_content(); ?>
                </div>
            <?php endif; ?>

            <!-- Services Grid (6 Core Solutions) -->
            <section class="earthin-services-section">
                <div class="earthin-services-grid">
                    <!-- Service 1 -->
                    <div class="earthin-service-card">
                        <div class="earthin-service-icon-box">⚡</div>
                        <h3 class="earthin-service-card-title"><?php esc_html_e('Performance Optimization', 'earthin'); ?></h3>
                        <p class="earthin-service-card-desc"><?php esc_html_e('Core Web Vitals tuning, disk-level caching engines, asset minification, and database overhead reduction.', 'earthin'); ?></p>
                        <ul class="earthin-service-features">
                            <li>✓ <?php esc_html_e('Sub-second TTFB optimization', 'earthin'); ?></li>
                            <li>✓ <?php esc_html_e('WebP next-gen compression', 'earthin'); ?></li>
                            <li>✓ <?php esc_html_e('100/100 Google PageSpeed scores', 'earthin'); ?></li>
                        </ul>
                    </div>

                    <!-- Service 2 -->
                    <div class="earthin-service-card">
                        <div class="earthin-service-icon-box">🛡️</div>
                        <h3 class="earthin-service-card-title"><?php esc_html_e('Security & Hardening', 'earthin'); ?></h3>
                        <p class="earthin-service-card-desc"><?php esc_html_e('Multi-layer defenses protecting against brute-force attacks, unauthorized uploads, and session hijackings.', 'earthin'); ?></p>
                        <ul class="earthin-service-features">
                            <li>✓ <?php esc_html_e('Two-Factor Authentication (2FA)', 'earthin'); ?></li>
                            <li>✓ <?php esc_html_e('Intelligent IP login shielding', 'earthin'); ?></li>
                            <li>✓ <?php esc_html_e('SVG XML sanitization engine', 'earthin'); ?></li>
                        </ul>
                    </div>

                    <!-- Service 3 -->
                    <div class="earthin-service-card">
                        <div class="earthin-service-icon-box">💬</div>
                        <h3 class="earthin-service-card-title"><?php esc_html_e('Customer Engagement', 'earthin'); ?></h3>
                        <p class="earthin-service-card-desc"><?php esc_html_e('Lightweight visitor communication tools via WhatsApp, Telegram, push notifications, and lead forms.', 'earthin'); ?></p>
                        <ul class="earthin-service-features">
                            <li>✓ <?php esc_html_e('Native WhatsApp chat bridge', 'earthin'); ?></li>
                            <li>✓ <?php esc_html_e('Web Push API notifications', 'earthin'); ?></li>
                            <li>✓ <?php esc_html_e('Custom drag-and-drop form builder', 'earthin'); ?></li>
                        </ul>
                    </div>

                    <!-- Service 4 -->
                    <div class="earthin-service-card">
                        <div class="earthin-service-icon-box">⚖️</div>
                        <h3 class="earthin-service-card-title"><?php esc_html_e('Compliance & Privacy', 'earthin'); ?></h3>
                        <p class="earthin-service-card-desc"><?php esc_html_e('International privacy compliance engines providing full GDPR, CCPA, and ePrivacy Directive adherence.', 'earthin'); ?></p>
                        <ul class="earthin-service-features">
                            <li>✓ <?php esc_html_e('Granular consent category blocking', 'earthin'); ?></li>
                            <li>✓ <?php esc_html_e('SHA-256 hashed proof-of-consent logs', 'earthin'); ?></li>
                            <li>✓ <?php esc_html_e('Automated EU/UK Geo-targeting rules', 'earthin'); ?></li>
                        </ul>
                    </div>

                    <!-- Service 5 -->
                    <div class="earthin-service-card">
                        <div class="earthin-service-icon-box">🎨</div>
                        <h3 class="earthin-service-card-title"><?php esc_html_e('Theme & Brand Customization', 'earthin'); ?></h3>
                        <p class="earthin-service-card-desc"><?php esc_html_e('Tailored white-labeling, custom editor toolbars, sticky headers, and bespoke typography palettes.', 'earthin'); ?></p>
                        <ul class="earthin-service-features">
                            <li>✓ <?php esc_html_e('Agency-ready login re-branding', 'earthin'); ?></li>
                            <li>✓ <?php esc_html_e('Classic TinyMCE toolbar expansions', 'earthin'); ?></li>
                            <li>✓ <?php esc_html_e('Zero-JS social sharing buttons', 'earthin'); ?></li>
                        </ul>
                    </div>

                    <!-- Service 6 -->
                    <div class="earthin-service-card">
                        <div class="earthin-service-icon-box">📊</div>
                        <h3 class="earthin-service-card-title"><?php esc_html_e('Diagnostics & Maintenance', 'earthin'); ?></h3>
                        <p class="earthin-service-card-desc"><?php esc_html_e('Proactive site health auditing, 301 redirection matrices, scheduled maintenance splash pages, and audit logging.', 'earthin'); ?></p>
                        <ul class="earthin-service-features">
                            <li>✓ <?php esc_html_e('Real-time server diagnostics suite', 'earthin'); ?></li>
                            <li>✓ <?php esc_html_e('503 SEO safe maintenance mode', 'earthin'); ?></li>
                            <li>✓ <?php esc_html_e('Regex 301 redirection manager', 'earthin'); ?></li>
                        </ul>
                    </div>
                </div>
            </section>

            <!-- 3-Step Process Section -->
            <section class="earthin-process-section">
                <div class="earthin-process-header">
                    <h2 class="earthin-process-title"><?php esc_html_e('Our Proven Delivery Process', 'earthin'); ?></h2>
                    <p class="earthin-process-desc"><?php esc_html_e('How we take digital projects from concept to production seamlessly:', 'earthin'); ?></p>
                </div>

                <div class="earthin-process-steps">
                    <div class="earthin-step-item">
                        <div class="earthin-step-badge">01</div>
                        <h4 class="earthin-step-title"><?php esc_html_e('Audit & Blueprint', 'earthin'); ?></h4>
                        <p class="earthin-step-desc"><?php esc_html_e('Deep analysis of technical requirements, architecture constraints, and UX goals.', 'earthin'); ?></p>
                    </div>

                    <div class="earthin-step-item">
                        <div class="earthin-step-badge">02</div>
                        <h4 class="earthin-step-title"><?php esc_html_e('Lean Engineering', 'earthin'); ?></h4>
                        <p class="earthin-step-desc"><?php esc_html_e('Implementation using modular, bloat-free code with zero external tracking overhead.', 'earthin'); ?></p>
                    </div>

                    <div class="earthin-step-item">
                        <div class="earthin-step-badge">03</div>
                        <h4 class="earthin-step-title"><?php esc_html_e('Deploy & Scale', 'earthin'); ?></h4>
                        <p class="earthin-step-desc"><?php esc_html_e('Production launch with automated caching, diagnostic monitoring, and continuous security.', 'earthin'); ?></p>
                    </div>
                </div>
            </section>

            <!-- Bottom CTA Section -->
            <section class="earthin-about-cta" style="margin-top: 3.5rem;">
                <div class="earthin-cta-inner">
                    <h2 class="earthin-cta-title"><?php esc_html_e('Need a Tailored Solution for Your Site?', 'earthin'); ?></h2>
                    <p class="earthin-cta-desc"><?php esc_html_e('Get in touch with us to discuss customized plugin extensions, themes, or full architectural audits.', 'earthin'); ?></p>
                    <div class="earthin-cta-buttons">
                        <a href="<?php echo esc_url(home_url('/contact/')); ?>" class="earthin-read-more" style="background:#fff; color:#047857;">
                            <?php esc_html_e('Request a Consultation →', 'earthin'); ?>
                        </a>
                    </div>
                </div>
            </section>
        </article>
        <?php
    endwhile;
    ?>
</main>

<?php
get_footer();
