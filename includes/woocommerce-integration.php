<?php
if (!defined('ABSPATH')) {
    exit;
}

// Generate WooCommerce Checkout URL for Subscription
function dpm_get_checkout_url($subscription_id) {
    // Get subscription price and SKU
    $price = get_post_meta($subscription_id, '_dpm_price', true);
    $sku = 'subscription_' . $subscription_id; // Unique SKU for each subscription

    if (class_exists('WooCommerce')) {
        // Check if product already exists
        $existing_product_id = get_posts(array(
            'post_type'   => 'product',
            'meta_key'    => '_dpm_subscription_id',
            'meta_value'  => $subscription_id,
            'fields'      => 'ids',
            'posts_per_page' => 1
        ));

        if (!empty($existing_product_id)) {
            $product = wc_get_product($existing_product_id[0]);
        } else {
            // Create a new WooCommerce product
            $product_id = wp_insert_post(array(
                'post_title'   => get_the_title($subscription_id),
                'post_type'    => 'product',
                'post_status'  => 'publish',
                'meta_input'   => array(
                    '_price'          => $price,
                    '_regular_price'  => $price,
                    '_sku'            => $sku,
                    '_dpm_subscription_id' => $subscription_id,
                ),
            ));

            wp_set_object_terms($product_id, 'simple', 'product_type');
            $product = wc_get_product($product_id);
        }

        if ($product) {
            return wc_get_checkout_url() . '?add-to-cart=' . $product->get_id();
        }
    }

    return '#';
}

// Store Subscription Data when an order is completed
function dpm_store_user_subscription_from_order($order_id) {
    $order = wc_get_order($order_id);
    $user_id = $order->get_user_id();

    if (!$user_id) return; // Ensure user is logged in

    foreach ($order->get_items() as $item) {
        $product = $item->get_product();
        if (!$product) continue;

        // Check if the product is a subscription
        $subscription_id = get_post_meta($product->get_id(), '_dpm_subscription_id', true);
        if ($subscription_id) {
            dpm_store_user_subscription($user_id, $subscription_id);
        }
    }
}
add_action('woocommerce_order_status_completed', 'dpm_store_user_subscription_from_order');

// Store Subscription Data
function dpm_store_user_subscription($user_id, $subscription_id) {
    $validity = get_post_meta($subscription_id, '_dpm_validity', true);
    $purchase_date = current_time('mysql');
    $valid_until = date('Y-m-d', strtotime("+$validity days", strtotime($purchase_date)));

    update_user_meta($user_id, '_dpm_user_subscription', array(
        'subscription_id' => $subscription_id,
        'purchase_date' => $purchase_date,
        'valid_until' => $valid_until,
    ));
}

// Check if user has a valid subscription
function has_valid_subscription($user_id) {
    $subscription = get_user_meta($user_id, '_dpm_user_subscription', true);

    if ($subscription) {
        $valid_until = strtotime($subscription['valid_until']);
        $current_time = time();

        // Ensure the subscription is still active (valid_until date is in the future)
        if ($current_time <= $valid_until) {
            return true;
        }
    }
    return false;
}
