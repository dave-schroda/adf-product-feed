<?php
/**
 * Plugin Name: Custom Product Options
 * Description: A custom WordPress plugin to add product options and price calculations based on a CSV file.
 * Version: 1.0
 * Author: Your Name
 * Author URI: https://yourwebsite.com
 */
require_once plugin_dir_path( __FILE__ ) . 'custom-product-options-settings.php';


// Enqueue the JavaScript files
function custom_product_options_enqueue_scripts() {
  global $custom_markup_percentage;

  $script_url = plugin_dir_url(__FILE__) . 'js/custom-product-options.js';

  wp_register_script('custom-product-options', $script_url, array('jquery'), '1.0', true);

  // Add pluginUrl and markup percentage to the script data
  wp_localize_script('custom-product-options', 'customJsData', array(
    'wpContentUrl' => content_url(),
    'customMarkupPercentage' => $custom_markup_percentage,
    'pluginUrl' => plugin_dir_url(__FILE__)
  ));

  wp_enqueue_script('custom-product-options');
}
add_action('wp_enqueue_scripts', 'custom_product_options_enqueue_scripts');

function custom_product_options_add_menu_item() {
  add_submenu_page(
    'woocommerce',
    'Custom Product Options Settings',
    'Custom Product Options',
    'manage_options',
    'custom-product-options-settings',
    'custom_product_options_settings_page'
  );
}
add_action('admin_menu', 'custom_product_options_add_menu_item');

/**
 * Add custom product options to cart item data
 */
function custom_product_options_add_to_cart( $cart_item_data, $product_id, $variation_id ) {
    $custom_data = array();

    // Get selected options
    if ( isset( $_POST['woodSelect'] ) ) {
        $custom_data['Wood'] = sanitize_text_field( $_POST['woodSelect'] );
    }

    if ( isset( $_POST['sizeSelect'] ) ) {
        $custom_data['Size'] = sanitize_text_field( $_POST['sizeSelect'] );
    }

    if ( ! empty( $custom_data ) ) {
        $cart_item_data['custom_data'] = $custom_data;
    }

    return $cart_item_data;
}
add_filter( 'woocommerce_add_cart_item_data', 'custom_product_options_add_to_cart', 10, 3 );

/**
 * Display selected options on cart and checkout pages
 */
function custom_product_options_display_cart( $item_data, $cart_item ) {
    if ( isset( $cart_item['custom_data'] ) ) {
        $custom_data = $cart_item['custom_data'];

        // Display selected options
        if ( isset( $custom_data['Wood'] ) ) {
            $item_data[] = array(
                'key'   => 'Wood',
                'value' => $custom_data['Wood']
            );
        }

        if ( isset( $custom_data['Size'] ) ) {
            $item_data[] = array(
                'key'   => 'Size',
                'value' => $custom_data['Size']
            );
        }
    }

    return $item_data;
}
add_filter( 'woocommerce_get_item_data', 'custom_product_options_display_cart', 10, 2 );

/**
 * Save selected options to order
 */
function custom_product_options_order_meta_handler( $item_id, $values, $cart_item_key ) {
    if ( isset( $values['custom_data'] ) ) {
        $custom_data = $values['custom_data'];

        // Save selected options to order
        if ( isset( $custom_data['Wood'] ) ) {
            wc_add_order_item_meta( $item_id, 'Wood', $custom_data['Wood'] );
        }

        if ( isset( $custom_data['Size'] ) ) {
            wc_add_order_item_meta( $item_id, 'Size', $custom_data['Size'] );
        }
    }
}
add_action( 'woocommerce_add_order_item_meta', 'custom_product_options_order_meta_handler', 10, 3 );

