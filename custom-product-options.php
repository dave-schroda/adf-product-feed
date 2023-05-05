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
                // WORKS
                add_action('woocommerce_before_add_to_cart_button', array($this->builder, 'generate_select_boxes'));
                add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
                add_filter('woocommerce_add_cart_item_data', array($this, 'add_custom_options_to_cart_item_data'), 10, 3);
                add_filter('woocommerce_get_item_data', array($this, 'display_custom_options_in_cart'), 10, 2);
                add_action( 'woocommerce_before_calculate_totals', array( $this, 'custom_product_options_update_cart_item_price' ));
                add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'add_custom_options_to_order_item_meta' ), 10, 4 );

                // CURRENTLY TESTING
                add_action('woocommerce_order_item_meta_start', array($this, 'display_custom_options_on_order_pages'), 5, 4);

            } else {
                add_action('admin_notices', array($this, 'woocommerce_missing_notice'));
            }
        }

        public function enqueue_scripts()
        {
            // Frontend JS
            wp_enqueue_script('custom-product-options-script', plugin_dir_url(__FILE__) . 'frontend/js/custom-product-options.js', array('jquery'), '1.0.0', true);
            wp_register_script( 'jquery-ui', 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/jquery-ui.min.js', true );
            wp_enqueue_script('jquery-ui');

            // Frontend CSS
            wp_enqueue_style('custom-product-options-css', plugin_dir_url(__FILE__) . 'frontend/css/custom-product-options-frontend.css', '1.0.0', true);

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

        // FUNCTION TO TAKE USER SELECTED CUSTOM OPTIONS AND SAVE THEM AS SIMPLE STRINGS
        public function add_custom_options_to_cart_item_data($cart_item_data, $product_id, $variation_id) {
            $selected_price = isset($_POST['selected_price']) ? floatval($_POST['selected_price']) : 0;
            $custom_options = array(
                'selected_price' => $selected_price,
            );

            // Loop over all POST data
            foreach($_POST as $key => $value) {
                // If the key starts with 'custom_option_', add it to the custom options
                if (strpos($key, 'custom_option_') === 0) {
                    $option_name = str_replace('custom_option_', '', $key);
                    $custom_options[$option_name] = sanitize_text_field($value);
                }
            }

            if (!empty($custom_options)) {
                $cart_item_data['custom_options'] = $custom_options;
            }

            return $cart_item_data;
        }

        // FUNCTION TO DISPLAY USER SELECTED CUSTOM OPTIONS IN CART/CHECKOUT PAGE
        public function display_custom_options_in_cart($item_data, $cart_item) {
            if (isset($cart_item['custom_options'])) {
                foreach($cart_item['custom_options'] as $name => $option) {
                    if ($name != 'selected_price') {
                        $display_name = ucfirst(str_replace('_', ' ', $name));
                        $display_value = ucwords(str_replace('_', ' ', $option));

                        // Remove slashes
                        $display_value = stripslashes($display_value);

                        $item_data[] = array(
                            'key'     => $display_name,
                            'value'   => $display_value,
                            'display' => '',
                        );
                    }
                }
            }

            return $item_data;
        }

        // FUNCTION TO PRICE FROM USER SELECTED CHOICES AND UPDATE THE PRODUCT'S PRICE TO IT
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

        // FUNCTION TO TAKE STRINGS FROM USER CHOICES AND SAVE THEM TO THE PRODUCT AS META DATA
        public function add_custom_options_to_order_item_meta( $item, $cart_item_key, $values, $order ) {
            if ( isset( $values['custom_options'] ) ) {
                foreach ( $values['custom_options'] as $key => $option ) {
                    if ( $key != 'selected_price' ) {
                        $display_name = ucfirst(str_replace('_', ' ', $key));
                        $display_value = ucwords(str_replace('_', ' ', $option));

                        // Remove slashes
                        $display_value = stripslashes($display_value);

                        // Save each custom option as a separate meta data
                        $item->add_meta_data( $display_name, $display_value );
                    }
                }
            }
        }

        public function display_custom_options_on_order_pages($item_id, $item, $order, $bool) {
            $custom_options = $item->get_meta('custom_options');

            if ($custom_options) {
                foreach ($custom_options as $name => $option) {
                    if ($name != 'selected_price') {
                        $display_name = ucfirst(str_replace('_', ' ', $name));
                        $display_value = ucwords(str_replace('_', ' ', $option));

                        // Remove slashes
                        $display_value = stripslashes($display_value);

                        echo '<br><strong>'.$display_name.':</strong> '.$display_value;
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
