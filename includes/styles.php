<?php
if (!defined('ABSPATH')) {
    exit;
}

// Enqueue plugin styles
function dpm_enqueue_custom_styles() {
    wp_enqueue_style('dpm-custom-style', plugin_dir_url(__FILE__) . '../assets/style.css');
}
add_action('wp_enqueue_scripts', 'dpm_enqueue_custom_styles');
