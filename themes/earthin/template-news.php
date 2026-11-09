<?php
/**
 * Template Name: Earthin: News & Editorial Magazine
 * Template Post Type: page, post
 *
 * A modern, high-impact editorial magazine and news portal template featuring
 * a live breaking news ticker, lead featured story banner, 3-column top stories grid,
 * editorial stream with dynamic WP_Query, trending articles sidebar, and newsletter dispatch.
 *
 * @package Earthin
 * @author Shaurya Joshi
 * @link https://plugins.earthin.online
 */

get_header();
?>

<div class="earthin-news-portal">

    <!-- 1. BREAKING NEWS TICKER -->
    <section class="earthin-news-ticker-bar" aria-label="<?php esc_attr_e('Breaking News', 'earthin'); ?>">
        <div class="earthin-news-container">
            <div class="earthin-ticker-inner">
                <div class="earthin-ticker-label">
                    <span class="earthin-ticker-pulse"></span>
                    <span class="earthin-ticker-tag"><?php esc_html_e('BREAKING', 'earthin'); ?></span>
                </div>
                <div class="earthin-ticker-content">
                    <span class="earthin-ticker-headline">
                        <?php
                        $ticker_query = new WP_Query([
                            'posts_per_page'      => 1,
                            'ignore_sticky_posts' => 1,
                        ]);
                        if ($ticker_query->have_posts()) {
                            while ($ticker_query->have_posts()) {
                                $ticker_query->the_post();
                                echo '<a href="' . esc_url(get_permalink()) . '">' . esc_html(get_the_title()) . '</a>';
                            }
                            wp_reset_postdata();
                        } else {
                            echo '<a href="#latest">' . esc_html__('Earthin Web Suite v2.4 introduces 8 new high-performance theme templates and 29 native plugins.', 'earthin') . '</a>';
                        }
                        ?>
                    </span>
                </div>
                <div class="earthin-ticker-meta">
                    <time datetime="<?php echo esc_attr(date('c')); ?>"><?php echo esc_html(date_i18n(get_option('date_format'))); ?></time>
                </div>
            </div>
        </div>
    </section>

    <div class="earthin-news-container">

        <!-- 2. LEAD HERO & TOP STORIES GRID -->
        <section class="earthin-news-hero-grid">
            <?php
            $hero_query = new WP_Query([
                'posts_per_page'      => 3,
                'ignore_sticky_posts' => 1,
            ]);

            $post_count = 0;
            $has_hero_posts = $hero_query->have_posts();

            if ($has_hero_posts) :
                while ($hero_query->have_posts()) :
                    $hero_query->the_post();
                    $post_count++;
                    $categories = get_the_category();
                    $cat_name = !empty($categories) ? $categories[0]->name : __('General', 'earthin');

                    if ($post_count === 1) :
            ?>
                        <!-- Primary Lead Story -->
                        <article class="earthin-news-lead-card">
                            <div class="earthin-news-lead-media">
                                <?php if (has_post_thumbnail()) : ?>
                                    <a href="<?php the_permalink(); ?>">
                                        <?php the_post_thumbnail('large', ['class' => 'earthin-news-lead-img', 'alt' => get_the_title()]); ?>
                                    </a>
                                <?php else : ?>
                                    <div class="earthin-news-placeholder-media lead-gradient">
                                        <span class="earthin-media-icon">📰</span>
                                    </div>
                                <?php endif; ?>
                                <span class="earthin-news-cat-badge"><?php echo esc_html($cat_name); ?></span>
                            </div>
                            <div class="earthin-news-lead-content">
                                <div class="earthin-news-meta-row">
                                    <span class="earthin-news-author"><?php echo esc_html(get_the_author()); ?></span>
                                    <span class="earthin-meta-separator">•</span>
                                    <time class="earthin-news-date" datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date()); ?></time>
                                    <span class="earthin-meta-separator">•</span>
                                    <span class="earthin-news-read-time"><?php printf(esc_html__('%d min read', 'earthin'), max(1, (int) ceil(str_word_count(strip_tags(get_the_content())) / 200))); ?></span>
                                </div>
                                <h1 class="earthin-news-lead-title">
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                </h1>
                                <p class="earthin-news-lead-excerpt">
                                    <?php echo esc_html(wp_trim_words(get_the_excerpt(), 28)); ?>
                                </p>
                                <a href="<?php the_permalink(); ?>" class="earthin-news-read-btn">
                                    <?php esc_html_e('Read Full Coverage', 'earthin'); ?> →
                                </a>
                            </div>
                        </article>
                    <?php
                    elseif ($post_count === 2) :
                        echo '<div class="earthin-news-sub-column">';
                    ?>
                        <article class="earthin-news-sub-card">
                            <div class="earthin-news-sub-media">
                                <?php if (has_post_thumbnail()) : ?>
                                    <a href="<?php the_permalink(); ?>"><?php the_post_thumbnail('medium', ['class' => 'earthin-news-sub-img']); ?></a>
                                <?php else : ?>
                                    <div class="earthin-news-placeholder-media sub-gradient-1"><span class="earthin-media-icon">⚡</span></div>
                                <?php endif; ?>
                                <span class="earthin-news-cat-badge small"><?php echo esc_html($cat_name); ?></span>
                            </div>
                            <div class="earthin-news-sub-body">
                                <span class="earthin-news-date"><?php echo esc_html(get_the_date()); ?></span>
                                <h3 class="earthin-news-sub-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                            </div>
                        </article>
                    <?php
                    elseif ($post_count === 3) :
                    ?>
                        <article class="earthin-news-sub-card">
                            <div class="earthin-news-sub-media">
                                <?php if (has_post_thumbnail()) : ?>
                                    <a href="<?php the_permalink(); ?>"><?php the_post_thumbnail('medium', ['class' => 'earthin-news-sub-img']); ?></a>
                                <?php else : ?>
                                    <div class="earthin-news-placeholder-media sub-gradient-2"><span class="earthin-media-icon">🛡️</span></div>
                                <?php endif; ?>
                                <span class="earthin-news-cat-badge small"><?php echo esc_html($cat_name); ?></span>
                            </div>
                            <div class="earthin-news-sub-body">
                                <span class="earthin-news-date"><?php echo esc_html(get_the_date()); ?></span>
                                <h3 class="earthin-news-sub-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                            </div>
                        </article>
                    <?php
                        echo '</div>'; // end sub-column
                    endif;
                endwhile;
                if ($post_count === 2) {
                    echo '</div>'; // close if only 2 posts
                }
                wp_reset_postdata();
            else :
            ?>
                <!-- Fallback Lead & Sub Story when fresh install has 0 posts -->
                <article class="earthin-news-lead-card">
                    <div class="earthin-news-lead-media">
                        <div class="earthin-news-placeholder-media lead-gradient">
                            <span class="earthin-media-icon">🚀</span>
                        </div>
                        <span class="earthin-news-cat-badge"><?php esc_html_e('ECOSYSTEM', 'earthin'); ?></span>
                    </div>
                    <div class="earthin-news-lead-content">
                        <div class="earthin-news-meta-row">
                            <span class="earthin-news-author">Shaurya Joshi</span>
                            <span class="earthin-meta-separator">•</span>
                            <time class="earthin-news-date"><?php echo esc_html(date_i18n(get_option('date_format'))); ?></time>
                            <span class="earthin-meta-separator">•</span>
                            <span class="earthin-news-read-time">4 min read</span>
                        </div>
                        <h1 class="earthin-news-lead-title">
                            <a href="#coverage"><?php esc_html_e('Architecting Ultra-Fast Digital Publishing: The Modern WordPress & Microservices Blueprint', 'earthin'); ?></a>
                        </h1>
                        <p class="earthin-news-lead-excerpt">
                            <?php esc_html_e('Discover how modular theme architecture, distributed caching layers, and native privacy-preserving plugins deliver sub-100 millisecond response times without third-party vendor lock-in.', 'earthin'); ?>
                        </p>
                        <a href="#coverage" class="earthin-news-read-btn">
                            <?php esc_html_e('Read Full Story', 'earthin'); ?> →
                        </a>
                    </div>
                </article>

                <div class="earthin-news-sub-column">
                    <article class="earthin-news-sub-card">
                        <div class="earthin-news-sub-media">
                            <div class="earthin-news-placeholder-media sub-gradient-1"><span class="earthin-media-icon">⚡</span></div>
                            <span class="earthin-news-cat-badge small"><?php esc_html_e('PERFORMANCE', 'earthin'); ?></span>
                        </div>
                        <div class="earthin-news-sub-body">
                            <span class="earthin-news-date"><?php echo esc_html(date_i18n('M j, Y', strtotime('-1 day'))); ?></span>
                            <h3 class="earthin-news-sub-title"><a href="#sub1"><?php esc_html_e('Micro-Caching in PHP 8.2: Eliminating Database Bottlenecks under Traffic Spikes', 'earthin'); ?></a></h3>
                        </div>
                    </article>

                    <article class="earthin-news-sub-card">
                        <div class="earthin-news-sub-media">
                            <div class="earthin-news-placeholder-media sub-gradient-2"><span class="earthin-media-icon">🛡️</span></div>
                            <span class="earthin-news-cat-badge small"><?php esc_html_e('SECURITY', 'earthin'); ?></span>
                        </div>
                        <div class="earthin-news-sub-body">
                            <span class="earthin-news-date"><?php echo esc_html(date_i18n('M j, Y', strtotime('-2 days'))); ?></span>
                            <h3 class="earthin-news-sub-title"><a href="#sub2"><?php esc_html_e('Zero-Trust Authentication Shield: Protecting WordPress REST Endpoints', 'earthin'); ?></a></h3>
                        </div>
                    </article>
                </div>
            <?php endif; ?>
        </section>

        <!-- 3. MAIN EDITORIAL STREAM & TRENDING SIDEBAR -->
        <div class="earthin-news-main-layout">

            <!-- Primary Editorial Stream -->
            <main id="primary" class="earthin-news-feed" role="main">
                <div class="earthin-news-section-header">
                    <h2 class="earthin-section-title"><?php esc_html_e('Latest Dispatches & Analysis', 'earthin'); ?></h2>
                    <span class="earthin-section-subtitle"><?php esc_html_e('In-depth reports from our editorial team', 'earthin'); ?></span>
                </div>

                <div class="earthin-news-articles-grid">
                    <?php
                    $feed_query = new WP_Query([
                        'posts_per_page'      => 6,
                        'offset'              => $has_hero_posts ? 3 : 0,
                        'ignore_sticky_posts' => 1,
                    ]);

                    if ($feed_query->have_posts()) :
                        while ($feed_query->have_posts()) :
                            $feed_query->the_post();
                            $cats = get_the_category();
                            $cname = !empty($cats) ? $cats[0]->name : __('Dispatch', 'earthin');
                    ?>
                            <article class="earthin-news-article-card">
                                <div class="earthin-article-thumb">
                                    <?php if (has_post_thumbnail()) : ?>
                                        <a href="<?php the_permalink(); ?>"><?php the_post_thumbnail('medium_large', ['class' => 'earthin-article-img']); ?></a>
                                    <?php else : ?>
                                        <div class="earthin-article-placeholder"><span>📝</span></div>
                                    <?php endif; ?>
                                    <span class="earthin-news-cat-pill"><?php echo esc_html($cname); ?></span>
                                </div>
                                <div class="earthin-article-content">
                                    <div class="earthin-article-meta">
                                        <time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date()); ?></time>
                                        <span class="earthin-meta-separator">•</span>
                                        <span><?php echo esc_html(get_the_author()); ?></span>
                                    </div>
                                    <h3 class="earthin-article-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                    <p class="earthin-article-summary"><?php echo esc_html(wp_trim_words(get_the_excerpt(), 18)); ?></p>
                                </div>
                            </article>
                    <?php
                        endwhile;
                        wp_reset_postdata();
                    else :
                        // Sample News Articles for preview
                        $sample_news = [
                            [
                                'cat'     => 'INFRASTRUCTURE',
                                'title'   => 'Automated Asset Pipelines: Building Modern Block Themes with Zero Bloat',
                                'excerpt' => 'Modern block editors do not require 2 megabytes of bloated JavaScript bundles. Here is how clean semantic CSS wins every time.',
                                'date'    => 'Yesterday',
                                'author'  => 'Editorial Desk',
                                'icon'    => '⚡',
                            ],
                            [
                                'cat'     => 'ECOMMERCE',
                                'title'   => 'Instant UPI QR Checkout Engines: Why Direct Payments are Winning South Asia',
                                'excerpt' => 'How dynamic server-side UPI QR generation eliminates costly payment aggregator gateway fees for open-source digital shops.',
                                'date'    => '3 days ago',
                                'author'  => 'FinTech Desk',
                                'icon'    => '💳',
                            ],
                            [
                                'cat'     => 'DATABASE',
                                'title'   => 'Database Optimization Strategies for WordPress Sites with Over 1M Records',
                                'excerpt' => 'Practical index tuning, autoload option cleanup, and scheduled transient purges that keep queries lightning quick.',
                                'date'    => '4 days ago',
                                'author'  => 'Systems Team',
                                'icon'    => '🗄️',
                            ],
                            [
                                'cat'     => 'SECURITY',
                                'title'   => 'Hardening the WP-Admin Login Gateway Against Advanced Credential Stuffing',
                                'excerpt' => 'Implementing adaptive rate limiting, honeypot traps, and device fingerprint verification on standard authentication points.',
                                'date'    => '5 days ago',
                                'author'  => 'SecOps Desk',
                                'icon'    => '🔒',
                            ],
                        ];

                        foreach ($sample_news as $item) :
                    ?>
                            <article class="earthin-news-article-card">
                                <div class="earthin-article-thumb">
                                    <div class="earthin-article-placeholder"><span><?php echo esc_html($item['icon']); ?></span></div>
                                    <span class="earthin-news-cat-pill"><?php echo esc_html($item['cat']); ?></span>
                                </div>
                                <div class="earthin-article-content">
                                    <div class="earthin-article-meta">
                                        <time><?php echo esc_html($item['date']); ?></time>
                                        <span class="earthin-meta-separator">•</span>
                                        <span><?php echo esc_html($item['author']); ?></span>
                                    </div>
                                    <h3 class="earthin-article-title"><a href="#read"><?php echo esc_html($item['title']); ?></a></h3>
                                    <p class="earthin-article-summary"><?php echo esc_html($item['excerpt']); ?></p>
                                </div>
                            </article>
                    <?php
                        endforeach;
                    endif;
                    ?>
                </div>

                <!-- Page content if published -->
                <?php while (have_posts()) : the_post(); ?>
                    <?php if (get_the_content()) : ?>
                        <div class="earthin-editorial-editor-content">
                            <?php the_content(); ?>
                        </div>
                    <?php endif; ?>
                <?php endwhile; ?>
            </main>

            <!-- News Portal Sidebar -->
            <aside class="earthin-news-sidebar" role="complementary" aria-label="<?php esc_attr_e('Editorial Sidebar', 'earthin'); ?>">

                <!-- Trending / Editor's Pick Widget -->
                <div class="earthin-news-widget earthin-widget-trending">
                    <h3 class="earthin-widget-title">
                        <span class="earthin-widget-icon">🔥</span>
                        <?php esc_html_e('Trending This Week', 'earthin'); ?>
                    </h3>
                    <ol class="earthin-trending-list">
                        <li class="earthin-trending-item">
                            <span class="earthin-trending-rank">01</span>
                            <div class="earthin-trending-info">
                                <a href="#trend-1" class="earthin-trending-title"><?php esc_html_e('Headless vs Native Monolith: The 2026 Developer Consensus', 'earthin'); ?></a>
                                <span class="earthin-trending-meta"><?php esc_html_e('8.4k reads • Tech', 'earthin'); ?></span>
                            </div>
                        </li>
                        <li class="earthin-trending-item">
                            <span class="earthin-trending-rank">02</span>
                            <div class="earthin-trending-info">
                                <a href="#trend-2" class="earthin-trending-title"><?php esc_html_e('The Complete Guide to Full Site Editing & Block Hooks', 'earthin'); ?></a>
                                <span class="earthin-trending-meta"><?php esc_html_e('6.1k reads • Design', 'earthin'); ?></span>
                            </div>
                        </li>
                        <li class="earthin-trending-item">
                            <span class="earthin-trending-rank">03</span>
                            <div class="earthin-trending-info">
                                <a href="#trend-3" class="earthin-trending-title"><?php esc_html_e('Building Zero-Dependency Plugins with Pure PHP & CSS', 'earthin'); ?></a>
                                <span class="earthin-trending-meta"><?php esc_html_e('5.2k reads • Code', 'earthin'); ?></span>
                            </div>
                        </li>
                        <li class="earthin-trending-item">
                            <span class="earthin-trending-rank">04</span>
                            <div class="earthin-trending-info">
                                <a href="#trend-4" class="earthin-trending-title"><?php esc_html_e('Google Core Web Vitals 2026: Passing INP with Ease', 'earthin'); ?></a>
                                <span class="earthin-trending-meta"><?php esc_html_e('4.8k reads • SEO', 'earthin'); ?></span>
                            </div>
                        </li>
                    </ol>
                </div>

                <!-- Topic Categories Tag Cloud -->
                <div class="earthin-news-widget earthin-widget-topics">
                    <h3 class="earthin-widget-title">
                        <span class="earthin-widget-icon">🏷️</span>
                        <?php esc_html_e('Explore Topics', 'earthin'); ?>
                    </h3>
                    <div class="earthin-topics-cloud">
                        <a href="#topic-wordpress" class="earthin-topic-tag"><?php esc_html_e('WordPress', 'earthin'); ?> <span>28</span></a>
                        <a href="#topic-php" class="earthin-topic-tag"><?php esc_html_e('PHP 8.2', 'earthin'); ?> <span>19</span></a>
                        <a href="#topic-security" class="earthin-topic-tag"><?php esc_html_e('Security', 'earthin'); ?> <span>15</span></a>
                        <a href="#topic-performance" class="earthin-topic-tag"><?php esc_html_e('Performance', 'earthin'); ?> <span>22</span></a>
                        <a href="#topic-tailwind" class="earthin-topic-tag"><?php esc_html_e('CSS Architecture', 'earthin'); ?> <span>11</span></a>
                        <a href="#topic-devops" class="earthin-topic-tag"><?php esc_html_e('DevOps', 'earthin'); ?> <span>8</span></a>
                    </div>
                </div>

                <!-- Newsletter Subscription Widget -->
                <div class="earthin-news-widget earthin-widget-newsletter">
                    <div class="earthin-newsletter-card">
                        <span class="earthin-newsletter-badge"><?php esc_html_e('FREE DISPATCH', 'earthin'); ?></span>
                        <h4 class="earthin-newsletter-title"><?php esc_html_e('Earthin Morning Briefing', 'earthin'); ?></h4>
                        <p class="earthin-newsletter-desc"><?php esc_html_e('Curated web engineering, security audits, and WordPress ecosystem updates sent straight to your inbox every Thursday.', 'earthin'); ?></p>
                        <form class="earthin-newsletter-form" onsubmit="event.preventDefault(); alert('<?php echo esc_js(__('Thank you for subscribing to Earthin Daily Dispatch!', 'earthin')); ?>');">
                            <input type="email" class="earthin-newsletter-input" placeholder="<?php esc_attr_e('name@domain.com', 'earthin'); ?>" required />
                            <button type="submit" class="earthin-newsletter-submit"><?php esc_html_e('Subscribe Free', 'earthin'); ?></button>
                        </form>
                        <span class="earthin-newsletter-disclaimer"><?php esc_html_e('No spam. Unsubscribe anytime in 1 click.', 'earthin'); ?></span>
                    </div>
                </div>

            </aside>
        </div>

    </div>

</div>

<?php
get_footer();
