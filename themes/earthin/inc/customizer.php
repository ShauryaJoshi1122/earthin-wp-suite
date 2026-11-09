<?php
/**
 * Earthin Theme Customizer Controls
 *
 * @package Earthin
 */

if (!defined('ABSPATH')) {
    exit;
}

function earthin_customize_register(WP_Customize_Manager $wp_customize): void {
    // Theme Options Section
    $wp_customize->add_section('earthin_theme_options', [
        'title'    => __('Earthin Global Settings', 'earthin'),
        'priority' => 30,
    ]);

    // Primary Brand Color
    $wp_customize->add_setting('earthin_primary_color', [
        'default'           => '#059669',
        'sanitize_callback' => 'sanitize_hex_color',
        'transport'         => 'refresh',
    ]);
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'earthin_primary_color', [
        'label'    => __('Primary Accent Color', 'earthin'),
        'section'  => 'earthin_theme_options',
        'settings' => 'earthin_primary_color',
    ]));

    // Container Width
    $wp_customize->add_setting('earthin_container_width', [
        'default'           => 1200,
        'sanitize_callback' => 'absint',
        'transport'         => 'refresh',
    ]);
    $wp_customize->add_control('earthin_container_width', [
        'label'    => __('Container Max Width (px)', 'earthin'),
        'section'  => 'earthin_theme_options',
        'type'     => 'number',
        'input_attrs' => [
            'min'  => 800,
            'max'  => 1600,
            'step' => 20,
        ],
    ]);

    // Footer Copyright Text
    $wp_customize->add_setting('earthin_footer_text', [
        'default'           => sprintf('Proudly powered by WordPress & Earthin Suite by %s', 'Shaurya Joshi'),
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'refresh',
    ]);
    $wp_customize->add_control('earthin_footer_text', [
        'label'    => __('Footer Copyright Text', 'earthin'),
        'section'  => 'earthin_theme_options',
        'type'     => 'text',
    ]);
}
add_action('customize_register', 'earthin_customize_register');

function earthin_customizer_css(): void {
    $primary = get_theme_mod('earthin_primary_color', '#059669');
    $container = get_theme_mod('earthin_container_width', 1200);

    $custom_css = "
        :root {
            --earthin-primary: " . esc_attr($primary) . ";
            --earthin-max-width: " . absint($container) . "px;
        }
    ";
    wp_add_inline_style('earthin-style', $custom_css);
}
add_action('wp_enqueue_scripts', 'earthin_customizer_css');
