<?php
/**
 * Custom template tags for Earthin theme
 *
 * @package Earthin
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('earthin_posted_on')) :
    function earthin_posted_on(): void {
        $time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';
        if (get_the_time('U') !== get_the_modified_time('U')) {
            $time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time>';
        }

        $time_string = sprintf(
            $time_string,
            esc_attr(get_the_date(DATE_W3C)),
            esc_html(get_the_date())
        );

        echo '<span class="posted-on">' . sprintf(
            /* translators: %s: post date. */
            esc_html_x('Published on %s', 'post date', 'earthin'),
            '<a href="' . esc_url(get_permalink()) . '" rel="bookmark">' . $time_string . '</a>'
        ) . '</span>';
    }
endif;

if (!function_exists('earthin_posted_by')) :
    function earthin_posted_by(): void {
        echo '<span class="byline">' . sprintf(
            /* translators: %s: post author. */
            esc_html_x('By %s', 'post author', 'earthin'),
            '<span class="author vcard"><a class="url fn n" href="' . esc_url(get_author_posts_url(get_the_author_meta('ID'))) . '">' . esc_html(get_the_author()) . '</a></span>'
        ) . '</span>';
    }
endif;

if (!function_exists('earthin_entry_footer')) :
    function earthin_entry_footer(): void {
        if ('post' === get_post_type()) {
            $categories_list = get_the_category_list(esc_html__(', ', 'earthin'));
            if ($categories_list) {
                printf('<span class="cat-links">' . esc_html__('Categories: %s', 'earthin') . '</span> ', $categories_list);
            }

            $tags_list = get_the_tag_list('', esc_html__(', ', 'earthin'));
            if ($tags_list) {
                printf('<span class="tags-links">' . esc_html__('Tags: %s', 'earthin') . '</span>', $tags_list);
            }
        }
    }
endif;
