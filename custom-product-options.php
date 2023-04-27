<?php
/**
 * Plugin Name: Amish Product Options
 * Description: A custom plugin that adds dynamic select boxes based on a JSON file for WooCommerce products.
 * Version: 1.0
 * Author: David Schroeder
 * Author URI: https://amishdirectfurniture.com
 * Text Domain: custom-product-options
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!class_exists('Custom_Product_Options')) {

    class Custom_Product_Options
    {
        public $builder;

        public function __construct()
        {
            add_action('plugins_loaded', array($this, 'init'));
        }

        public function init()
        {    
            // REQUIRE IMPORTANT
            require_once plugin_dir_path(__FILE__) . 'builder/custom-product-options-builder.php';
            $this->builder = new Custom_Product_Options_Builder();

            if (class_exists('WooCommerce')) {
                add_action('woocommerce_before_add_to_cart_button', array($this->builder, 'generate_select_boxes'));
                add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
                add_filter('woocommerce_add_cart_item_data', array($this, 'add_custom_options_to_cart_item_data'), 10, 3);
                add_filter('woocommerce_get_item_data', array($this, 'display_custom_options_in_cart'), 10, 2);
                add_action( 'woocommerce_before_calculate_totals', array( $this, 'custom_product_options_update_cart_item_price' ));
            } else {
                add_action('admin_notices', array($this, 'woocommerce_missing_notice'));
            }
        }

        public function enqueue_scripts()
        {
            wp_enqueue_script('custom-product-options-script', plugin_dir_url(__FILE__) . 'js/custom-product-options.js', array('jquery'), '1.0.0', true);

            // Pass the markup_percentage to the JS script
            $markup_percentage = get_option('markup_percentage', 100);
            wp_localize_script('custom-product-options-script', 'customProductOptionsData', array(
                'markup_percentage' => $markup_percentage,
            ));
        }

        public function woocommerce_missing_notice()
        {
            echo '<div class="error"><p>' . sprintf(__('Custom Product Options requires %s to be installed and active.', 'custom-product-options'), '<a href="https://wordpress.org/plugins/woocommerce/" target="_blank">WooCommerce</a>') . '</p></div>';
        }

        public function add_custom_options_to_cart_item_data($cart_item_data, $product_id, $variation_id) {
            if (isset($_POST['custom_option_wood']) && isset($_POST['custom_option_size'])) {
                $selected_price = isset($_POST['selected_price']) ? floatval($_POST['selected_price']) : 0;
                $cart_item_data['custom_options'] = array(
                    'wood' => sanitize_text_field($_POST['custom_option_wood']),
                    'size' => sanitize_text_field($_POST['custom_option_size']),
                    'selected_price' => $selected_price, // Add this line
                );
            }

            return $cart_item_data;
        }

        public function display_custom_options_in_cart($item_data, $cart_item) {
            if (isset($cart_item['custom_options'])) {
                $item_data[] = array(
                    'key'     => __('Wood', 'custom-product-options'),
                    'value'   => $cart_item['custom_options']['wood'],
                    'display' => '',
                );
                $item_data[] = array(
                    'key'     => __('Size', 'custom-product-options'),
                    'value'   => $cart_item['custom_options']['size'],
                    'display' => '',
                );
            }

            return $item_data;
        }

        public function custom_product_options_update_cart_item_price( $cart_object ) {
            if ( !WC()->session->__isset( 'reload_checkout' ) ) {
                foreach ( $cart_object->get_cart() as $cart_item_key => $cart_item ) {
                    if ( isset( $cart_item['custom_options']['selected_price'] ) && !empty( $cart_item['custom_options']['selected_price'] ) ) {
                        $price = $cart_item['custom_options']['selected_price'];
                        $cart_item['data']->set_price( (float) $price );
                    }
                }
            }
        }
    }

    new Custom_Product_Options();

    // Include the settings file
    require_once plugin_dir_path(__FILE__) . 'custom-product-options-settings.php';

    // Register the settings page
    add_action('admin_menu', 'custom_product_options_create_settings_page');
}
