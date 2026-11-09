<?php
/**
 * Template Name: Earthin: Changelog & Release Notes
 * Template Post Type: page, post
 *
 * Detailed changelog and release history template featuring a vertical
 * interconnected timeline, version release tags, categorized change pills
 * (New, Improvement, Fix, Security), and subscription channels.
 *
 * @package Earthin
 * @author Shaurya Joshi
 * @link https://plugins.earthin.online
 */

get_header();
?>

<div class="earthin-changelog-page">

    <!-- 1. HERO HEADER -->
    <header class="earthin-changelog-hero">
        <div class="earthin-container text-center">
            <span class="earthin-hero-pill"><?php esc_html_e('PRODUCT UPDATES & RELEASES', 'earthin'); ?></span>
            <h1 class="earthin-changelog-title"><?php esc_html_e('Earthin Changelog', 'earthin'); ?></h1>
            <p class="earthin-changelog-subtitle">
                <?php esc_html_e('Track every new feature, performance optimization, refactoring step, and security hardening update across the Earthin ecosystem.', 'earthin'); ?>
            </p>

            <div class="earthin-changelog-actions">
                <a href="https://github.com/ShauryaJoshi1122/earthin-wp-suite/releases" target="_blank" rel="noopener noreferrer" class="earthin-btn earthin-btn-secondary">
                    <span>🐙</span> <?php esc_html_e('View GitHub Releases', 'earthin'); ?>
                </a>
                <a href="#subscribe" class="earthin-btn earthin-btn-primary">
                    <span>🔔</span> <?php esc_html_e('Subscribe to Release Alerts', 'earthin'); ?>
                </a>
            </div>
        </div>
    </header>

    <div class="earthin-container">

        <!-- 2. TIMELINE WRAPPER -->
        <div class="earthin-timeline-wrapper">

            <!-- Release 1: v2.4.0 -->
            <article class="earthin-timeline-release">
                <div class="earthin-timeline-marker">
                    <span class="earthin-marker-dot active"></span>
                    <span class="earthin-marker-line"></span>
                </div>
                <div class="earthin-release-card">
                    <div class="earthin-release-header">
                        <div class="earthin-release-meta">
                            <span class="earthin-version-badge current"><?php esc_html_e('v2.4.0', 'earthin'); ?></span>
                            <span class="earthin-status-pill"><?php esc_html_e('Latest Stable', 'earthin'); ?></span>
                            <time class="earthin-release-date" datetime="2026-11-09"><?php esc_html_e('November 9, 2026', 'earthin'); ?></time>
                        </div>
                        <h2 class="earthin-release-title"><?php esc_html_e('14 High-Speed Theme Templates & UPI QR Engine Expansion', 'earthin'); ?></h2>
                    </div>

                    <div class="earthin-release-body">
                        <p class="earthin-release-summary">
                            <?php esc_html_e('A major landmark release introducing 14 custom WordPress templates, dynamic UPI QR checkout codes, complete responsive styling, and enhanced admin settings interfaces.', 'earthin'); ?>
                        </p>

                        <div class="earthin-change-group">
                            <h4 class="earthin-change-type new"><span class="pill">NEW</span> <?php esc_html_e('New Features & Capabilities', 'earthin'); ?></h4>
                            <ul class="earthin-change-items">
                                <li><strong><?php esc_html_e('8 Production Theme Templates:', 'earthin'); ?></strong> <?php esc_html_e('Added Full Width, Blank Canvas, Distraction-Free Reading, Contact Info Grid, About Us, Services, Portfolio, and Left Sidebar templates.', 'earthin'); ?></li>
                                <li><strong><?php esc_html_e('News & Editorial Magazine Portal:', 'earthin'); ?></strong> <?php esc_html_e('Breaking news live ticker, hero headline banner, 3-column editorial stream with WP_Query integration, and trending sidebar.', 'earthin'); ?></li>
                                <li><strong><?php esc_html_e('Dedicated Pricing, Knowledge Base, Team & Changelog Templates:', 'earthin'); ?></strong> <?php esc_html_e('Interactive billing toggle, instant docs search, career board, and animated release timeline.', 'earthin'); ?></li>
                                <li><strong><?php esc_html_e('Dynamic UPI QR Engine:', 'earthin'); ?></strong> <?php esc_html_e('Auto-generates UPI intent links and QR codes with custom merchant ID and order price calculation.', 'earthin'); ?></li>
                            </ul>
                        </div>

                        <div class="earthin-change-group">
                            <h4 class="earthin-change-type improvement"><span class="pill">IMPROVED</span> <?php esc_html_e('Improvements & Optimizations', 'earthin'); ?></h4>
                            <ul class="earthin-change-items">
                                <li><strong><?php esc_html_e('CSS Modular Architecture:', 'earthin'); ?></strong> <?php esc_html_e('Streamlined template CSS with zero render-blocking dependencies and full mobile responsiveness.', 'earthin'); ?></li>
                                <li><strong><?php esc_html_e('Dynamic Body Class Filter:', 'earthin'); ?></strong> <?php esc_html_e('Added earthin_body_classes hook to automatically apply no-sidebar and left-sidebar layout flags.', 'earthin'); ?></li>
                                <li><strong><?php esc_html_e('Page Builder Settings Interface:', 'earthin'); ?></strong> <?php esc_html_e('Dedicated Settings page under WP-Admin for seamless grid options configuration.', 'earthin'); ?></li>
                            </ul>
                        </div>

                        <div class="earthin-change-group">
                            <h4 class="earthin-change-type security"><span class="pill">SECURITY</span> <?php esc_html_e('Security & Hardening', 'earthin'); ?></h4>
                            <ul class="earthin-change-items">
                                <li><strong><?php esc_html_e('Auth Gateway Shielding:', 'earthin'); ?></strong> <?php esc_html_e('Next.js Hub role checks, secure session cookies, and sanitized contact form submission handling.', 'earthin'); ?></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </article>

            <!-- Release 2: v2.3.0 -->
            <article class="earthin-timeline-release">
                <div class="earthin-timeline-marker">
                    <span class="earthin-marker-dot"></span>
                    <span class="earthin-marker-line"></span>
                </div>
                <div class="earthin-release-card">
                    <div class="earthin-release-header">
                        <div class="earthin-release-meta">
                            <span class="earthin-version-badge"><?php esc_html_e('v2.3.0', 'earthin'); ?></span>
                            <time class="earthin-release-date" datetime="2026-10-18"><?php esc_html_e('October 18, 2026', 'earthin'); ?></time>
                        </div>
                        <h2 class="earthin-release-title"><?php esc_html_e('29 Native Plugins Suite & Next.js Plugin Hub', 'earthin'); ?></h2>
                    </div>

                    <div class="earthin-release-body">
                        <div class="earthin-change-group">
                            <h4 class="earthin-change-type new"><span class="pill">NEW</span> <?php esc_html_e('New Features', 'earthin'); ?></h4>
                            <ul class="earthin-change-items">
                                <li><strong><?php esc_html_e('Next.js Plugin Hub:', 'earthin'); ?></strong> <?php esc_html_e('Launched high-speed web hub with search, live filtering, catalog indexing, and 1-click ZIP downloads.', 'earthin'); ?></li>
                                <li><strong><?php esc_html_e('Performance Suite:', 'earthin'); ?></strong> <?php esc_html_e('Cache Accelerator, Image Optimizer, Database Cleaner, and Asset Minifier included out of the box.', 'earthin'); ?></li>
                            </ul>
                        </div>
                        <div class="earthin-change-group">
                            <h4 class="earthin-change-type fix"><span class="pill">FIX</span> <?php esc_html_e('Bug Fixes', 'earthin'); ?></h4>
                            <ul class="earthin-change-items">
                                <li><?php esc_html_e('Fixed transient expiration timestamp edge cases under PHP 8.2 with high concurrency.', 'earthin'); ?></li>
                                <li><?php esc_html_e('Resolved SVG sanitizer false positives for nested defs and linearGradient attributes.', 'earthin'); ?></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </article>

            <!-- Release 3: v2.2.0 -->
            <article class="earthin-timeline-release">
                <div class="earthin-timeline-marker">
                    <span class="earthin-marker-dot"></span>
                    <span class="earthin-marker-line"></span>
                </div>
                <div class="earthin-release-card">
                    <div class="earthin-release-header">
                        <div class="earthin-release-meta">
                            <span class="earthin-version-badge"><?php esc_html_e('v2.2.0', 'earthin'); ?></span>
                            <time class="earthin-release-date" datetime="2026-09-24"><?php esc_html_e('September 24, 2026', 'earthin'); ?></time>
                        </div>
                        <h2 class="earthin-release-title"><?php esc_html_e('Security & Hardening Overhaul', 'earthin'); ?></h2>
                    </div>

                    <div class="earthin-release-body">
                        <div class="earthin-change-group">
                            <h4 class="earthin-change-type security"><span class="pill">SECURITY</span> <?php esc_html_e('Security Hardening', 'earthin'); ?></h4>
                            <ul class="earthin-change-items">
                                <li><?php esc_html_e('Login Shield: Added adaptive IP rate limiting and auto-lockout for brute force attempts.', 'earthin'); ?></li>
                                <li><?php esc_html_e('Activity Log: Real-time event auditing for core file modifications, user role changes, and plugin state updates.', 'earthin'); ?></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </article>

        </div>

        <!-- Page editor content if any -->
        <?php while (have_posts()) : the_post(); ?>
            <?php if (get_the_content()) : ?>
                <div class="earthin-changelog-custom-content">
                    <?php the_content(); ?>
                </div>
            <?php endif; ?>
        <?php endwhile; ?>

    </div>

</div>

<?php
get_footer();
