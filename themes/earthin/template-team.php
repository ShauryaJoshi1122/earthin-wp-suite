<?php
/**
 * Template Name: Earthin: Team & Careers
 * Template Post Type: page, post
 *
 * Professional Team & Careers template showcasing core contributors,
 * company values & culture, and active open positions with application links.
 *
 * @package Earthin
 * @author Shaurya Joshi
 * @link https://plugins.earthin.online
 */

get_header();
?>

<div class="earthin-team-page">

    <!-- 1. HERO HEADER -->
    <header class="earthin-team-hero">
        <div class="earthin-container text-center">
            <span class="earthin-hero-pill"><?php esc_html_e('PEOPLE & CULTURE', 'earthin'); ?></span>
            <h1 class="earthin-team-title"><?php esc_html_e('Meet the Builders Powering Earthin', 'earthin'); ?></h1>
            <p class="earthin-team-subtitle">
                <?php esc_html_e('A distributed team of engineers, designers, and open-source creators committed to building the fastest, most reliable WordPress ecosystem on earth.', 'earthin'); ?>
            </p>
        </div>
    </header>

    <div class="earthin-container">

        <!-- 2. LEADERSHIP & CORE CONTRIBUTORS -->
        <section class="earthin-team-section">
            <div class="earthin-section-header text-center">
                <span class="earthin-badge"><?php esc_html_e('LEADERSHIP & ARCHITECTURE', 'earthin'); ?></span>
                <h2 class="earthin-section-title"><?php esc_html_e('Core Contributors', 'earthin'); ?></h2>
            </div>

            <div class="earthin-team-grid">

                <!-- Member 1: Founder & Architect -->
                <div class="earthin-member-card">
                    <div class="earthin-member-avatar-wrap">
                        <div class="earthin-avatar-placeholder bg-emerald">SJ</div>
                        <span class="earthin-member-status" title="<?php esc_attr_e('Active Core Maintainer', 'earthin'); ?>"></span>
                    </div>
                    <div class="earthin-member-info">
                        <h3 class="earthin-member-name">Shaurya Joshi</h3>
                        <span class="earthin-member-role"><?php esc_html_e('Founder & Lead Architect', 'earthin'); ?></span>
                        <p class="earthin-member-bio">
                            <?php esc_html_e('Passionate software engineer and systems architect specializing in high-performance web systems, WordPress modular architecture, and modern microservices.', 'earthin'); ?>
                        </p>
                        <div class="earthin-member-socials">
                            <a href="https://github.com/ShauryaJoshi1122" target="_blank" rel="noopener noreferrer" class="earthin-social-icon" aria-label="GitHub">🐙</a>
                            <a href="https://plugins.earthin.online" target="_blank" rel="noopener noreferrer" class="earthin-social-icon" aria-label="Website">🌐</a>
                            <a href="#twitter" class="earthin-social-icon" aria-label="Twitter">🐦</a>
                        </div>
                    </div>
                </div>

                <!-- Member 2: Principal Systems Engineer -->
                <div class="earthin-member-card">
                    <div class="earthin-member-avatar-wrap">
                        <div class="earthin-avatar-placeholder bg-blue">AR</div>
                        <span class="earthin-member-status" title="<?php esc_attr_e('Active Contributor', 'earthin'); ?>"></span>
                    </div>
                    <div class="earthin-member-info">
                        <h3 class="earthin-member-name">Aarav Roy</h3>
                        <span class="earthin-member-role"><?php esc_html_e('Principal Systems Engineer', 'earthin'); ?></span>
                        <p class="earthin-member-bio">
                            <?php esc_html_e('Focuses on database caching strategies, Redis cluster topologies, memory leak diagnostics, and micro-optimization for high-concurrency environments.', 'earthin'); ?>
                        </p>
                        <div class="earthin-member-socials">
                            <a href="#github" class="earthin-social-icon" aria-label="GitHub">🐙</a>
                            <a href="#linkedin" class="earthin-social-icon" aria-label="LinkedIn">💼</a>
                        </div>
                    </div>
                </div>

                <!-- Member 3: Head of UI/UX & Design -->
                <div class="earthin-member-card">
                    <div class="earthin-member-avatar-wrap">
                        <div class="earthin-avatar-placeholder bg-purple">PV</div>
                        <span class="earthin-member-status" title="<?php esc_attr_e('Active Contributor', 'earthin'); ?>"></span>
                    </div>
                    <div class="earthin-member-info">
                        <h3 class="earthin-member-name">Priya Verma</h3>
                        <span class="earthin-member-role"><?php esc_html_e('Design Director & Design Systems', 'earthin'); ?></span>
                        <p class="earthin-member-bio">
                            <?php esc_html_e('Crafts intuitive user experiences, accessibility standards (WCAG AAA), and semantic design tokens across Earthin theme templates and admin interfaces.', 'earthin'); ?>
                        </p>
                        <div class="earthin-member-socials">
                            <a href="#dribbble" class="earthin-social-icon" aria-label="Dribbble">🎨</a>
                            <a href="#twitter" class="earthin-social-icon" aria-label="Twitter">🐦</a>
                        </div>
                    </div>
                </div>

                <!-- Member 4: SecOps & Penetration Testing -->
                <div class="earthin-member-card">
                    <div class="earthin-member-avatar-wrap">
                        <div class="earthin-avatar-placeholder bg-red">MK</div>
                        <span class="earthin-member-status" title="<?php esc_attr_e('Active Contributor', 'earthin'); ?>"></span>
                    </div>
                    <div class="earthin-member-info">
                        <h3 class="earthin-member-name">Mihir Kulkarni</h3>
                        <span class="earthin-member-role"><?php esc_html_e('SecOps & Threat Intelligence', 'earthin'); ?></span>
                        <p class="earthin-member-bio">
                            <?php esc_html_e('Leads zero-trust architecture, automated penetration tests, vulnerability audits, and rate-limiting modules for the Login Shield and security plugins.', 'earthin'); ?>
                        </p>
                        <div class="earthin-member-socials">
                            <a href="#security" class="earthin-social-icon" aria-label="Security Key">🔑</a>
                            <a href="#github" class="earthin-social-icon" aria-label="GitHub">🐙</a>
                        </div>
                    </div>
                </div>

            </div>
        </section>

        <!-- 3. CULTURE & PERKS -->
        <section class="earthin-culture-section">
            <div class="earthin-section-header text-center">
                <span class="earthin-badge"><?php esc_html_e('WHY WORK WITH US', 'earthin'); ?></span>
                <h2 class="earthin-section-title"><?php esc_html_e('Built on Autonomy, Speed & Freedom', 'earthin'); ?></h2>
            </div>

            <div class="earthin-perks-grid">
                <div class="earthin-perk-card">
                    <div class="earthin-perk-icon">🌍</div>
                    <h4 class="earthin-perk-title"><?php esc_html_e('100% Remote & Asynchronous', 'earthin'); ?></h4>
                    <p class="earthin-perk-desc"><?php esc_html_e('Work from wherever you are happiest and most productive. We value results over arbitrary hours seated at a desk.', 'earthin'); ?></p>
                </div>

                <div class="earthin-perk-card">
                    <div class="earthin-perk-icon">💻</div>
                    <h4 class="earthin-perk-title"><?php esc_html_e('Top-Tier Hardware & Setup', 'earthin'); ?></h4>
                    <p class="earthin-perk-desc"><?php esc_html_e('Generous equipment budget for your dream workstation, monitors, ergonomic seating, and local dev environments.', 'earthin'); ?></p>
                </div>

                <div class="earthin-perk-card">
                    <div class="earthin-perk-icon">📚</div>
                    <h4 class="earthin-perk-title"><?php esc_html_e('Learning & Conference Stipend', 'earthin'); ?></h4>
                    <p class="earthin-perk-desc"><?php esc_html_e('$2,000 annual budget for books, technical courses, certifications, and international WordCamp travel.', 'earthin'); ?></p>
                </div>

                <div class="earthin-perk-card">
                    <div class="earthin-perk-icon">🏖️</div>
                    <h4 class="earthin-perk-title"><?php esc_html_e('Unlimited Flexible Time Off', 'earthin'); ?></h4>
                    <p class="earthin-perk-desc"><?php esc_html_e('Mandatory minimum 4 weeks of vacation per year to ensure everyone stays energized, healthy, and inspired.', 'earthin'); ?></p>
                </div>
            </div>
        </section>

        <!-- 4. OPEN POSITIONS / CAREER BOARD -->
        <section class="earthin-careers-section" id="open-roles">
            <div class="earthin-section-header text-center">
                <span class="earthin-badge"><?php esc_html_e('JOIN OUR MISSION', 'earthin'); ?></span>
                <h2 class="earthin-section-title"><?php esc_html_e('Open Positions', 'earthin'); ?></h2>
                <p class="earthin-section-subtitle"><?php esc_html_e('Help us reinvent WordPress tooling and empowering millions of publishers worldwide.', 'earthin'); ?></p>
            </div>

            <div class="earthin-jobs-list">

                <!-- Job 1 -->
                <div class="earthin-job-card">
                    <div class="earthin-job-meta-col">
                        <div class="earthin-job-tags">
                            <span class="earthin-job-tag dept"><?php esc_html_e('Engineering', 'earthin'); ?></span>
                            <span class="earthin-job-tag loc"><?php esc_html_e('Remote (Worldwide)', 'earthin'); ?></span>
                            <span class="earthin-job-tag type"><?php esc_html_e('Full-Time', 'earthin'); ?></span>
                        </div>
                        <h3 class="earthin-job-title"><?php esc_html_e('Senior WordPress Core & Plugin Engineer', 'earthin'); ?></h3>
                        <p class="earthin-job-snippet"><?php esc_html_e('Architect next-generation WordPress block plugins, micro-caching drivers, and high-performance REST APIs.', 'earthin'); ?></p>
                    </div>
                    <div class="earthin-job-cta">
                        <a href="mailto:careers@earthin.online?subject=Application:%20Senior%20WordPress%20Engineer" class="earthin-btn earthin-btn-primary">
                            <?php esc_html_e('Apply Now', 'earthin'); ?> →
                        </a>
                    </div>
                </div>

                <!-- Job 2 -->
                <div class="earthin-job-card">
                    <div class="earthin-job-meta-col">
                        <div class="earthin-job-tags">
                            <span class="earthin-job-tag dept"><?php esc_html_e('Product & Frontend', 'earthin'); ?></span>
                            <span class="earthin-job-tag loc"><?php esc_html_e('Remote (Worldwide)', 'earthin'); ?></span>
                            <span class="earthin-job-tag type"><?php esc_html_e('Full-Time', 'earthin'); ?></span>
                        </div>
                        <h3 class="earthin-job-title"><?php esc_html_e('Full Stack Next.js & React Developer', 'earthin'); ?></h3>
                        <p class="earthin-job-snippet"><?php esc_html_e('Build and maintain the Earthin Plugin Hub web platform, catalog indexing, automated testing pipelines, and payments.', 'earthin'); ?></p>
                    </div>
                    <div class="earthin-job-cta">
                        <a href="mailto:careers@earthin.online?subject=Application:%20Full%20Stack%20Next.js%20Developer" class="earthin-btn earthin-btn-primary">
                            <?php esc_html_e('Apply Now', 'earthin'); ?> →
                        </a>
                    </div>
                </div>

                <!-- Job 3 -->
                <div class="earthin-job-card">
                    <div class="earthin-job-meta-col">
                        <div class="earthin-job-tags">
                            <span class="earthin-job-tag dept"><?php esc_html_e('Documentation & DevRel', 'earthin'); ?></span>
                            <span class="earthin-job-tag loc"><?php esc_html_e('Remote (Worldwide)', 'earthin'); ?></span>
                            <span class="earthin-job-tag type"><?php esc_html_e('Full-Time / Contract', 'earthin'); ?></span>
                        </div>
                        <h3 class="earthin-job-title"><?php esc_html_e('Technical Writer & Documentation Specialist', 'earthin'); ?></h3>
                        <p class="earthin-job-snippet"><?php esc_html_e('Write world-class developer tutorials, interactive code samples, API specifications, and troubleshooting playbooks.', 'earthin'); ?></p>
                    </div>
                    <div class="earthin-job-cta">
                        <a href="mailto:careers@earthin.online?subject=Application:%20Technical%20Writer" class="earthin-btn earthin-btn-primary">
                            <?php esc_html_e('Apply Now', 'earthin'); ?> →
                        </a>
                    </div>
                </div>

            </div>

            <!-- General Inquiries -->
            <div class="earthin-general-apply text-center">
                <p><?php esc_html_e('Do not see a role that fits your background? We are always on the lookout for remarkable talent.', 'earthin'); ?></p>
                <a href="mailto:careers@earthin.online?subject=General%20Application" class="earthin-link-arrow">
                    <?php esc_html_e('Send an open application to our engineering leads', 'earthin'); ?> →
                </a>
            </div>
        </section>

        <!-- Page editor content if any -->
        <?php while (have_posts()) : the_post(); ?>
            <?php if (get_the_content()) : ?>
                <div class="earthin-team-custom-content">
                    <?php the_content(); ?>
                </div>
            <?php endif; ?>
        <?php endwhile; ?>

    </div>

</div>

<?php
get_footer();
