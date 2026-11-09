<?php
/**
 * Template Name: Earthin: Pricing & Plans
 * Template Post Type: page, post
 *
 * High-converting, transparent pricing template featuring an interactive
 * monthly/annual billing switcher, 3 tier cards with feature comparison matrix,
 * money-back guarantee seal, and FAQ accordions.
 *
 * @package Earthin
 * @author Shaurya Joshi
 * @link https://plugins.earthin.online
 */

get_header();
?>

<div class="earthin-pricing-page">

    <!-- 1. HERO HEADER -->
    <header class="earthin-pricing-hero">
        <div class="earthin-container text-center">
            <span class="earthin-hero-pill"><?php esc_html_e('HONEST & TRANSPARENT PRICING', 'earthin'); ?></span>
            <h1 class="earthin-pricing-title"><?php esc_html_e('Simple Plans for Every Stage of Growth', 'earthin'); ?></h1>
            <p class="earthin-pricing-subtitle">
                <?php esc_html_e('From personal blogs to high-volume enterprise networks. Full access to our 29+ plugins, high-performance themes, and direct updates.', 'earthin'); ?>
            </p>

            <!-- Billing Toggle Switcher -->
            <div class="earthin-billing-switcher" role="radiogroup" aria-label="<?php esc_attr_e('Billing Period', 'earthin'); ?>">
                <span class="earthin-billing-label active" id="billing-monthly-label"><?php esc_html_e('Monthly Billing', 'earthin'); ?></span>
                <button type="button" class="earthin-toggle-switch" id="earthinPricingToggle" aria-checked="false" role="switch" onclick="toggleBillingPeriod()">
                    <span class="earthin-toggle-slider"></span>
                </button>
                <span class="earthin-billing-label" id="billing-annual-label">
                    <?php esc_html_e('Annual Billing', 'earthin'); ?>
                    <span class="earthin-discount-badge"><?php esc_html_e('Save 20%', 'earthin'); ?></span>
                </span>
            </div>
        </div>
    </header>

    <div class="earthin-container">

        <!-- 2. PRICING TIERS -->
        <div class="earthin-pricing-grid">

            <!-- Tier 1: Starter -->
            <div class="earthin-price-card">
                <div class="earthin-price-card-header">
                    <span class="earthin-plan-tier"><?php esc_html_e('STARTER', 'earthin'); ?></span>
                    <h3 class="earthin-plan-name"><?php esc_html_e('Community', 'earthin'); ?></h3>
                    <p class="earthin-plan-desc"><?php esc_html_e('Essential foundation for personal blogs and developers exploring the Earthin ecosystem.', 'earthin'); ?></p>
                </div>

                <div class="earthin-price-amount-box">
                    <div class="earthin-price-val">
                        <span class="earthin-currency">$</span>
                        <span class="earthin-amount">0</span>
                    </div>
                    <span class="earthin-billing-cadence"><?php esc_html_e('free forever', 'earthin'); ?></span>
                </div>

                <ul class="earthin-plan-features">
                    <li><span class="earthin-check-icon">✓</span> <?php esc_html_e('5 Core Earthin Plugins', 'earthin'); ?></li>
                    <li><span class="earthin-check-icon">✓</span> <?php esc_html_e('Earthin High-Speed Theme', 'earthin'); ?></li>
                    <li><span class="earthin-check-icon">✓</span> <?php esc_html_e('Standard Community Forum Access', 'earthin'); ?></li>
                    <li><span class="earthin-check-icon">✓</span> <?php esc_html_e('Standard Documentation & Guides', 'earthin'); ?></li>
                    <li class="disabled"><span class="earthin-cross-icon">✕</span> <?php esc_html_e('Automated Backup & Cloud Sync', 'earthin'); ?></li>
                    <li class="disabled"><span class="earthin-cross-icon">✕</span> <?php esc_html_e('Priority Ticket SLA', 'earthin'); ?></li>
                </ul>

                <div class="earthin-price-cta">
                    <a href="#download" class="earthin-btn earthin-btn-secondary earthin-btn-block">
                        <?php esc_html_e('Get Started Free', 'earthin'); ?>
                    </a>
                </div>
            </div>

            <!-- Tier 2: Pro (Featured) -->
            <div class="earthin-price-card earthin-price-featured">
                <div class="earthin-featured-ribbon"><?php esc_html_e('MOST POPULAR', 'earthin'); ?></div>
                <div class="earthin-price-card-header">
                    <span class="earthin-plan-tier pro"><?php esc_html_e('PROFESSIONAL', 'earthin'); ?></span>
                    <h3 class="earthin-plan-name"><?php esc_html_e('Pro All-Access', 'earthin'); ?></h3>
                    <p class="earthin-plan-desc"><?php esc_html_e('Complete access to the entire 29+ plugin library, pro extensions, and priority developer support.', 'earthin'); ?></p>
                </div>

                <div class="earthin-price-amount-box">
                    <div class="earthin-price-val">
                        <span class="earthin-currency">$</span>
                        <span class="earthin-amount" data-monthly="29" data-annual="23">29</span>
                    </div>
                    <span class="earthin-billing-cadence" data-monthly="<?php esc_attr_e('/month, billed monthly', 'earthin'); ?>" data-annual="<?php esc_attr_e('/month, billed annually ($276/yr)', 'earthin'); ?>">
                        <?php esc_html_e('/month, billed monthly', 'earthin'); ?>
                    </span>
                </div>

                <ul class="earthin-plan-features">
                    <li><span class="earthin-check-icon highlight">✓</span> <strong><?php esc_html_e('All 29+ Production Plugins Included', 'earthin'); ?></strong></li>
                    <li><span class="earthin-check-icon highlight">✓</span> <strong><?php esc_html_e('Unlimited Client & Production Sites', 'earthin'); ?></strong></li>
                    <li><span class="earthin-check-icon highlight">✓</span> <?php esc_html_e('Earthin Page Builder Pro & UPI QR Engine', 'earthin'); ?></li>
                    <li><span class="earthin-check-icon highlight">✓</span> <?php esc_html_e('Login Shield, Cache Accelerator & Security Tools', 'earthin'); ?></li>
                    <li><span class="earthin-check-icon highlight">✓</span> <?php esc_html_e('Automatic 1-Click Updates & Health Diagnostics', 'earthin'); ?></li>
                    <li><span class="earthin-check-icon highlight">✓</span> <?php esc_html_e('Priority Ticket & Developer Help (24hr SLA)', 'earthin'); ?></li>
                </ul>

                <div class="earthin-price-cta">
                    <a href="#get-pro" class="earthin-btn earthin-btn-primary earthin-btn-block">
                        <?php esc_html_e('Get All-Access Pro', 'earthin'); ?> →
                    </a>
                </div>
            </div>

            <!-- Tier 3: Enterprise -->
            <div class="earthin-price-card">
                <div class="earthin-price-card-header">
                    <span class="earthin-plan-tier"><?php esc_html_e('ENTERPRISE', 'earthin'); ?></span>
                    <h3 class="earthin-plan-name"><?php esc_html_e('Agency & Scale', 'earthin'); ?></h3>
                    <p class="earthin-plan-desc"><?php esc_html_e('Tailored for high-traffic networks, agencies, and large publishers needing bespoke compliance & SLA.', 'earthin'); ?></p>
                </div>

                <div class="earthin-price-amount-box">
                    <div class="earthin-price-val">
                        <span class="earthin-currency">$</span>
                        <span class="earthin-amount" data-monthly="99" data-annual="79">99</span>
                    </div>
                    <span class="earthin-billing-cadence" data-monthly="<?php esc_attr_e('/month, billed monthly', 'earthin'); ?>" data-annual="<?php esc_attr_e('/month, billed annually ($948/yr)', 'earthin'); ?>">
                        <?php esc_html_e('/month, billed monthly', 'earthin'); ?>
                    </span>
                </div>

                <ul class="earthin-plan-features">
                    <li><span class="earthin-check-icon">✓</span> <?php esc_html_e('Everything in Pro Tier', 'earthin'); ?></li>
                    <li><span class="earthin-check-icon">✓</span> <?php esc_html_e('White-Label & Rebranding Rights', 'earthin'); ?></li>
                    <li><span class="earthin-check-icon">✓</span> <?php esc_html_e('Direct Slack / Discord Private Channel', 'earthin'); ?></li>
                    <li><span class="earthin-check-icon">✓</span> <?php esc_html_e('Custom Feature Requests & Code Audits', 'earthin'); ?></li>
                    <li><span class="earthin-check-icon">✓</span> <?php esc_html_e('Dedicated Security & Disaster Recovery SLA', 'earthin'); ?></li>
                    <li><span class="earthin-check-icon">✓</span> <?php esc_html_e('Custom Invoicing & Multi-seat Team Billing', 'earthin'); ?></li>
                </ul>

                <div class="earthin-price-cta">
                    <a href="#contact-sales" class="earthin-btn earthin-btn-secondary earthin-btn-block">
                        <?php esc_html_e('Contact Agency Sales', 'earthin'); ?>
                    </a>
                </div>
            </div>

        </div>

        <!-- 3. FEATURE COMPARISON MATRIX TABLE -->
        <section class="earthin-matrix-section">
            <div class="earthin-matrix-header text-center">
                <span class="earthin-badge"><?php esc_html_e('IN-DEPTH BREAKDOWN', 'earthin'); ?></span>
                <h2 class="earthin-matrix-title"><?php esc_html_e('Compare Plans Feature by Feature', 'earthin'); ?></h2>
            </div>

            <div class="earthin-table-responsive">
                <table class="earthin-comparison-table">
                    <thead>
                        <tr>
                            <th class="col-feature"><?php esc_html_e('Core Features & Capabilities', 'earthin'); ?></th>
                            <th class="col-starter"><?php esc_html_e('Starter (Free)', 'earthin'); ?></th>
                            <th class="col-pro highlight"><?php esc_html_e('Pro All-Access', 'earthin'); ?></th>
                            <th class="col-enterprise"><?php esc_html_e('Enterprise Agency', 'earthin'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong><?php esc_html_e('Plugin Library Access', 'earthin'); ?></strong></td>
                            <td>5 Essential Plugins</td>
                            <td class="highlight"><strong>All 29+ Plugins</strong></td>
                            <td><strong>All 29+ & Future Plugins</strong></td>
                        </tr>
                        <tr>
                            <td><strong><?php esc_html_e('Theme Templates', 'earthin'); ?></strong></td>
                            <td>Standard Default</td>
                            <td class="highlight">14+ High-Speed Templates</td>
                            <td>All Templates + Custom Builder</td>
                        </tr>
                        <tr>
                            <td><strong><?php esc_html_e('Site License Limit', 'earthin'); ?></strong></td>
                            <td>1 Site</td>
                            <td class="highlight">Unlimited Production Sites</td>
                            <td>Unlimited + Client Resale</td>
                        </tr>
                        <tr>
                            <td><strong><?php esc_html_e('UPI QR & Payment Engine', 'earthin'); ?></strong></td>
                            <td>Basic</td>
                            <td class="highlight">Instant Auto-Generated QR</td>
                            <td>Multi-Merchant Gateway Routing</td>
                        </tr>
                        <tr>
                            <td><strong><?php esc_html_e('Cache Accelerator & Minifier', 'earthin'); ?></strong></td>
                            <td>File Cache Only</td>
                            <td class="highlight">Full In-Memory & Object Cache</td>
                            <td>Distributed Edge Redis / Memcached</td>
                        </tr>
                        <tr>
                            <td><strong><?php esc_html_e('Security & Login Shield', 'earthin'); ?></strong></td>
                            <td>Standard Lockout</td>
                            <td class="highlight">2FA, Geo-Block & Adaptive Traps</td>
                            <td>Bespoke WAF & IP Threat Feeds</td>
                        </tr>
                        <tr>
                            <td><strong><?php esc_html_e('White-Label & Rebranding', 'earthin'); ?></strong></td>
                            <td>—</td>
                            <td class="highlight">—</td>
                            <td>Included (100% Brandable)</td>
                        </tr>
                        <tr>
                            <td><strong><?php esc_html_e('Support SLA', 'earthin'); ?></strong></td>
                            <td>Community Forum</td>
                            <td class="highlight">24-Hour Ticket Response</td>
                            <td>1-Hour Critical Emergency SLA</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- 4. TRUST & GUARANTEE BADGE -->
        <section class="earthin-guarantee-banner">
            <div class="earthin-guarantee-inner">
                <div class="earthin-guarantee-badge-icon">🛡️</div>
                <div class="earthin-guarantee-text">
                    <h3 class="earthin-guarantee-heading"><?php esc_html_e('Risk-Free 30-Day Money-Back Guarantee', 'earthin'); ?></h3>
                    <p class="earthin-guarantee-desc">
                        <?php esc_html_e('Test Earthin plugins and templates on your real production sites. If you are not completely satisfied with speed, flexibility, and performance, get a 100% refund without questions.', 'earthin'); ?>
                    </p>
                </div>
            </div>
        </section>

        <!-- 5. PRICING FAQ -->
        <section class="earthin-pricing-faq">
            <div class="text-center">
                <span class="earthin-badge"><?php esc_html_e('GOT QUESTIONS?', 'earthin'); ?></span>
                <h2 class="earthin-faq-title"><?php esc_html_e('Frequently Asked Pricing Questions', 'earthin'); ?></h2>
            </div>

            <div class="earthin-faq-accordion">
                <details class="earthin-faq-item" open>
                    <summary class="earthin-faq-question"><?php esc_html_e('Can I upgrade or downgrade between plans anytime?', 'earthin'); ?></summary>
                    <div class="earthin-faq-answer">
                        <p><?php esc_html_e('Yes! You can instantly upgrade your plan at any time from your Earthin account dashboard. Any unused balance will be automatically prorated toward your new subscription.', 'earthin'); ?></p>
                    </div>
                </details>

                <details class="earthin-faq-item">
                    <summary class="earthin-faq-question"><?php esc_html_e('What happens if I cancel my subscription?', 'earthin'); ?></summary>
                    <div class="earthin-faq-answer">
                        <p><?php esc_html_e('Your installed plugins and themes will continue functioning normally on your websites forever. Canceling only pauses automated 1-click updates, cloud backups, and priority support.', 'earthin'); ?></p>
                    </div>
                </details>

                <details class="earthin-faq-item">
                    <summary class="earthin-faq-question"><?php esc_html_e('What payment methods do you accept?', 'earthin'); ?></summary>
                    <div class="earthin-faq-answer">
                        <p><?php esc_html_e('We accept all major Credit/Debit Cards, UPI QR (Instant zero-fee payment for Indian merchants), PayPal, Apple Pay, and Google Pay.', 'earthin'); ?></p>
                    </div>
                </details>

                <details class="earthin-faq-item">
                    <summary class="earthin-faq-question"><?php esc_html_e('Do you offer a discount for open-source and non-profits?', 'earthin'); ?></summary>
                    <div class="earthin-faq-answer">
                        <p><?php esc_html_e('Yes! We love open-source and registered charities. Contact our support desk with your organization details to receive a complimentary 50% discount.', 'earthin'); ?></p>
                    </div>
                </details>
            </div>
        </section>

        <!-- Page editor content if any -->
        <?php while (have_posts()) : the_post(); ?>
            <?php if (get_the_content()) : ?>
                <div class="earthin-pricing-custom-content">
                    <?php the_content(); ?>
                </div>
            <?php endif; ?>
        <?php endwhile; ?>

    </div>

</div>

<script>
function toggleBillingPeriod() {
    var toggleBtn = document.getElementById('earthinPricingToggle');
    var isAnnual = toggleBtn.getAttribute('aria-checked') === 'true';
    var nextState = !isAnnual;
    toggleBtn.setAttribute('aria-checked', nextState ? 'true' : 'false');
    toggleBtn.classList.toggle('active', nextState);

    var monthlyLabel = document.getElementById('billing-monthly-label');
    var annualLabel = document.getElementById('billing-annual-label');
    if (monthlyLabel) monthlyLabel.classList.toggle('active', !nextState);
    if (annualLabel) annualLabel.classList.toggle('active', nextState);

    var amounts = document.querySelectorAll('.earthin-price-amount-box .earthin-amount');
    amounts.forEach(function(el) {
        if (nextState && el.getAttribute('data-annual')) {
            el.textContent = el.getAttribute('data-annual');
        } else if (!nextState && el.getAttribute('data-monthly')) {
            el.textContent = el.getAttribute('data-monthly');
        }
    });

    var cadences = document.querySelectorAll('.earthin-price-amount-box .earthin-billing-cadence');
    cadences.forEach(function(el) {
        if (nextState && el.getAttribute('data-annual')) {
            el.textContent = el.getAttribute('data-annual');
        } else if (!nextState && el.getAttribute('data-monthly')) {
            el.textContent = el.getAttribute('data-monthly');
        }
    });
}
</script>

<?php
get_footer();
