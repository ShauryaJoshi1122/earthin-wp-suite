<?php
/**
 * Template Name: Earthin: Knowledge Base & Docs
 * Template Post Type: page, post
 *
 * Comprehensive documentation portal and knowledge base template featuring
 * a live interactive search bar, 6 core topic category cards with article lists,
 * trending tutorials, and multi-channel developer support cards.
 *
 * @package Earthin
 * @author Shaurya Joshi
 * @link https://plugins.earthin.online
 */

get_header();
?>

<div class="earthin-kb-portal">

    <!-- 1. HERO SEARCH HEADER -->
    <header class="earthin-kb-hero">
        <div class="earthin-container text-center">
            <span class="earthin-hero-pill"><?php esc_html_e('DOCUMENTATION & HELP CENTER', 'earthin'); ?></span>
            <h1 class="earthin-kb-title"><?php esc_html_e('How Can We Help You Today?', 'earthin'); ?></h1>
            <p class="earthin-kb-subtitle">
                <?php esc_html_e('Explore comprehensive guides, API references, configuration walkthroughs, and troubleshooting steps for the Earthin ecosystem.', 'earthin'); ?>
            </p>

            <!-- Instant Search Input -->
            <div class="earthin-kb-search-box">
                <form role="search" method="get" class="earthin-kb-search-form" action="<?php echo esc_url(home_url('/')); ?>">
                    <span class="earthin-kb-search-icon">🔍</span>
                    <input type="search" class="earthin-kb-search-input" name="s" placeholder="<?php esc_attr_e('Search articles, error codes, plugins, hooks, UPI setup...', 'earthin'); ?>" required />
                    <button type="submit" class="earthin-kb-search-submit"><?php esc_html_e('Search Docs', 'earthin'); ?></button>
                </form>
                <div class="earthin-kb-quick-tags">
                    <span class="earthin-quick-label"><?php esc_html_e('Popular Topics:', 'earthin'); ?></span>
                    <a href="#quickstart" class="earthin-quick-tag"><?php esc_html_e('Quickstart', 'earthin'); ?></a>
                    <a href="#upi" class="earthin-quick-tag"><?php esc_html_e('UPI QR Engine', 'earthin'); ?></a>
                    <a href="#caching" class="earthin-quick-tag"><?php esc_html_e('Cache Accelerator', 'earthin'); ?></a>
                    <a href="#page-builder" class="earthin-quick-tag"><?php esc_html_e('Page Builder', 'earthin'); ?></a>
                    <a href="#rest-api" class="earthin-quick-tag"><?php esc_html_e('REST API', 'earthin'); ?></a>
                </div>
            </div>
        </div>
    </header>

    <div class="earthin-container">

        <!-- 2. CORE TOPIC CATEGORIES (6 CARDS) -->
        <section class="earthin-kb-categories-section">
            <div class="earthin-section-header text-center">
                <span class="earthin-badge"><?php esc_html_e('BROWSE BY TOPIC', 'earthin'); ?></span>
                <h2 class="earthin-section-title"><?php esc_html_e('Knowledge Base Categories', 'earthin'); ?></h2>
            </div>

            <div class="earthin-kb-grid">

                <!-- Category 1: Getting Started -->
                <div class="earthin-kb-card" id="quickstart">
                    <div class="earthin-kb-card-header">
                        <div class="earthin-kb-icon-wrap bg-emerald">🚀</div>
                        <div>
                            <h3 class="earthin-kb-category-name"><?php esc_html_e('Getting Started', 'earthin'); ?></h3>
                            <span class="earthin-kb-article-count"><?php esc_html_e('6 Guides', 'earthin'); ?></span>
                        </div>
                    </div>
                    <p class="earthin-kb-card-desc"><?php esc_html_e('Initial theme installation, child theme setup, and plugin auto-activation.', 'earthin'); ?></p>
                    <ul class="earthin-kb-article-list">
                        <li><a href="#guide-1"><span>📄</span> <?php esc_html_e('Installing the Earthin Suite via WP-Admin', 'earthin'); ?></a></li>
                        <li><a href="#guide-2"><span>📄</span> <?php esc_html_e('Understanding Theme Templates & Header Layouts', 'earthin'); ?></a></li>
                        <li><a href="#guide-3"><span>📄</span> <?php esc_html_e('1-Click Sample Content & Navigation Setup', 'earthin'); ?></a></li>
                    </ul>
                    <a href="#all-getting-started" class="earthin-kb-view-all"><?php esc_html_e('View all 6 guides', 'earthin'); ?> →</a>
                </div>

                <!-- Category 2: Plugins & Extensions -->
                <div class="earthin-kb-card" id="plugins">
                    <div class="earthin-kb-card-header">
                        <div class="earthin-kb-icon-wrap bg-blue">🧩</div>
                        <div>
                            <h3 class="earthin-kb-category-name"><?php esc_html_e('Plugins & Hub', 'earthin'); ?></h3>
                            <span class="earthin-kb-article-count"><?php esc_html_e('14 Guides', 'earthin'); ?></span>
                        </div>
                    </div>
                    <p class="earthin-kb-card-desc"><?php esc_html_e('Configuring the 29+ native plugins from the Earthin Next.js Hub.', 'earthin'); ?></p>
                    <ul class="earthin-kb-article-list">
                        <li><a href="#guide-4"><span>📄</span> <?php esc_html_e('Managing Plugins in Earthin Plugin Hub', 'earthin'); ?></a></li>
                        <li><a href="#guide-5"><span>📄</span> <?php esc_html_e('Earthin Page Builder: Creating Dynamic Grids', 'earthin'); ?></a></li>
                        <li><a href="#guide-6"><span>📄</span> <?php esc_html_e('Form Builder: Conditional Logic & Webhooks', 'earthin'); ?></a></li>
                    </ul>
                    <a href="#all-plugins" class="earthin-kb-view-all"><?php esc_html_e('View all 14 guides', 'earthin'); ?> →</a>
                </div>

                <!-- Category 3: Payments & UPI QR -->
                <div class="earthin-kb-card" id="upi">
                    <div class="earthin-kb-card-header">
                        <div class="earthin-kb-icon-wrap bg-purple">💳</div>
                        <div>
                            <h3 class="earthin-kb-category-name"><?php esc_html_e('Payments & UPI QR', 'earthin'); ?></h3>
                            <span class="earthin-kb-article-count"><?php esc_html_e('8 Guides', 'earthin'); ?></span>
                        </div>
                    </div>
                    <p class="earthin-kb-card-desc"><?php esc_html_e('Configuring zero-fee UPI QR codes, admin verification, and receipts.', 'earthin'); ?></p>
                    <ul class="earthin-kb-article-list">
                        <li><a href="#guide-7"><span>📄</span> <?php esc_html_e('Setting your Merchant UPI ID & Store Currency', 'earthin'); ?></a></li>
                        <li><a href="#guide-8"><span>📄</span> <?php esc_html_e('Dynamic QR Generation at Checkout', 'earthin'); ?></a></li>
                        <li><a href="#guide-9"><span>📄</span> <?php esc_html_e('Automated UTR Transaction Verification', 'earthin'); ?></a></li>
                    </ul>
                    <a href="#all-upi" class="earthin-kb-view-all"><?php esc_html_e('View all 8 guides', 'earthin'); ?> →</a>
                </div>

                <!-- Category 4: Performance & Caching -->
                <div class="earthin-kb-card" id="caching">
                    <div class="earthin-kb-card-header">
                        <div class="earthin-kb-icon-wrap bg-amber">⚡</div>
                        <div>
                            <h3 class="earthin-kb-category-name"><?php esc_html_e('Performance & Caching', 'earthin'); ?></h3>
                            <span class="earthin-kb-article-count"><?php esc_html_e('9 Guides', 'earthin'); ?></span>
                        </div>
                    </div>
                    <p class="earthin-kb-card-desc"><?php esc_html_e('Achieving 100/100 Google PageSpeed and sub-100ms load times.', 'earthin'); ?></p>
                    <ul class="earthin-kb-article-list">
                        <li><a href="#guide-10"><span>📄</span> <?php esc_html_e('Cache Accelerator: Page & Object Cache Setup', 'earthin'); ?></a></li>
                        <li><a href="#guide-11"><span>📄</span> <?php esc_html_e('Image Optimizer: WebP/AVIF Conversion & Lazy Loading', 'earthin'); ?></a></li>
                        <li><a href="#guide-12"><span>📄</span> <?php esc_html_e('Database Cleaner: Optimizing Transients & Revisions', 'earthin'); ?></a></li>
                    </ul>
                    <a href="#all-performance" class="earthin-kb-view-all"><?php esc_html_e('View all 9 guides', 'earthin'); ?> →</a>
                </div>

                <!-- Category 5: Security & Privacy -->
                <div class="earthin-kb-card" id="security">
                    <div class="earthin-kb-card-header">
                        <div class="earthin-kb-icon-wrap bg-red">🛡️</div>
                        <div>
                            <h3 class="earthin-kb-category-name"><?php esc_html_e('Security & Privacy', 'earthin'); ?></h3>
                            <span class="earthin-kb-article-count"><?php esc_html_e('11 Guides', 'earthin'); ?></span>
                        </div>
                    </div>
                    <p class="earthin-kb-card-desc"><?php esc_html_e('Hardening authentication, preventing brute-force attacks, and compliance.', 'earthin'); ?></p>
                    <ul class="earthin-kb-article-list">
                        <li><a href="#guide-13"><span>📄</span> <?php esc_html_e('Login Shield: Configuring Rate Limits & 2FA', 'earthin'); ?></a></li>
                        <li><a href="#guide-14"><span>📄</span> <?php esc_html_e('Activity Log: Real-time Audit Trail for Admins', 'earthin'); ?></a></li>
                        <li><a href="#guide-15"><span>📄</span> <?php esc_html_e('Cookie Consent: GDPR/CCPA Compliance Setup', 'earthin'); ?></a></li>
                    </ul>
                    <a href="#all-security" class="earthin-kb-view-all"><?php esc_html_e('View all 11 guides', 'earthin'); ?> →</a>
                </div>

                <!-- Category 6: Developer API & Hooks -->
                <div class="earthin-kb-card" id="rest-api">
                    <div class="earthin-kb-card-header">
                        <div class="earthin-kb-icon-wrap bg-cyan">⚙️</div>
                        <div>
                            <h3 class="earthin-kb-category-name"><?php esc_html_e('Developer API & Hooks', 'earthin'); ?></h3>
                            <span class="earthin-kb-article-count"><?php esc_html_e('15 Guides', 'earthin'); ?></span>
                        </div>
                    </div>
                    <p class="earthin-kb-card-desc"><?php esc_html_e('Extending themes and plugins with PHP filters, actions, and REST endpoints.', 'earthin'); ?></p>
                    <ul class="earthin-kb-article-list">
                        <li><a href="#guide-16"><span>📄</span> <?php esc_html_e('Complete Earthin Action & Filter Reference', 'earthin'); ?></a></li>
                        <li><a href="#guide-17"><span>📄</span> <?php esc_html_e('Customizing Page Templates with Child Themes', 'earthin'); ?></a></li>
                        <li><a href="#guide-18"><span>📄</span> <?php esc_html_e('Next.js Hub REST API & Webhook Specifications', 'earthin'); ?></a></li>
                    </ul>
                    <a href="#all-developer" class="earthin-kb-view-all"><?php esc_html_e('View all 15 guides', 'earthin'); ?> →</a>
                </div>

            </div>
        </section>

        <!-- 3. TRENDING & POPULAR GUIDES -->
        <section class="earthin-kb-trending-section">
            <div class="earthin-section-header">
                <span class="earthin-badge"><?php esc_html_e('FREQUENTLY ACCESSED', 'earthin'); ?></span>
                <h2 class="earthin-section-title"><?php esc_html_e('Top Articles Read This Month', 'earthin'); ?></h2>
            </div>

            <div class="earthin-kb-trending-grid">
                <a href="#popular-1" class="earthin-kb-popular-row">
                    <span class="earthin-popular-icon">🔥</span>
                    <div class="earthin-popular-details">
                        <h4 class="earthin-popular-title"><?php esc_html_e('Fixing Common Rewrite & Permalinks 404 Errors in Apache and Nginx', 'earthin'); ?></h4>
                        <span class="earthin-popular-meta"><?php esc_html_e('Troubleshooting • 4 min read • 14,200 views', 'earthin'); ?></span>
                    </div>
                    <span class="earthin-popular-arrow">→</span>
                </a>

                <a href="#popular-2" class="earthin-kb-popular-row">
                    <span class="earthin-popular-icon">⚡</span>
                    <div class="earthin-popular-details">
                        <h4 class="earthin-popular-title"><?php esc_html_e('How to Configure Redis Object Caching for High-Concurrency Traffic', 'earthin'); ?></h4>
                        <span class="earthin-popular-meta"><?php esc_html_e('Performance • 6 min read • 9,800 views', 'earthin'); ?></span>
                    </div>
                    <span class="earthin-popular-arrow">→</span>
                </a>

                <a href="#popular-3" class="earthin-kb-popular-row">
                    <span class="earthin-popular-icon">💳</span>
                    <div class="earthin-popular-details">
                        <h4 class="earthin-popular-title"><?php esc_html_e('Testing Instant UPI QR Payments with Mock Sandboxes', 'earthin'); ?></h4>
                        <span class="earthin-popular-meta"><?php esc_html_e('Payments • 3 min read • 8,400 views', 'earthin'); ?></span>
                    </div>
                    <span class="earthin-popular-arrow">→</span>
                </a>

                <a href="#popular-4" class="earthin-kb-popular-row">
                    <span class="earthin-popular-icon">🔒</span>
                    <div class="earthin-popular-details">
                        <h4 class="earthin-popular-title"><?php esc_html_e('Hardening wp-config.php and File Permissions for Production Servers', 'earthin'); ?></h4>
                        <span class="earthin-popular-meta"><?php esc_html_e('Security • 5 min read • 7,900 views', 'earthin'); ?></span>
                    </div>
                    <span class="earthin-popular-arrow">→</span>
                </a>
            </div>
        </section>

        <!-- 4. SUPPORT CHANNELS BANNER -->
        <section class="earthin-kb-support-banner">
            <div class="earthin-kb-support-inner">
                <div class="earthin-kb-support-text">
                    <h3 class="earthin-kb-support-heading"><?php esc_html_e('Cannot find what you are looking for?', 'earthin'); ?></h3>
                    <p class="earthin-kb-support-desc">
                        <?php esc_html_e('Our engineering team is always active. Get immediate answers from community members or open a priority technical support ticket.', 'earthin'); ?>
                    </p>
                </div>
                <div class="earthin-kb-support-actions">
                    <a href="#community" class="earthin-btn earthin-btn-secondary"><?php esc_html_e('Join Community Forum', 'earthin'); ?></a>
                    <a href="#ticket" class="earthin-btn earthin-btn-primary"><?php esc_html_e('Open Support Ticket', 'earthin'); ?> →</a>
                </div>
            </div>
        </section>

        <!-- Page editor content if any -->
        <?php while (have_posts()) : the_post(); ?>
            <?php if (get_the_content()) : ?>
                <div class="earthin-kb-custom-content">
                    <?php the_content(); ?>
                </div>
            <?php endif; ?>
        <?php endwhile; ?>

    </div>

</div>

<?php
get_footer();
