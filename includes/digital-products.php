<?php
if (!defined('ABSPATH')) {
    exit;
}
add_action('admin_post_secure_download', 'dpm_secure_download_handler');
add_action('admin_post_nopriv_secure_download', 'dpm_secure_download_handler');

function dpm_secure_download_handler() {
    if (!is_user_logged_in()) {
        wp_die('Unauthorized access.');
    }

    $user_id = get_current_user_id();

    if (!has_valid_subscription($user_id)) {
        wp_die('You need an active subscription to download this file.');
    }

    $file_id = isset($_GET['file_id']) ? sanitize_text_field($_GET['file_id']) : '';
    $token = isset($_GET['token']) ? sanitize_text_field($_GET['token']) : '';
    $product_id = isset($_GET['product_id']) ? absint($_GET['product_id']) : 0;

    if (!$file_id || !$token || !wp_verify_nonce($token, 'secure_download_' . $file_id) || !$product_id) {
        wp_die('Invalid download request.');
    }

    $product = wc_get_product($product_id);
    if (!$product) {
        wp_die('Invalid product.');
    }

    $downloads = $product->get_downloads();

    if (isset($downloads[$file_id])) {
        $file_url = $downloads[$file_id]['file'];
        $file_name = basename($file_url);

        // Force download headers
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $file_name . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        readfile($file_url);
        exit;
    } else {
        wp_die('File not found.');
    }
}

// Hide Add to Cart & Price for Virtual Products
function dpm_hide_add_to_cart_for_digital_products() {
    if (!is_product()) {
        return; // Ensure we are on a product page
    }

    global $product;

    if (!$product instanceof WC_Product) {
        $product = wc_get_product(get_the_ID()); // Ensure $product is properly set
    }

    if ($product && $product->is_virtual()) {
        remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
        remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_price', 10);
    }
}
add_action('woocommerce_before_single_product', 'dpm_hide_add_to_cart_for_digital_products');


// Display Custom Download Button for Logged-in Subscribers
function dpm_custom_download_button() {
    global $product;

    if ($product->is_virtual()) {
        $downloads = $product->get_downloads();

        if (is_user_logged_in()) {
            $user_id = get_current_user_id();

            if (has_valid_subscription($user_id)) {
                if (!empty($downloads)) {
                    foreach ($downloads as $file_id => $download) {
                        $token = wp_create_nonce('secure_download_' . $file_id);
                        $download_url = admin_url('admin-post.php?action=secure_download&file_id=' . $file_id . '&token=' . $token . '&product_id=' . $product->get_id());

                        echo '<a href="' . esc_url($download_url) . '" class="button download-button">Download</a>';
                    }
                } else {
                    echo '<p>No file available for download.</p>';
                }
            } else {
                echo '<p>You need an active subscription to download this product.</p>';
            }
        } else {
            echo '<p>Please log in to download this product.</p>';
        }
    }
}

add_action('woocommerce_single_product_summary', 'dpm_custom_download_button', 25);

// Ensure Product is Virtual & No Price Needed
function dpm_make_product_virtual($product_data, $product) {
    if ($product->get_id()) {
        $product_data->set_virtual(true);
        $product_data->set_price('');
    }
    return $product_data;
}
add_filter('woocommerce_product_get_data', 'dpm_make_product_virtual', 10, 2);
