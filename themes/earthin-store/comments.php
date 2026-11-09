<?php
/**
 * Comments template for Earthin Store
 *
 * @package Earthin_Store
 */

if (post_password_required()) {
    return;
}
?>

<div id="comments" class="es-comments-area" style="margin-top: 3rem; padding-top: 2rem; border-top: 1px solid var(--es-border);">
    <?php if (have_comments()) : ?>
        <h3 class="es-comments-title" style="font-size: 1.4rem; font-weight: 800; margin: 0 0 1.5rem;">
            <?php
            $count = get_comments_number();
            if ($count === 1) {
                printf(esc_html__('1 Customer Review on &ldquo;%s&rdquo;', 'earthin-store'), get_the_title());
            } else {
                printf(esc_html(_nx('%1$s Customer Review', '%1$s Customer Reviews', $count, 'reviews title', 'earthin-store')), number_format_i18n($count));
            }
            ?>
        </h3>

        <ol class="es-comment-list" style="list-style: none; padding: 0; margin: 0 0 2rem; display: flex; flex-direction: column; gap: 1.5rem;">
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
        <p class="no-comments" style="color: var(--es-text-muted); font-style: italic;"><?php esc_html_e('Reviews are closed.', 'earthin-store'); ?></p>
    <?php endif; ?>

    <?php comment_form(); ?>
</div>
