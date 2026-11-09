<?php
/**
 * Index / Homepage template for Earthin Agency
 *
 * @package Earthin_Agency
 */

get_header();
?>

<!-- Hero -->
<section class="ea-hero">
    <div class="ea-container">
        <div class="ea-hero-inner">
            <span class="ea-hero-pill">
                <span>⚡</span> <?php esc_html_e('DIGITAL ARCHITECTURE & STRATEGY', 'earthin-agency'); ?>
            </span>
            <h1 class="ea-hero-title">
                <?php esc_html_e('We Build Web Systems That Scale to', 'earthin-agency'); ?> <span class="ea-gradient-text"><?php esc_html_e('Millions of Users', 'earthin-agency'); ?></span>
            </h1>
            <p class="ea-hero-subtitle">
                <?php esc_html_e('Partnering with high-growth startups, ambitious creators, and media enterprises to engineer blazing-fast WordPress solutions, zero-bloat plugins, and intuitive design systems.', 'earthin-agency'); ?>
            </p>
            <div class="ea-hero-actions">
                <a href="#contact" class="ea-btn ea-btn-primary"><?php esc_html_e('Book Strategic Discovery', 'earthin-agency'); ?> →</a>
                <a href="#work" class="ea-btn ea-btn-secondary"><?php esc_html_e('Explore Case Studies', 'earthin-agency'); ?></a>
            </div>

            <!-- Stats Bar -->
            <div class="ea-stats-bar">
                <div class="ea-stat-item">
                    <div class="ea-stat-num">99.99%</div>
                    <div class="ea-stat-lbl"><?php esc_html_e('Uptime SLA', 'earthin-agency'); ?></div>
                </div>
                <div class="ea-stat-item">
                    <div class="ea-stat-num">&lt;120ms</div>
                    <div class="ea-stat-lbl"><?php esc_html_e('Avg TTFB Speed', 'earthin-agency'); ?></div>
                </div>
                <div class="ea-stat-item">
                    <div class="ea-stat-num">500k+</div>
                    <div class="ea-stat-lbl"><?php esc_html_e('Active Installs', 'earthin-agency'); ?></div>
                </div>
                <div class="ea-stat-item">
                    <div class="ea-stat-num">100/100</div>
                    <div class="ea-stat-lbl"><?php esc_html_e('PageSpeed Score', 'earthin-agency'); ?></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Bento Grid Capabilities -->
<section class="ea-bento-section" id="capabilities">
    <div class="ea-container">
        <div class="ea-section-header">
            <span class="ea-section-badge"><?php esc_html_e('OUR EXPERTISE', 'earthin-agency'); ?></span>
            <h2 class="ea-section-title"><?php esc_html_e('Engineered for Extreme Velocity', 'earthin-agency'); ?></h2>
        </div>

        <div class="ea-bento-grid">
            <div class="ea-bento-card ea-bento-col-2">
                <div>
                    <div class="ea-bento-icon">⚡</div>
                    <h3 class="ea-bento-heading"><?php esc_html_e('Microservices & Distributed Caching Layer', 'earthin-agency'); ?></h3>
                    <p class="ea-bento-desc">
                        <?php esc_html_e('Eliminate database query latency through smart object caching, tiered Redis structures, and edge worker routing that effortlessly withstands massive viral traffic spikes.', 'earthin-agency'); ?>
                    </p>
                </div>
            </div>

            <div class="ea-bento-card">
                <div>
                    <div class="ea-bento-icon">🛡️</div>
                    <h3 class="ea-bento-heading"><?php esc_html_e('Zero-Trust Hardening', 'earthin-agency'); ?></h3>
                    <p class="ea-bento-desc">
                        <?php esc_html_e('Adaptive rate limiting, token authentication shields, and automated threat defense built directly into the PHP runtime.', 'earthin-agency'); ?>
                    </p>
                </div>
            </div>

            <div class="ea-bento-card">
                <div>
                    <div class="ea-bento-icon">🎨</div>
                    <h3 class="ea-bento-heading"><?php esc_html_e('Custom Design Systems', 'earthin-agency'); ?></h3>
                    <p class="ea-bento-desc">
                        <?php esc_html_e('Tailored design tokens, reusable block components, and responsive layouts built with modern semantic CSS and zero external frameworks.', 'earthin-agency'); ?>
                    </p>
                </div>
            </div>

            <div class="ea-bento-card ea-bento-col-2">
                <div>
                    <div class="ea-bento-icon">💳</div>
                    <h3 class="ea-bento-heading"><?php esc_html_e('Instant Checkout & UPI QR Payment Gateways', 'earthin-agency'); ?></h3>
                    <p class="ea-bento-desc">
                        <?php esc_html_e('Bespoke conversion-optimized checkout funnels with auto-generated dynamic UPI QR codes and automated webhook reconciliation for zero-fee transactions.', 'earthin-agency'); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Recent Work / Case Studies -->
