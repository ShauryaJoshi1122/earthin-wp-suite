<?php
/**
 * Template Name: About Us / Company Profile
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

<main id="primary" class="site-main earthin-about-main">
    <?php
    while (have_posts()) :
        the_post();
        ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class('earthin-about-article'); ?>>
            <!-- About Hero Header -->
            <header class="entry-header earthin-about-header">
                <span class="earthin-badge"><?php esc_html_e('Our Mission & Philosophy', 'earthin'); ?></span>
                <?php the_title('<h1 class="entry-title earthin-about-title">', '</h1>'); ?>
                <p class="earthin-about-lead">
                    <?php esc_html_e('We craft high-performance, privacy-respecting, and sustainable digital tools engineered for speed and simplicity.', 'earthin'); ?>
                </p>
            </header>

            <!-- Stat Counters Band -->
            <div class="earthin-stats-band">
                <div class="earthin-stat-item">
                    <div class="earthin-stat-number">99.9%</div>
                    <div class="earthin-stat-label"><?php esc_html_e('Platform Reliability', 'earthin'); ?></div>
                </div>
                <div class="earthin-stat-item">
                    <div class="earthin-stat-number">29+</div>
                    <div class="earthin-stat-label"><?php esc_html_e('WordPress Suite Tools', 'earthin'); ?></div>
                </div>
                <div class="earthin-stat-item">
                    <div class="earthin-stat-number">100%</div>
                    <div class="earthin-stat-label"><?php esc_html_e('Privacy By Default', 'earthin'); ?></div>
                </div>
                <div class="earthin-stat-item">
                    <div class="earthin-stat-number">&lt; 100ms</div>
                    <div class="earthin-stat-label"><?php esc_html_e('Page Execution Speed', 'earthin'); ?></div>
                </div>
            </div>

            <!-- Two-Column Story / Mission Section -->
            <div class="earthin-about-story-grid">
                <?php if (has_post_thumbnail()) : ?>
                    <div class="earthin-about-media">
                        <?php the_post_thumbnail('large', ['class' => 'earthin-about-img']); ?>
                    </div>
                <?php endif; ?>

                <div class="entry-content earthin-about-body">
                    <?php
                    the_content();

                    wp_link_pages([
                        'before' => '<div class="page-links">' . esc_html__('Pages:', 'earthin'),
                        'after'  => '</div>',
                    ]);
                    ?>
                </div>
            </div>

            <!-- Core Values Section -->
            <section class="earthin-values-section">
                <div class="earthin-values-header">
                    <h2 class="earthin-values-heading"><?php esc_html_e('Principles That Guide Our Work', 'earthin'); ?></h2>
                    <p class="earthin-values-desc"><?php esc_html_e('Every line of code and user experience we create is anchored in three core values:', 'earthin'); ?></p>
                </div>

                <div class="earthin-values-grid">
                    <div class="earthin-value-card">
                        <div class="earthin-value-icon">⚡</div>
                        <h3 class="earthin-value-title"><?php esc_html_e('Performance Without Bloat', 'earthin'); ?></h3>
                        <p class="earthin-value-text">
                            <?php esc_html_e('We reject multi-megabyte JavaScript libraries when native browser standards deliver better, faster, and cleaner results.', 'earthin'); ?>
                        </p>
                    </div>

                    <div class="earthin-value-card">
                        <div class="earthin-value-icon">🔒</div>
                        <h3 class="earthin-value-title"><?php esc_html_e('Privacy as a Human Right', 'earthin'); ?></h3>
                        <p class="earthin-value-text">
                            <?php esc_html_e('Zero trackers, zero third-party telemetry, and complete transparency. Your website belongs to you and your visitors.', 'earthin'); ?>
                        </p>
                    </div>

                    <div class="earthin-value-card">
                        <div class="earthin-value-icon">🌱</div>
                        <h3 class="earthin-value-title"><?php esc_html_e('Sustainable Engineering', 'earthin'); ?></h3>
                        <p class="earthin-value-text">
                            <?php esc_html_e('Efficient code reduces server load, saves electrical power in data centers, and contributes to a greener global web.', 'earthin'); ?>
                        </p>
                    </div>
                </div>
            </section>

            <!-- Call to Action Banner -->
            <section class="earthin-about-cta">
                <div class="earthin-cta-inner">
                    <h2 class="earthin-cta-title"><?php esc_html_e('Ready to Elevate Your Website Experience?', 'earthin'); ?></h2>
                    <p class="earthin-cta-desc"><?php esc_html_e('Explore our full suite of modular plugins or reach out to discuss your next custom build.', 'earthin'); ?></p>
                    <div class="earthin-cta-buttons">
                        <a href="<?php echo esc_url(home_url('/')); ?>" class="earthin-read-more" style="background:#fff; color:#047857;">
                            <?php esc_html_e('Explore Home →', 'earthin'); ?>
                        </a>
                        <a href="https://plugins.earthin.online" target="_blank" class="earthin-read-more" style="background:rgba(255,255,255,0.15); color:#fff; border:1px solid rgba(255,255,255,0.3);">
                            <?php esc_html_e('Visit Plugin Hub ↗', 'earthin'); ?>
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
