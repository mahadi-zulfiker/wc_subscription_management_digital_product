<?php
if (!defined('ABSPATH')) {
    exit;
}

// Register Custom Post Type for Subscriptions
function dpm_register_subscriptions() {
    $args = array(
        'labels' => [
            'name'                  => 'Subscriptions',
            'singular_name'         => 'Subscription',
            'all_items'             => 'All Subscriptions',
            'view_item'             => 'View Subscription',
            'add_new_item'          => 'Add New Subscription',
            'add_new'               => 'Add New',
            'edit_item'             => 'Edit Subscription',
            'update_item'           => 'Update Subscription',
            'search_items'          => 'Search Subscriptions',
            'not_found'             => 'No Subscriptions found',
            'not_found_in_trash'    => 'No Subscriptions found in Trash',
        ],
        'public'        => true,
        'menu_icon'     => 'dashicons-list-view',
        'supports'      => array('title', 'editor'),
    );
    register_post_type('subscription_plan', $args);
}
add_action('init', 'dpm_register_subscriptions');

// Add Meta Box for Subscription Details
function dpm_subscription_meta_box() {
    add_meta_box(
        'dpm_subscription_meta',
        'Subscription Details',
        'dpm_subscription_meta_box_callback',
        'subscription_plan',
        'side'
    );
}
add_action('add_meta_boxes', 'dpm_subscription_meta_box');

function dpm_subscription_meta_box_callback($post) {
    $price = get_post_meta($post->ID, '_dpm_price', true);
    $validity = get_post_meta($post->ID, '_dpm_validity', true);
    $package_details = get_post_meta($post->ID, '_dpm_package_details', true);
    ?>
    <label>Price: </label>
    <input type="text" name="dpm_price" value="<?php echo esc_attr($price); ?>" /><br><br>

    <label>Validity (days): </label>
    <input type="number" name="dpm_validity" value="<?php echo esc_attr($validity); ?>" /><br><br>

    <label>Package Details: </label>
    <textarea name="dpm_package_details" rows="5" cols="30"><?php echo esc_textarea($package_details); ?></textarea>
    <p>Enter details line by line.</p>
    <?php
}


// Save Subscription Meta Data
function dpm_save_subscription_meta($post_id) {
    if (array_key_exists('dpm_price', $_POST)) {
        update_post_meta($post_id, '_dpm_price', sanitize_text_field($_POST['dpm_price']));
    }
    if (array_key_exists('dpm_validity', $_POST)) {
        update_post_meta($post_id, '_dpm_validity', sanitize_text_field($_POST['dpm_validity']));
    }
    if (array_key_exists('dpm_package_details', $_POST)) {
        update_post_meta($post_id, '_dpm_package_details', sanitize_textarea_field($_POST['dpm_package_details']));
    }
}

add_action('save_post', 'dpm_save_subscription_meta');

// Display Subscription Plans
function dpm_display_subscriptions() {
    $query = new WP_Query(array('post_type' => 'subscription_plan'));

    echo '<div class="subscription-plans-row">';

    while ($query->have_posts()) : $query->the_post();
        $price = get_post_meta(get_the_ID(), '_dpm_price', true);
        $validity = get_post_meta(get_the_ID(), '_dpm_validity', true);
        $package_details = get_post_meta(get_the_ID(), '_dpm_package_details', true);

        if (function_exists('dpm_get_checkout_url')) {
            $checkout_url = dpm_get_checkout_url(get_the_ID());
        } else {
            $checkout_url = '#';
        }

        echo '<div class="subscription-plan">';
        the_title('<h2 style="font-weight: bold">', '</h2>');
        echo "<p><strong>Price:</strong> $price BDT</p>";
        echo "<p><strong>Validity:</strong> $validity days</p>";

        if (!empty($package_details)) {
            echo "<p><strong>Package Details:</strong></p><ul class='package-details' style='list-style-type: none; text-align: justified'>";
            $details_array = explode("\n", $package_details); // Convert lines into an array
            foreach ($details_array as $detail) {
                echo "<li>✅ " . esc_html(trim($detail)) . "</li>"; // Display each line with a bullet point
            }
            echo "</ul>";
        }

        // Check if user is logged in before showing the Subscribe button
        if (is_user_logged_in()) {
            echo "<a href='$checkout_url' class='subscribe-button'>Subscribe Now</a>";
        } else {
            echo "<p><a href='" . wp_login_url(get_permalink()) . "' class='login-to-subscribe'>Log in to Subscribe</a></p>";
        }

        echo '</div>';
    endwhile;

    wp_reset_postdata();
    echo '</div>';
}
add_shortcode('subscription_plans', 'dpm_display_subscriptions');
