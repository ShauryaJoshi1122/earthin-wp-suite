<?php
/**
 * Plugin Name: Earthin SVG Safe Sanitizer
 * Plugin URI: https://plugins.earthin.online/plugins/earthin-svg-support
 * Description: Enables SVG uploads in the Media Library with an automated XML sanitizer blocking scripts, XXE attacks, and inline malicious handlers.
 * Version: 1.0.0
 * Author: Shaurya Joshi
 * Author URI: https://plugins.earthin.online
 * Support URI: https://plugins.earthin.online/support
 * License: GPL-2.0-or-later
 * Text Domain: earthin-svg-support
 * Requires at least: 6.4
 * Requires PHP: 8.1
 */

if (!defined('ABSPATH')) {
    exit;
}

class Earthin_SVG_Support {
    private static ?Earthin_SVG_Support $instance = null;

    public static function get_instance(): Earthin_SVG_Support {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_filter('upload_mimes', [$this, 'allow_svg_mime_type']);
        add_filter('wp_handle_upload_prefilter', [$this, 'sanitize_svg_upload']);
        add_filter('wp_check_filetype_and_ext', [$this, 'fix_svg_filetype_check'], 10, 4);
        add_action('admin_head', [$this, 'inject_media_grid_svg_style']);
    }

    public function allow_svg_mime_type(array $mimes): array {
        if (current_user_can('upload_files')) {
            $mimes['svg'] = 'image/svg+xml';
            $mimes['svgz'] = 'image/svg+xml';
        }
        return $mimes;
    }

    public function fix_svg_filetype_check($data, $file, $filename, $mimes) {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if ('svg' === $ext) {
            $data['ext'] = 'svg';
            $data['type'] = 'image/svg+xml';
            $data['proper_filename'] = $filename;
        }
        return $data;
    }

    public function sanitize_svg_upload(array $file): array {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ('svg' !== $ext) {
            return $file;
        }

        $filepath = $file['tmp_name'];
        if (!is_file($filepath)) {
            return $file;
        }

        $content = file_get_contents($filepath);
        if (false === $content) {
            $file['error'] = __('Unable to read SVG file.', 'earthin-svg-support');
            return $file;
        }

        // XXE and Entity Expansion Guard
        if (preg_match('/<!ENTITY/i', $content) || preg_match('/<!DOCTYPE[^>]*\[/i', $content)) {
            $file['error'] = __('Security block: SVG contains forbidden XML entity or DTD definition.', 'earthin-svg-support');
            return $file;
        }

        // Sanitize dangerous elements and attributes
        $sanitized = $this->sanitize_svg_content($content);
        if (!$sanitized) {
            $file['error'] = __('Security block: Malicious script tags or event handlers detected in SVG.', 'earthin-svg-support');
            return $file;
        }

        file_put_contents($filepath, $sanitized);
        return $file;
    }

    private function sanitize_svg_content(string $svg): ?string {
        // Disallow dangerous tags
        $forbidden_tags = ['script', 'foreignobject', 'iframe', 'embed', 'object', 'applet', 'meta', 'link'];
        foreach ($forbidden_tags as $tag) {
            if (preg_match('/<\s*' . $tag . '\b[^>]*>/i', $svg)) {
                return null;
            }
        }

        // Strip inline event handlers (e.g. onload, onerror, onclick)
        $svg = preg_replace('/\s+on[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $svg);

        // Strip javascript: protocols in href / xlink:href
        $svg = preg_replace('/(href|xlink:href)\s*=\s*["\']\s*javascript:[^"\']*["\']/i', '', $svg);

        // Strip data: URIs containing base64 scripts
        $svg = preg_replace('/data:\s*text\/html/i', '', $svg);

        return $svg;
    }

    public function inject_media_grid_svg_style(): void {
        echo '<style>
            .attachment .thumbnail img[src$=".svg"],
            .media-icon img[src$=".svg"] {
                width: 100% !important;
                height: auto !important;
            }
        </style>';
    }
}

add_action('plugins_loaded', ['Earthin_SVG_Support', 'get_instance']);

add_filter('plugin_action_links_' . plugin_basename(__FILE__), function($links) {
    $media_link = '<a href="' . esc_url(admin_url('upload.php')) . '">' . __('Media Library', 'earthin-svg-support') . '</a>';
    array_unshift($links, $media_link);
    return $links;
});
