<?php
/**
 * Plugin Name: Custom Product Options
 * Description: A custom WordPress plugin to add product options and price calculations based on a CSV file.
 * Version: 1.0
 * Author: Your Name
 * Author URI: https://yourwebsite.com
 */

// REQUIRED FILES
// require_once plugin_dir_path(__FILE__) . 'class-adf-product-feed-ajax.php';
require_once plugin_dir_path( __FILE__ ) . 'custom-product-options-settings.php';
require_once plugin_dir_path(__FILE__) . 'class-adf-product-feed-ajax.php';


// Enqueue the JavaScript files
function custom_product_options_enqueue_scripts() {
  global $custom_markup_percentage;

  $script_url = plugin_dir_url(__FILE__) . 'js/custom-product-options.js';

  wp_register_script('custom-product-options', $script_url, array('jquery'), '1.0', true);

  // Add pluginUrl, markup percentage, and ajax_url to the script data
  wp_localize_script('custom-product-options', 'customJsData', array(
      'wpContentUrl' => content_url(),
      'customMarkupPercentage' => $custom_markup_percentage,
      'pluginUrl' => plugin_dir_url(__FILE__),
      'ajax_url' => admin_url('admin-ajax.php') // Add this line
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
