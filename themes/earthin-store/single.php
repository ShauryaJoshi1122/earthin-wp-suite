<?php
/**
 * Single product / post template for Earthin Store
 *
 * @package Earthin_Store
 */

get_header();
?>

<div class="es-container" style="padding-top: 3rem; padding-bottom: 5rem;">

    <?php while (have_posts()) : the_post(); ?>

        <article id="post-<?php the_ID(); ?>" <?php post_class('es-single-product'); ?>>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 3.5rem; margin-bottom: 4rem;">

                <!-- Product Gallery / Preview -->
                <div class="es-single-media-col">
                    <div style="background: #0f172a; border-radius: var(--es-radius); overflow: hidden; height: 380px; display: flex; align-items: center; justify-content: center;">
                        <?php if (has_post_thumbnail()) : ?>
                            <?php the_post_thumbnail('large', ['style' => 'width: 100%; height: 100%; object-fit: cover;']); ?>
                        <?php else : ?>
                            <span style="font-size: 5rem;">📦</span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Product Purchasing Info -->
                <div class="es-single-info-col">
                    <span style="display: inline-block; background: rgba(37, 99, 235, 0.1); color: var(--es-primary); font-size: 0.75rem; font-weight: 800; padding: 0.25rem 0.65rem; border-radius: 9999px; margin-bottom: 0.75rem;">
                        <?php esc_html_e('VERIFIED OFFICIAL RELEASE', 'earthin-store'); ?>
                    </span>
                    <h1 style="font-size: 2.25rem; font-weight: 900; margin: 0 0 0.85rem; line-height: 1.25;"><?php the_title(); ?></h1>

                    <div style="display: flex; align-items: center; gap: 0.5rem; color: #f59e0b; margin-bottom: 1.5rem;">
                        <span>★★★★★</span>
                        <span style="color: var(--es-text-muted); font-size: 0.85rem;">(4.9 / 5 from 180+ buyers)</span>
                    </div>

                    <div style="display: flex; align-items: baseline; gap: 0.75rem; margin-bottom: 1.75rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--es-border);">
                        <span style="font-size: 2.5rem; font-weight: 900; color: var(--es-text);">$29</span>
                        <span style="font-size: 1.15rem; color: var(--es-text-muted); text-decoration: line-through;">$49</span>
                        <span style="background: rgba(16, 185, 129, 0.15); color: var(--es-accent); font-size: 0.8rem; font-weight: 800; padding: 0.25rem 0.6rem; border-radius: 9999px;">
                            <?php esc_html_e('Save $20', 'earthin-store'); ?>
                        </span>
                    </div>

                    <div style="margin-bottom: 2rem;">
                        <button type="button" class="es-btn es-btn-primary" style="width: 100%; font-size: 1.05rem; padding: 0.85rem; border: none; cursor: pointer;" onclick="alert('<?php echo esc_js(__('Redirecting to instant checkout...', 'earthin-store')); ?>')">
                            <?php esc_html_e('Instant Checkout with UPI QR / Card', 'earthin-store'); ?> ⚡
                        </button>
                    </div>

                    <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.65rem; font-size: 0.9rem; color: var(--es-text);">
                        <li>✓ <strong><?php esc_html_e('Immediate ZIP Download', 'earthin-store'); ?></strong></li>
                        <li>✓ <?php esc_html_e('Valid for Unlimited Sites & Staging', 'earthin-store'); ?></li>
                        <li>✓ <?php esc_html_e('1 Year Priority Ticket Support & Updates', 'earthin-store'); ?></li>
                        <li>✓ <?php esc_html_e('100% Clean Code - No telemetry, no lock-in', 'earthin-store'); ?></li>
                    </ul>
                </div>

            </div>

            <!-- Detailed Description / Content -->
            <div style="background: var(--es-surface); border: 1px solid var(--es-border); border-radius: var(--es-radius); padding: 3rem; margin-bottom: 4rem;">
                <h2 style="font-size: 1.6rem; font-weight: 800; margin: 0 0 1.5rem;"><?php esc_html_e('Product Description & Documentation', 'earthin-store'); ?></h2>
                <div class="es-entry-content" style="line-height: 1.8; color: var(--es-text);">
                    <?php the_content(); ?>
                </div>
            </div>

            <?php
            if (comments_open() || get_comments_number()) :
                comments_template();
            endif;
            ?>

        </article>

    <?php endwhile; ?>

</div>

<?php
get_footer();
