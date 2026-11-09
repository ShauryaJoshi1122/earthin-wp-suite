<?php
/**
 * Template Name: Earthin: Case Studies & Testimonials
 * Template Post Type: page, post
 *
 * Impactful social proof template featuring key performance metrics,
 * 2 in-depth client case study spotlights, 6 customer reviews with 5-star ratings,
 * and high-converting call to action.
 *
 * @package Earthin
 * @author Shaurya Joshi
 * @link https://plugins.earthin.online
 */

get_header();
?>

<div class="earthin-testimonials-page">

    <!-- 1. HERO HEADER -->
    <header class="earthin-testimonials-hero">
        <div class="earthin-container text-center">
            <span class="earthin-hero-pill"><?php esc_html_e('PROVEN PERFORMANCE & SOCIAL PROOF', 'earthin'); ?></span>
            <h1 class="earthin-testimonials-title"><?php esc_html_e('Trusted by High-Velocity Publishers & Agencies', 'earthin'); ?></h1>
            <p class="earthin-testimonials-subtitle">
                <?php esc_html_e('Discover how digital leaders scale their WordPress infrastructure, cut page load times by 70%, and boost conversions with Earthin.', 'earthin'); ?>
            </p>
        </div>
    </header>

    <div class="earthin-container">

        <!-- 2. SOCIAL PROOF METRICS BAR -->
        <section class="earthin-proof-metrics-bar">
            <div class="earthin-proof-metric">
                <span class="earthin-proof-number">4.9/5</span>
                <span class="earthin-proof-stars">★★★★★</span>
                <span class="earthin-proof-desc"><?php esc_html_e('Average Rating (500+ Reviews)', 'earthin'); ?></span>
            </div>
            <div class="earthin-proof-metric">
                <span class="earthin-proof-number">10,000+</span>
                <span class="earthin-proof-label"><?php esc_html_e('Active Installations', 'earthin'); ?></span>
                <span class="earthin-proof-desc"><?php esc_html_e('Across 48 Countries', 'earthin'); ?></span>
            </div>
            <div class="earthin-proof-metric">
                <span class="earthin-proof-number">99.98%</span>
                <span class="earthin-proof-label"><?php esc_html_e('Customer Satisfaction', 'earthin'); ?></span>
                <span class="earthin-proof-desc"><?php esc_html_e('Verified Ticket Feedback', 'earthin'); ?></span>
            </div>
            <div class="earthin-proof-metric">
                <span class="earthin-proof-number">&lt;150ms</span>
                <span class="earthin-proof-label"><?php esc_html_e('Avg TTFB Latency', 'earthin'); ?></span>
                <span class="earthin-proof-desc"><?php esc_html_e('With Earthin Cache Accelerator', 'earthin'); ?></span>
            </div>
        </section>

        <!-- 3. IN-DEPTH CASE STUDIES (2 SPOTLIGHTS) -->
        <section class="earthin-case-studies-section">
            <div class="earthin-section-header text-center">
                <span class="earthin-badge"><?php esc_html_e('IN-DEPTH CUSTOMER SPOTLIGHTS', 'earthin'); ?></span>
                <h2 class="earthin-section-title"><?php esc_html_e('Transformative Growth Stories', 'earthin'); ?></h2>
            </div>

            <!-- Spotlight 1 -->
            <article class="earthin-case-study-card">
                <div class="earthin-cs-content">
                    <span class="earthin-cs-client-badge"><?php esc_html_e('MEDIA & EDITORIAL PUBLISHER', 'earthin'); ?></span>
                    <h3 class="earthin-cs-title"><?php esc_html_e('How TechPulse Scaled to 2.8 Million Monthly Readers with 0% Downtime', 'earthin'); ?></h3>
                    <p class="earthin-cs-summary">
                        <?php esc_html_e('Facing extreme traffic spikes during breaking tech launches, TechPulse migrated from a heavy bloated theme to the Earthin suite. With the built-in Cache Accelerator and optimized templates, server memory consumption dropped by 64% while mobile bounce rate plunged.', 'earthin'); ?>
                    </p>
                    <div class="earthin-cs-metrics-grid">
                        <div class="earthin-cs-stat">
                            <span class="earthin-cs-stat-val text-emerald">-78%</span>
                            <span class="earthin-cs-stat-lbl"><?php esc_html_e('Page Load Time (4.2s → 0.9s)', 'earthin'); ?></span>
                        </div>
                        <div class="earthin-cs-stat">
                            <span class="earthin-cs-stat-val text-blue">+310%</span>
                            <span class="earthin-cs-stat-lbl"><?php esc_html_e('Organic Search Traffic', 'earthin'); ?></span>
                        </div>
                        <div class="earthin-cs-stat">
                            <span class="earthin-cs-stat-val text-purple">100/100</span>
                            <span class="earthin-cs-stat-lbl"><?php esc_html_e('Core Web Vitals Pass Rate', 'earthin'); ?></span>
                        </div>
                    </div>
                </div>
                <div class="earthin-cs-quote-box">
                    <p class="earthin-cs-quote-text">
                        "<?php esc_html_e('Switching to Earthin was the single highest-ROI infrastructure decision we made this year. We handled an 800,000 visitor spike during our annual hardware event without a hiccup.', 'earthin'); ?>"
                    </p>
                    <div class="earthin-cs-author">
                        <div class="earthin-avatar-placeholder bg-emerald">VK</div>
                        <div>
                            <strong class="earthin-cs-author-name">Vikram Khanna</strong>
                            <span class="earthin-cs-author-title"><?php esc_html_e('CTO, TechPulse Media', 'earthin'); ?></span>
                        </div>
                    </div>
                </div>
            </article>

            <!-- Spotlight 2 -->
            <article class="earthin-case-study-card reversed">
                <div class="earthin-cs-content">
                    <span class="earthin-cs-client-badge"><?php esc_html_e('ECOMMERCE & DIGITAL GOODS', 'earthin'); ?></span>
                    <h3 class="earthin-cs-title"><?php esc_html_e('Zero-Fee UPI QR Engine Multiplies Conversions by 3.4x for CreativeStore', 'earthin'); ?></h3>
                    <p class="earthin-cs-summary">
                        <?php esc_html_e('CreativeStore was losing up to 3% in payment gateway fees and suffering cart abandonment from clunky redirect checkout pages. By deploying Earthin dynamic UPI QR generation, users completed checkout directly on mobile in under 12 seconds.', 'earthin'); ?>
                    </p>
                    <div class="earthin-cs-metrics-grid">
                        <div class="earthin-cs-stat">
                            <span class="earthin-cs-stat-val text-emerald">3.4x</span>
                            <span class="earthin-cs-stat-lbl"><?php esc_html_e('Checkout Conversion Rate', 'earthin'); ?></span>
                        </div>
                        <div class="earthin-cs-stat">
                            <span class="earthin-cs-stat-val text-blue">0%</span>
                            <span class="earthin-cs-stat-lbl"><?php esc_html_e('Middleman Gateway Surcharges', 'earthin'); ?></span>
                        </div>
                        <div class="earthin-cs-stat">
                            <span class="earthin-cs-stat-val text-purple">&lt;12s</span>
                            <span class="earthin-cs-stat-lbl"><?php esc_html_e('Average Order Completion Time', 'earthin'); ?></span>
                        </div>
                    </div>
                </div>
                <div class="earthin-cs-quote-box">
                    <p class="earthin-cs-quote-text">
                        "<?php esc_html_e('Our customers love scanning the dynamic QR with GPay, PhonePe, or Paytm and finishing checkout instantly. Our abandoned cart rate dropped from 38% down to 9%.', 'earthin'); ?>"
                    </p>
                    <div class="earthin-cs-author">
                        <div class="earthin-avatar-placeholder bg-blue">AM</div>
                        <div>
                            <strong class="earthin-cs-author-name">Ananya Mehta</strong>
                            <span class="earthin-cs-author-title"><?php esc_html_e('Founder & Managing Director, CreativeStore', 'earthin'); ?></span>
                        </div>
                    </div>
                </div>
            </article>

        </section>

        <!-- 4. CLIENT TESTIMONIALS GRID (6 REVIEWS) -->
        <section class="earthin-testimonials-grid-section">
            <div class="earthin-section-header text-center">
                <span class="earthin-badge"><?php esc_html_e('WHAT USERS SAY', 'earthin'); ?></span>
                <h2 class="earthin-section-title"><?php esc_html_e('Loved by Thousands of Web Professionals', 'earthin'); ?></h2>
            </div>

            <div class="earthin-reviews-grid">

                <!-- Review 1 -->
                <div class="earthin-review-card">
                    <div class="earthin-review-stars">★★★★★</div>
                    <p class="earthin-review-body">
                        "<?php esc_html_e('The cleanest WordPress theme code I have reviewed in 10 years. No bloat, no 50 plugins required just to show a hero banner. Clean semantic HTML5 and ultra-fast styling.', 'earthin'); ?>"
                    </p>
                    <div class="earthin-reviewer">
                        <div class="earthin-avatar-placeholder bg-emerald">RS</div>
                        <div class="earthin-reviewer-info">
                            <strong class="earthin-reviewer-name">Rohan Sharma</strong>
                            <span class="earthin-reviewer-role"><?php esc_html_e('Senior WordPress Architect, DevAgency', 'earthin'); ?></span>
                        </div>
                        <span class="earthin-verified-badge" title="<?php esc_attr_e('Verified Customer', 'earthin'); ?>">✓</span>
                    </div>
                </div>

                <!-- Review 2 -->
                <div class="earthin-review-card">
                    <div class="earthin-review-stars">★★★★★</div>
                    <p class="earthin-review-body">
                        "<?php esc_html_e('The new Editorial Magazine template and breaking news ticker solved our newsroom needs immediately. Our editors can publish in seconds without touching custom code.', 'earthin'); ?>"
                    </p>
                    <div class="earthin-reviewer">
                        <div class="earthin-avatar-placeholder bg-blue">SL</div>
                        <div class="earthin-reviewer-info">
                            <strong class="earthin-reviewer-name">Sara Lin</strong>
                            <span class="earthin-reviewer-role"><?php esc_html_e('Managing Editor, Apex Chronicle', 'earthin'); ?></span>
                        </div>
                        <span class="earthin-verified-badge" title="<?php esc_attr_e('Verified Customer', 'earthin'); ?>">✓</span>
                    </div>
                </div>

                <!-- Review 3 -->
                <div class="earthin-review-card">
                    <div class="earthin-review-stars">★★★★★</div>
                    <p class="earthin-review-body">
                        "<?php esc_html_e('The Login Shield plugin combined with the Cache Accelerator cut our bot attack traffic by 99% while our TTFB went from 850ms down to 120ms. Exceptional work.', 'earthin'); ?>"
                    </p>
                    <div class="earthin-reviewer">
                        <div class="earthin-avatar-placeholder bg-purple">DJ</div>
                        <div class="earthin-reviewer-info">
                            <strong class="earthin-reviewer-name">David Jenkins</strong>
                            <span class="earthin-reviewer-role"><?php esc_html_e('DevOps Lead, CloudCraft', 'earthin'); ?></span>
                        </div>
                        <span class="earthin-verified-badge" title="<?php esc_attr_e('Verified Customer', 'earthin'); ?>">✓</span>
                    </div>
                </div>

                <!-- Review 4 -->
                <div class="earthin-review-card">
                    <div class="earthin-review-stars">★★★★★</div>
                    <p class="earthin-review-body">
                        "<?php esc_html_e('I was skeptical about having 29 plugins in a single ecosystem, but every single plugin is lightweight, well-tested, and doesn’t conflict with standard WP hooks. A game changer.', 'earthin'); ?>"
                    </p>
                    <div class="earthin-reviewer">
                        <div class="earthin-avatar-placeholder bg-amber">NG</div>
                        <div class="earthin-reviewer-info">
                            <strong class="earthin-reviewer-name">Neha Gupta</strong>
                            <span class="earthin-reviewer-role"><?php esc_html_e('Independent Freelancer & Consultant', 'earthin'); ?></span>
                        </div>
                        <span class="earthin-verified-badge" title="<?php esc_attr_e('Verified Customer', 'earthin'); ?>">✓</span>
                    </div>
                </div>

                <!-- Review 5 -->
                <div class="earthin-review-card">
                    <div class="earthin-review-stars">★★★★★</div>
                    <p class="earthin-review-body">
                        "<?php esc_html_e('The Blank Canvas template alone made building high-converting webinar funnels so much easier. Header and footer disappear, but CSS remains intact. Love it.', 'earthin'); ?>"
                    </p>
                    <div class="earthin-reviewer">
                        <div class="earthin-avatar-placeholder bg-red">MK</div>
                        <div class="earthin-reviewer-info">
                            <strong class="earthin-reviewer-name">Markus Koch</strong>
                            <span class="earthin-reviewer-role"><?php esc_html_e('Growth Lead, ScaleSaaS', 'earthin'); ?></span>
                        </div>
                        <span class="earthin-verified-badge" title="<?php esc_attr_e('Verified Customer', 'earthin'); ?>">✓</span>
                    </div>
                </div>

                <!-- Review 6 -->
                <div class="earthin-review-card">
                    <div class="earthin-review-stars">★★★★★</div>
                    <p class="earthin-review-body">
                        "<?php esc_html_e('Outstanding support and continuous updates. Shaurya and the Earthin contributors respond to issues within hours and genuinely listen to user feedback.', 'earthin'); ?>"
                    </p>
                    <div class="earthin-reviewer">
                        <div class="earthin-avatar-placeholder bg-cyan">TN</div>
                        <div class="earthin-reviewer-info">
                            <strong class="earthin-reviewer-name">Tanvi Nair</strong>
                            <span class="earthin-reviewer-role"><?php esc_html_e('Product Manager, NextGen Media', 'earthin'); ?></span>
                        </div>
                        <span class="earthin-verified-badge" title="<?php esc_attr_e('Verified Customer', 'earthin'); ?>">✓</span>
                    </div>
                </div>

            </div>
        </section>

        <!-- 5. FINAL CALL TO ACTION -->
        <section class="earthin-testimonials-cta-banner">
            <div class="earthin-testimonials-cta-inner text-center">
                <span class="earthin-badge"><?php esc_html_e('GET STARTED TODAY', 'earthin'); ?></span>
                <h2 class="earthin-cta-heading"><?php esc_html_e('Ready to Experience the Earthin Advantage?', 'earthin'); ?></h2>
                <p class="earthin-cta-subheading"><?php esc_html_e('Join over 10,000+ creators, developers, and media brands building faster websites.', 'earthin'); ?></p>
                <div class="earthin-cta-btns">
                    <a href="#get-started" class="earthin-btn earthin-btn-primary earthin-btn-lg"><?php esc_html_e('Get All 14+ Templates & 29 Plugins', 'earthin'); ?> →</a>
                    <a href="#contact" class="earthin-btn earthin-btn-secondary earthin-btn-lg"><?php esc_html_e('Explore Live Demos', 'earthin'); ?></a>
                </div>
            </div>
        </section>

        <!-- Page editor content if any -->
        <?php while (have_posts()) : the_post(); ?>
            <?php if (get_the_content()) : ?>
                <div class="earthin-testimonials-custom-content">
                    <?php the_content(); ?>
                </div>
            <?php endif; ?>
        <?php endwhile; ?>

    </div>

</div>

<?php
get_footer();
