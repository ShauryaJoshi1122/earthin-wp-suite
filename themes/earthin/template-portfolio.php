<?php
/**
 * Template Name: Portfolio / Project Showcase
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

<main id="primary" class="site-main earthin-portfolio-main">
    <?php
    while (have_posts()) :
        the_post();
        ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class('earthin-portfolio-article'); ?>>
            <!-- Portfolio Hero Header -->
            <header class="entry-header earthin-portfolio-header">
                <span class="earthin-badge"><?php esc_html_e('Featured Work & Case Studies', 'earthin'); ?></span>
                <?php the_title('<h1 class="entry-title earthin-portfolio-title">', '</h1>'); ?>
                <p class="earthin-portfolio-lead">
                    <?php esc_html_e('A curated collection of modular WordPress plugins, high-performance web applications, and minimalist digital experiences.', 'earthin'); ?>
                </p>
            </header>

            <!-- Page Intro Content (if any) -->
            <?php
            $intro_content = get_the_content();
            if (!empty(trim($intro_content))) :
                ?>
                <div class="entry-content earthin-portfolio-intro">
                    <?php the_content(); ?>
                </div>
            <?php endif; ?>

            <!-- Showcase Grid -->
            <div class="earthin-portfolio-grid">
                <!-- Project 1 -->
                <div class="earthin-project-card">
                    <div class="earthin-project-thumbnail" style="background: linear-gradient(135deg, #059669 0%, #047857 100%);">
                        <span class="earthin-project-emoji">🌱</span>
                    </div>
                    <div class="earthin-project-content">
                        <div class="earthin-project-tags">
                            <span class="earthin-project-tag">WordPress Core</span>
                            <span class="earthin-project-tag">Full Suite</span>
                        </div>
                        <h3 class="earthin-project-title"><?php esc_html_e('Earthin WP Suite', 'earthin'); ?></h3>
                        <p class="earthin-project-desc">
                            <?php esc_html_e('A unified ecosystem of 29 modular WordPress plugins and responsive theme delivering enterprise speed, security, and privacy.', 'earthin'); ?>
                        </p>
                        <a href="https://plugins.earthin.online" target="_blank" class="earthin-project-link">
                            <?php esc_html_e('Explore Project ↗', 'earthin'); ?>
                        </a>
                    </div>
                </div>

                <!-- Project 2 -->
                <div class="earthin-project-card">
                    <div class="earthin-project-thumbnail" style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);">
                        <span class="earthin-project-emoji">⚡</span>
                    </div>
                    <div class="earthin-project-content">
                        <div class="earthin-project-tags">
                            <span class="earthin-project-tag">Performance</span>
                            <span class="earthin-project-tag">Caching</span>
                        </div>
                        <h3 class="earthin-project-title"><?php esc_html_e('Cache Accelerator Engine', 'earthin'); ?></h3>
                        <p class="earthin-project-desc">
                            <?php esc_html_e('Disk-based page cache with instant gzip serialization and automated URI query bypass filters.', 'earthin'); ?>
                        </p>
                        <a href="https://plugins.earthin.online/plugins/earthin-cache-accelerator" target="_blank" class="earthin-project-link">
                            <?php esc_html_e('Explore Project ↗', 'earthin'); ?>
                        </a>
                    </div>
                </div>

                <!-- Project 3 -->
                <div class="earthin-project-card">
                    <div class="earthin-project-thumbnail" style="background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);">
                        <span class="earthin-project-emoji">🍪</span>
                    </div>
                    <div class="earthin-project-content">
                        <div class="earthin-project-tags">
                            <span class="earthin-project-tag">Privacy</span>
                            <span class="earthin-project-tag">GDPR & CCPA</span>
                        </div>
                        <h3 class="earthin-project-title"><?php esc_html_e('Cookie Consent Pro', 'earthin'); ?></h3>
                        <p class="earthin-project-desc">
                            <?php esc_html_e('Strict script-blocking consent banner with EU geo-targeting and cryptographic SHA-256 proof-of-consent audits.', 'earthin'); ?>
                        </p>
                        <a href="https://plugins.earthin.online/plugins/earthin-cookie-consent-pro" target="_blank" class="earthin-project-link">
                            <?php esc_html_e('Explore Project ↗', 'earthin'); ?>
                        </a>
                    </div>
                </div>

                <!-- Project 4 -->
                <div class="earthin-project-card">
                    <div class="earthin-project-thumbnail" style="background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%);">
                        <span class="earthin-project-emoji">🛡️</span>
                    </div>
                    <div class="earthin-project-content">
                        <div class="earthin-project-tags">
                            <span class="earthin-project-tag">Cybersecurity</span>
                            <span class="earthin-project-tag">Defense</span>
                        </div>
                        <h3 class="earthin-project-title"><?php esc_html_e('Security 2FA & Login Shield', 'earthin'); ?></h3>
                        <p class="earthin-project-desc">
                            <?php esc_html_e('Time-based One-Time Password (TOTP) two-factor authentication combined with intelligent brute-force IP rate-limiting.', 'earthin'); ?>
                        </p>
                        <a href="https://plugins.earthin.online/plugins/earthin-security-2fa" target="_blank" class="earthin-project-link">
                            <?php esc_html_e('Explore Project ↗', 'earthin'); ?>
                        </a>
                    </div>
                </div>

                <!-- Project 5 -->
                <div class="earthin-project-card">
                    <div class="earthin-project-thumbnail" style="background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);">
                        <span class="earthin-project-emoji">💬</span>
                    </div>
                    <div class="earthin-project-content">
                        <div class="earthin-project-tags">
                            <span class="earthin-project-tag">Messaging</span>
                            <span class="earthin-project-tag">Conversion</span>
                        </div>
                        <h3 class="earthin-project-title"><?php esc_html_e('Chat Connect Floating Bridge', 'earthin'); ?></h3>
                        <p class="earthin-project-desc">
                            <?php esc_html_e('Lightweight visitor engagement widget linking WhatsApp and Telegram directly with zero third-party tracking cookies.', 'earthin'); ?>
                        </p>
                        <a href="https://plugins.earthin.online/plugins/earthin-chat-connect" target="_blank" class="earthin-project-link">
                            <?php esc_html_e('Explore Project ↗', 'earthin'); ?>
                        </a>
                    </div>
                </div>

                <!-- Project 6 -->
                <div class="earthin-project-card">
                    <div class="earthin-project-thumbnail" style="background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);">
                        <span class="earthin-project-emoji">🏗️</span>
                    </div>
                    <div class="earthin-project-content">
                        <div class="earthin-project-tags">
                            <span class="earthin-project-tag">Builder</span>
                            <span class="earthin-project-tag">Semantic HTML</span>
                        </div>
                        <h3 class="earthin-project-title"><?php esc_html_e('Visual Layout Builder', 'earthin'); ?></h3>
                        <p class="earthin-project-desc">
                            <?php esc_html_e('Modular section-and-column visual layout composer that saves pure semantic HTML with under 2KB of runtime CSS.', 'earthin'); ?>
                        </p>
                        <a href="https://plugins.earthin.online/plugins/earthin-page-builder" target="_blank" class="earthin-project-link">
                            <?php esc_html_e('Explore Project ↗', 'earthin'); ?>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Bottom Project Inquiry CTA -->
            <section class="earthin-about-cta" style="margin-top: 4rem;">
                <div class="earthin-cta-inner">
                    <h2 class="earthin-cta-title"><?php esc_html_e('Have an Ambitious Project in Mind?', 'earthin'); ?></h2>
                    <p class="earthin-cta-desc"><?php esc_html_e('We partner with growing teams to build fast, beautiful, and secure digital platforms.', 'earthin'); ?></p>
                    <div class="earthin-cta-buttons">
                        <a href="<?php echo esc_url(home_url('/contact/')); ?>" class="earthin-read-more" style="background:#fff; color:#047857;">
                            <?php esc_html_e('Start a Conversation →', 'earthin'); ?>
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