<section class="ea-work-section" id="work">
    <div class="ea-container">
        <div class="ea-section-header">
            <span class="ea-section-badge"><?php esc_html_e('CLIENT SUCCESS STORIES', 'earthin-agency'); ?></span>
            <h2 class="ea-section-title"><?php esc_html_e('Featured Projects & Implementations', 'earthin-agency'); ?></h2>
        </div>

        <div class="ea-work-grid">
            <?php
            if (have_posts()) :
                while (have_posts()) : the_post();
            ?>
                    <article id="post-<?php the_ID(); ?>" <?php post_class('ea-project-card'); ?>>
                        <div class="ea-project-media">
                            <?php if (has_post_thumbnail()) : ?>
                                <a href="<?php the_permalink(); ?>"><?php the_post_thumbnail('medium_large'); ?></a>
                            <?php else : ?>
                                <span>🚀</span>
                            <?php endif; ?>
                        </div>
                        <div class="ea-project-body">
                            <span class="ea-project-tags"><?php esc_html_e('Case Study', 'earthin-agency'); ?></span>
                            <h3 class="ea-project-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                            <p class="ea-project-desc"><?php echo esc_html(wp_trim_words(get_the_excerpt(), 16)); ?></p>
                            <a href="<?php the_permalink(); ?>" style="color: var(--ea-primary); font-weight: 700; text-decoration: none; font-size: 0.9rem;">
                                <?php esc_html_e('Read Case Study', 'earthin-agency'); ?> →
                            </a>
                        </div>
                    </article>
            <?php
                endwhile;
            else :
                $mock_works = [
                    ['title' => 'Scaling TechPulse to 2.8M Monthly Readers', 'tag' => 'Media & Publishing', 'desc' => '78% reduction in page load time, 100/100 Core Web Vitals, and zero downtime during annual hardware keynote.'],
                    ['title' => 'Global Commerce UPI Direct Checkout Overhaul', 'tag' => 'FinTech & eCommerce', 'desc' => 'Implemented server-side UPI QR generator cutting cart abandonment by 29% and saving $40,000 in transaction gateway fees.'],
                    ['title' => 'NexusCloud Enterprise Security & Audit Log', 'tag' => 'SaaS Security', 'desc' => 'Engineered bespoke 2FA login shield and activity audit engine across 45 multi-tenant client WordPress environments.'],
                ];

                foreach ($mock_works as $work) :
            ?>
                    <article class="ea-project-card">
                        <div class="ea-project-media">
                            <span>🚀</span>
                        </div>
                        <div class="ea-project-body">
                            <span class="ea-project-tags"><?php echo esc_html($work['tag']); ?></span>
                            <h3 class="ea-project-title"><a href="#study"><?php echo esc_html($work['title']); ?></a></h3>
                            <p class="ea-project-desc"><?php echo esc_html($work['desc']); ?></p>
                            <a href="#study" style="color: var(--ea-primary); font-weight: 700; text-decoration: none; font-size: 0.9rem;">
                                <?php esc_html_e('Read Case Study', 'earthin-agency'); ?> →
                            </a>
                        </div>
                    </article>
            <?php
                endforeach;
            endif;
            ?>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="ea-container" id="contact">
    <div class="ea-cta-banner">
        <h2 class="ea-cta-heading"><?php esc_html_e('Have an Ambitious Project in Mind?', 'earthin-agency'); ?></h2>
        <p class="ea-cta-desc">
            <?php esc_html_e('Let our technical architects audit your architecture, streamline your page speed, or engineer bespoke WordPress solutions.', 'earthin-agency'); ?>
        </p>
        <a href="mailto:hello@earthin.online?subject=Project%20Inquiry:%20Earthin%20Agency" class="ea-btn ea-btn-primary" style="font-size: 1.1rem; padding: 1rem 2rem;">
            <?php esc_html_e('Schedule an Initial Consultation', 'earthin-agency'); ?> →
        </a>
    </div>
</section>

<?php
get_footer();
