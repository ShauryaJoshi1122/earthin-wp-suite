<?php
/**
 * Template Name: Contact Page with Info Grid
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

<main id="primary" class="site-main earthin-contact-main">
    <?php
    while (have_posts()) :
        the_post();
        ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class('earthin-contact-article'); ?>>
            <!-- Contact Page Hero Header -->
            <header class="entry-header earthin-contact-header">
                <span class="earthin-badge"><?php esc_html_e('Get In Touch', 'earthin'); ?></span>
                <?php the_title('<h1 class="entry-title earthin-contact-title">', '</h1>'); ?>
                <p class="earthin-contact-subtitle">
                    <?php esc_html_e('Have questions, ideas, or need assistance? Reach out to our team below and we will get back to you promptly.', 'earthin'); ?>
                </p>
            </header>

            <div class="earthin-contact-layout">
                <!-- Left Column: Contact Cards & Info -->
                <div class="earthin-contact-info-col">
                    <div class="earthin-contact-cards-grid">
                        <!-- Email Card -->
                        <div class="earthin-contact-card">
                            <div class="earthin-contact-icon">✉️</div>
                            <div class="earthin-contact-card-content">
                                <h3 class="earthin-contact-card-title"><?php esc_html_e('Email Inquiries', 'earthin'); ?></h3>
                                <p class="earthin-contact-card-desc"><?php esc_html_e('Direct email for client support and general questions.', 'earthin'); ?></p>
                                <a href="mailto:<?php echo esc_attr(get_option('admin_email')); ?>" class="earthin-contact-link">
                                    <?php echo esc_html(get_option('admin_email')); ?> &rarr;
                                </a>
                            </div>
                        </div>

                        <!-- Phone / Call Card -->
                        <div class="earthin-contact-card">
                            <div class="earthin-contact-icon">📞</div>
                            <div class="earthin-contact-card-content">
                                <h3 class="earthin-contact-card-title"><?php esc_html_e('Direct Hotline', 'earthin'); ?></h3>
                                <p class="earthin-contact-card-desc"><?php esc_html_e('Mon - Fri from 9am to 6pm local time.', 'earthin'); ?></p>
                                <span class="earthin-contact-text">+1 (800) 555-EARTH</span>
                            </div>
                        </div>

                        <!-- Headquarters Card -->
                        <div class="earthin-contact-card">
                            <div class="earthin-contact-icon">📍</div>
                            <div class="earthin-contact-card-content">
                                <h3 class="earthin-contact-card-title"><?php esc_html_e('Digital Headquarters', 'earthin'); ?></h3>
                                <p class="earthin-contact-card-desc"><?php esc_html_e('Online globally across all time zones with 24/7 cloud monitoring.', 'earthin'); ?></p>
                                <span class="earthin-contact-text"><?php echo esc_html(wp_parse_url(home_url(), PHP_URL_HOST)); ?></span>
                            </div>
                        </div>

                        <!-- Operating Hours Badge Card -->
                        <div class="earthin-contact-card earthin-hours-card">
                            <div class="earthin-contact-icon">🕒</div>
                            <div class="earthin-contact-card-content">
                                <h3 class="earthin-contact-card-title"><?php esc_html_e('Availability & SLAs', 'earthin'); ?></h3>
                                <div class="earthin-hours-list">
                                    <div class="earthin-hours-row">
                                        <span><?php esc_html_e('Monday – Friday:', 'earthin'); ?></span>
                                        <strong>9:00 AM – 6:00 PM</strong>
                                    </div>
                                    <div class="earthin-hours-row">
                                        <span><?php esc_html_e('Weekend Inquiries:', 'earthin'); ?></span>
                                        <span class="earthin-pill-muted"><?php esc_html_e('Reviewed Mon morning', 'earthin'); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick FAQ Box -->
                    <div class="earthin-contact-faq-box">
                        <h3 class="earthin-faq-heading">💡 <?php esc_html_e('Frequently Asked Questions', 'earthin'); ?></h3>
                        <details class="earthin-faq-item">
                            <summary class="earthin-faq-question"><?php esc_html_e('What is the typical response turnaround?', 'earthin'); ?></summary>
                            <p class="earthin-faq-answer"><?php esc_html_e('We strive to respond to all inquiries within 24 business hours. Critical issues from existing clients are prioritized immediately.', 'earthin'); ?></p>
                        </details>
                        <details class="earthin-faq-item">
                            <summary class="earthin-faq-question"><?php esc_html_e('Can I schedule a live consultation?', 'earthin'); ?></summary>
                            <p class="earthin-faq-answer"><?php esc_html_e('Yes! Mention your preferred meeting times in your message and our team will coordinate a virtual session.', 'earthin'); ?></p>
                        </details>
                    </div>
                </div>

                <!-- Right Column: Interactive Form / Content Area -->
                <div class="earthin-contact-form-col">
                    <div class="earthin-contact-form-card">
                        <div class="earthin-form-card-header">
                            <h2 class="earthin-form-title"><?php esc_html_e('Send a Message', 'earthin'); ?></h2>
                            <span class="earthin-sla-pill">⚡ <?php esc_html_e('Fast Response', 'earthin'); ?></span>
                        </div>

                        <div class="entry-content earthin-contact-body">
                            <?php
                            $page_content = get_the_content();
                            if (!empty(trim($page_content))) {
                                the_content();
                            } else {
                                ?>
                                <p style="color:var(--earthin-text-muted); font-size:0.95rem; margin-bottom:1.5rem;">
                                    <?php esc_html_e('Fill out the form below or paste any WordPress form shortcode (like Earthin Form Builder [earthin_form]) into this page to accept inquiries.', 'earthin'); ?>
                                </p>
                                <form class="earthin-default-contact-form" method="post" action="#primary">
                                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">
                                        <div>
                                            <label style="display:block; font-weight:600; font-size:0.875rem; margin-bottom:6px;"><?php esc_html_e('Your Name *', 'earthin'); ?></label>
                                            <input type="text" name="contact_name" required placeholder="Jane Doe" style="width:100%; padding:10px 14px; border:1px solid var(--earthin-border); border-radius:8px; font-size:0.95rem;">
                                        </div>
                                        <div>
                                            <label style="display:block; font-weight:600; font-size:0.875rem; margin-bottom:6px;"><?php esc_html_e('Your Email *', 'earthin'); ?></label>
                                            <input type="email" name="contact_email" required placeholder="jane@example.com" style="width:100%; padding:10px 14px; border:1px solid var(--earthin-border); border-radius:8px; font-size:0.95rem;">
                                        </div>
                                    </div>
                                    <div style="margin-bottom:16px;">
                                        <label style="display:block; font-weight:600; font-size:0.875rem; margin-bottom:6px;"><?php esc_html_e('Subject', 'earthin'); ?></label>
                                        <input type="text" name="contact_subject" placeholder="Project Inquiry / General Question" style="width:100%; padding:10px 14px; border:1px solid var(--earthin-border); border-radius:8px; font-size:0.95rem;">
                                    </div>
                                    <div style="margin-bottom:20px;">
                                        <label style="display:block; font-weight:600; font-size:0.875rem; margin-bottom:6px;"><?php esc_html_e('Message *', 'earthin'); ?></label>
                                        <textarea name="contact_message" rows="5" required placeholder="How can we help you?" style="width:100%; padding:10px 14px; border:1px solid var(--earthin-border); border-radius:8px; font-size:0.95rem;"></textarea>
                                    </div>
                                    <button type="submit" class="earthin-read-more" style="width:100%; justify-content:center; padding:12px 24px; font-size:1rem; cursor:pointer; border:none;">
                                        <?php esc_html_e('Send Message →', 'earthin'); ?>
                                    </button>
                                </form>
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </article>
        <?php
    endwhile;
    ?>
</main>

<?php
get_footer();
