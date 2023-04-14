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
    // Find the product based on selected wood and size
    $product = custom_product_options_find_product( $custom_data['Size'], $custom_data['Wood'] );

    if ( $product ) {
      $price = $product[ $custom_data['Wood'] ];

      // Get the markup percentage from the options page
      $custom_markup_percentage = get_option( 'custom_markup_percentage', 0 );
      $markupPercentage = floatval( $custom_markup_percentage );

      if ( $markupPercentage > 0 ) {
        $price = ceil( $price * ( 1 + $markupPercentage / 100 ) );
      }

      $cart_item_data['custom_data'] = $custom_data;
      $cart_item_data['price'] = $price;
    }
  }

  return $cart_item_data;
}
add_filter( 'woocommerce_add_cart_item_data', 'custom_product_options_add_to_cart', 10, 3 );

/**
 *Display selected options on cart and checkout pages
 */
function custom_product_options_display_cart( $item_data, $cart_item ) {
    if ( isset( $cart_item['custom_data'] ) ) {
        $custom_data = $cart_item['custom_data'];

        // Get selected wood, size and price
        $wood = isset( $custom_data['Wood'] ) ? $custom_data['Wood'] : '';
        $size = isset( $custom_data['Size'] ) ? $custom_data['Size'] : '';
        $price = isset( $custom_data['Price'] ) ? wc_price( $custom_data['Price'] ) : '';

        if ( ! empty( $wood ) && ! empty( $size ) && ! empty( $price ) ) {
            $item_data[] = array(
                'key'   => 'Wood',
                'value' => $wood,
            );

            $item_data[] = array(
                'key'   => 'Size',
                'value' => $size,
            );

            $item_data[] = array(
                'key'   => 'Price',
                'value' => $price,
            );
        }
    }

    return $item_data;
}
add_filter( 'woocommerce_add_cart_item_data', 'add_size_to_cart_item_data', 10, 3 );
function add_size_to_cart_item_data($cart_item_data, $product_id, $variation_id ){
    if( isset( $_POST['product-size'] ) ) {
        $cart_item_data['product-size'] = esc_attr( $_POST['product-size'] );
    }
    return $cart_item_data;
}

/**
 * Save selected options to order
 */
function custom_product_options_order_meta_handler( $item_id, $values, $cart_item_key ) {
    if ( isset( $values['custom_data'] ) ) {
        $custom_data = $values['custom_data'];

        // Save selected options and price to order
        if ( isset( $custom_data['Wood'] ) && isset( $custom_data['Size'] ) && isset( $custom_data['price'] ) ) {
            wc_add_order_item_meta( $item_id, 'Wood', $custom_data['Wood'] );
            wc_add_order_item_meta( $item_id, 'Size', $custom_data['Size'] );
            wc_add_order_item_meta( $item_id, 'Price', wc_price( $custom_data['price'] ) );
        }
    }
}

function custom_product_options_update_cart_item_data( $cart_item_key, $values ) {
  if ( isset( $values['custom_data']['price'] ) ) {
    WC()->cart->cart_contents[ $cart_item_key ]['custom_data']['price'] = $values['custom_data']['price'];
  }
}
add_action( 'woocommerce_cart_item_data_updated', 'custom_product_options_update_cart_item_data', 10, 2 );
