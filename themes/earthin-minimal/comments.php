<?php
/**
 * Comments template for Earthin Minimal
 *
 * @package Earthin_Minimal
 */

if (post_password_required()) {
    return;
}
?>

<div id="comments" class="em-comments-area">
    <?php if (have_comments()) : ?>
        <h3 class="em-comments-title" style="font-size: 1.4rem; font-weight: 800; margin: 0 0 1.5rem;">
            <?php
            $count = get_comments_number();
            if ($count === 1) {
                printf(esc_html__('1 Comment on &ldquo;%s&rdquo;', 'earthin-minimal'), get_the_title());
            } else {
                printf(esc_html(_nx('%1$s Comment', '%1$s Comments', $count, 'comments title', 'earthin-minimal')), number_format_i18n($count));
            }
            ?>
        </h3>

        <ol class="em-comment-list" style="list-style: none; padding: 0; margin: 0 0 2rem; display: flex; flex-direction: column; gap: 1.5rem;">
            <?php
            wp_list_comments([
                'style'       => 'ol',
                'short_ping'  => true,
                'avatar_size' => 48,
            ]);
            ?>
        </ol>

        <?php the_comments_pagination(); ?>

    <?php endif; ?>

    <?php if (!comments_open() && get_comments_number() && post_type_supports(get_post_type(), 'comments')) : ?>
        <p class="no-comments" style="color: var(--em-text-muted); font-style: italic;"><?php esc_html_e('Comments are closed.', 'earthin-minimal'); ?></p>
    <?php endif; ?>

    <?php comment_form(); ?>
</div>
