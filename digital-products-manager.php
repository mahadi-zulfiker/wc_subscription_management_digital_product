<?php
/*
Plugin Name: Digital Products Manager
Description: Manage digital products with subscription-based downloads.
Version: 1.1
Author: Amitav Roy Chowdhury
*/

if (!defined('ABSPATH')) {
    exit;
}

// Include separate files for better structure
include_once plugin_dir_path(__FILE__) . 'includes/digital-products.php';
include_once plugin_dir_path(__FILE__) . 'includes/subscriptions.php';
include_once plugin_dir_path(__FILE__) . 'includes/woocommerce-integration.php';
include_once plugin_dir_path(__FILE__) . 'includes/styles.php';

// Enqueue styles
function dpm_enqueue_styles() {
    wp_enqueue_style('dpm-style', plugin_dir_url(__FILE__) . 'assets/style.css');
}
function dpm_exclude_category_from_shop($query) {
    if (!is_admin() && $query->is_main_query() && is_shop()) {
        $query->set('tax_query', array(
            array(
                'taxonomy' => 'product_cat',
                'field'    => 'slug', 
                'terms'    => array('packages'), 
                'operator' => 'NOT IN',
            ),
        ));
    }
}
add_action('pre_get_posts', 'dpm_exclude_category_from_shop');

add_action('wp_enqueue_scripts', 'dpm_enqueue_styles');
