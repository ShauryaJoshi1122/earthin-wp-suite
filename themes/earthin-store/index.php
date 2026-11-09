<?php
/**
 * Index / Shop Catalog template for Earthin Store
 *
 * @package Earthin_Store
 */

get_header();
?>

<!-- Store Hero -->
<section class="es-store-hero">
    <div class="es-container">
        <div class="es-hero-content">
            <span class="es-hero-pill"><?php esc_html_e('DIGITAL STORE & EXTENSIONS', 'earthin-store'); ?></span>
            <h1 class="es-hero-title"><?php esc_html_e('Supercharge Your Digital Publishing Stack', 'earthin-store'); ?></h1>
            <p class="es-hero-subtitle">
                <?php esc_html_e('Download high-performance WordPress themes, developer tools, and security extensions with instant UPI checkout and 1-click updates.', 'earthin-store'); ?>
            </p>
            <div class="es-hero-btns">
                <a href="#catalog" class="es-btn es-btn-primary"><?php esc_html_e('Explore Products', 'earthin-store'); ?> ↓</a>
                <a href="#bundles" class="es-btn es-btn-secondary"><?php esc_html_e('View Pro All-Access Bundle', 'earthin-store'); ?></a>
            </div>
        </div>
    </div>
</section>

<div class="es-container">

    <!-- Trust Badges Strip -->
    <div class="es-trust-strip">
        <div class="es-trust-item">
            <div class="es-trust-icon">⚡</div>
            <div>
                <h4 class="es-trust-title"><?php esc_html_e('Instant Automated Download', 'earthin-store'); ?></h4>
                <p class="es-trust-desc"><?php esc_html_e('Access ZIP binaries immediately after checkout.', 'earthin-store'); ?></p>
            </div>
        </div>
        <div class="es-trust-item">
            <div class="es-trust-icon">💳</div>
            <div>
                <h4 class="es-trust-title"><?php esc_html_e('Direct UPI QR & Cards', 'earthin-store'); ?></h4>
                <p class="es-trust-desc"><?php esc_html_e('Zero-fee instant scan with GPay, PhonePe, Paytm.', 'earthin-store'); ?></p>
            </div>
        </div>
        <div class="es-trust-item">
            <div class="es-trust-icon">🔄</div>
            <div>
                <h4 class="es-trust-title"><?php esc_html_e('Lifetime 1-Click Updates', 'earthin-store'); ?></h4>
                <p class="es-trust-desc"><?php esc_html_e('Direct dashboard updates without breaking hooks.', 'earthin-store'); ?></p>
            </div>
        </div>
        <div class="es-trust-item">
            <div class="es-trust-icon">🛡️</div>
            <div>
                <h4 class="es-trust-title"><?php esc_html_e('30-Day Money-Back Guarantee', 'earthin-store'); ?></h4>
                <p class="es-trust-desc"><?php esc_html_e('100% risk-free testing on your live servers.', 'earthin-store'); ?></p>
            </div>
        </div>
    </div>

    <!-- Catalog Section -->
    <section id="catalog">
        <div class="es-section-title-bar">
            <h2 class="es-section-title"><?php esc_html_e('Featured Digital Products', 'earthin-store'); ?></h2>
            <span style="font-size: 0.9rem; color: var(--es-text-muted);"><?php esc_html_e('Showing latest items', 'earthin-store'); ?></span>
        </div>

        <div class="es-product-grid">
            <?php
            if (have_posts()) :
                while (have_posts()) : the_post();
            ?>
                    <article id="post-<?php the_ID(); ?>" <?php post_class('es-product-card'); ?>>
                        <div class="es-product-thumb">
                            <?php if (has_post_thumbnail()) : ?>
                                <a href="<?php the_permalink(); ?>"><?php the_post_thumbnail('medium'); ?></a>
                            <?php else : ?>
                                <div class="es-product-placeholder-icon">📦</div>
                            <?php endif; ?>
                            <span class="es-sale-badge"><?php esc_html_e('HOT', 'earthin-store'); ?></span>
                        </div>
                        <div class="es-product-body">
                            <span class="es-product-category"><?php esc_html_e('WordPress Software', 'earthin-store'); ?></span>
                            <h3 class="es-product-name"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                            <p class="es-product-desc"><?php echo esc_html(wp_trim_words(get_the_excerpt(), 14)); ?></p>
                            <div class="es-product-footer">
                                <div class="es-price-box">
                                    <span class="es-price-current">$29</span>
                                    <span class="es-price-old">$49</span>
                                </div>
                                <a href="<?php the_permalink(); ?>" class="es-buy-btn"><?php esc_html_e('Buy Now', 'earthin-store'); ?> →</a>
                            </div>
                        </div>
                    </article>
            <?php
                endwhile;
            else :
                // Mock products for instant store preview
                $mock_items = [
                    ['title' => 'Earthin Page Builder Pro', 'cat' => 'BUILDER', 'price' => '$29', 'old' => '$49', 'icon' => '🧱', 'desc' => 'Visual drag & drop grid builder with dynamic post loops.'],
                    ['title' => 'Earthin Cache Accelerator', 'cat' => 'PERFORMANCE', 'price' => '$19', 'old' => '$39', 'icon' => '⚡', 'desc' => 'High-concurrency micro-caching and asset optimization.'],
                    ['title' => 'Earthin Login Shield & 2FA', 'cat' => 'SECURITY', 'price' => '$24', 'old' => '$45', 'icon' => '🛡️', 'desc' => 'Adaptive rate limiting, honeypot traps, and brute-force block.'],
                    ['title' => 'Earthin Dynamic UPI QR Engine', 'cat' => 'COMMERCE', 'price' => '$34', 'old' => '$59', 'icon' => '💳', 'desc' => 'Instant zero-commission UPI QR generation at checkout.'],
                    ['title' => 'Earthin Image Optimizer & WebP', 'cat' => 'PERFORMANCE', 'price' => '$15', 'old' => '$29', 'icon' => '🖼️', 'desc' => 'Lossless AVIF/WebP compression with auto-serve rewrites.'],
                    ['title' => 'Earthin Form Builder & Webhooks', 'cat' => 'FORMS', 'price' => '$22', 'old' => '$39', 'icon' => '📝', 'desc' => 'Multi-step responsive form builder with webhook integrations.'],
                ];

                foreach ($mock_items as $prod) :
            ?>
                    <article class="es-product-card">
                        <div class="es-product-thumb">
                            <span class="es-product-placeholder-icon"><?php echo esc_html($prod['icon']); ?></span>
                            <span class="es-sale-badge"><?php esc_html_e('POPULAR', 'earthin-store'); ?></span>
                        </div>
                        <div class="es-product-body">
                            <span class="es-product-category"><?php echo esc_html($prod['cat']); ?></span>
                            <h3 class="es-product-name"><a href="#buy"><?php echo esc_html($prod['title']); ?></a></h3>
                            <p class="es-product-desc"><?php echo esc_html($prod['desc']); ?></p>
                            <div class="es-product-footer">
                                <div class="es-price-box">
                                    <span class="es-price-current"><?php echo esc_html($prod['price']); ?></span>
                                    <span class="es-price-old"><?php echo esc_html($prod['old']); ?></span>
                                </div>
                                <a href="#buy" class="es-buy-btn"><?php esc_html_e('Buy Now', 'earthin-store'); ?> →</a>
                            </div>
                        </div>
                    </article>
            <?php
                endforeach;
            endif;
            ?>
        </div>
    </section>

</div>

<?php
get_footer();
