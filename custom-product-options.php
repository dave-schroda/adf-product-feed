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

        public function __construct()
        {
            add_action('plugins_loaded', array($this, 'init'));
        }

        public function init()
        {
            if (class_exists('WooCommerce')) {
                add_action('woocommerce_before_add_to_cart_button', array($this, 'generate_select_boxes'));
                add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
                add_filter('woocommerce_add_cart_item_data', array($this, 'add_custom_options_to_cart_item_data'), 10, 3);
                add_filter('woocommerce_get_item_data', array($this, 'display_custom_options_in_cart'), 10, 2);
            } else {
                add_action('admin_notices', array($this, 'woocommerce_missing_notice'));
            }
        }

        public function generate_select_boxes()
        {
            global $product;
            $sku = $product->get_sku();
            $json_file_path = plugin_dir_path(__FILE__) . 'product-csv-files/' . $sku . '.json';

            // Display the JSON file path on the product page
            echo '<!-- JSON file path: ' . htmlspecialchars($json_file_path) . ' -->';

            if (file_exists($json_file_path)) {
                echo '<!-- JSON file found -->';
                $json_data = json_decode(file_get_contents($json_file_path), true);

                // Display the JSON data on the product page
                echo '<!-- JSON data: ' . htmlspecialchars(print_r($json_data, true)) . ' -->';

                if ($json_data) {
                    echo '<!-- Generating select boxes -->';
                    echo '<div class="custom-product-options">';
                    echo '<p class="price-message">See price after choosing your options</p>';
                    echo '<style> p.price { display: none; } </style>';

                    // Wood select box
                    echo '<div class="custom-option">';
                    echo '<label for="wood">Wood</label>';
                    echo '<select id="wood" class="custom-option-select" data-option-key="wood">';
                    echo '<option value="" data-price="0">Select Wood</option>';

                    $wood_types = array("Red Oak", "Cherry", "Brown Maple", "Quartersawn White Oak", "Hard Maple");

                    foreach ($wood_types as $wood_type) {
                        echo '<option value="' . esc_attr($wood_type) . '" data-price="0">' . esc_html($wood_type) . '</option>';
                    }

                    echo '</select>';
                    echo '</div>';

                    // Size select box
                    echo '<div class="custom-option">';
                    echo '<label for="size">Size</label>';
                    echo '<select id="size" class="custom-option-select" data-option-key="size">';
                    echo '<option value="" data-price="0">Select Size</option>';

                    foreach ($json_data as $value) {
                        echo '<option value="' . esc_attr($value['Size']) . '" data-prices=\'' . json_encode(array_intersect_key($value, array_flip($wood_types))) . '\'>' . esc_html($value['Size']) . '</option>';
                    }

                    echo '</select>';
                    echo '</div>';

                    echo '</div>';
                } else {
                    echo '<!-- Size data not found in JSON data -->';
                }
            } else {
                echo '<!-- JSON file not found -->';
            }
        }

        public function enqueue_scripts()
        {
            wp_enqueue_script('custom-product-options-script', plugin_dir_url(__FILE__) . 'js/custom-product-options.js', array('jquery'), '1.0.0', true);
        }

        public function woocommerce_missing_notice()
        {
            echo '<div class="error"><p>' . sprintf(__('Custom Product Options requires %s to be installed and active.', 'custom-product-options'), '<a href="https://wordpress.org/plugins/woocommerce/" target="_blank">WooCommerce</a>') . '</p></div>';
        }

        public function add_custom_options_to_cart_item_data($cart_item_data, $product_id, $variation_id) {
            if (isset($_POST['custom_option_wood']) && isset($_POST['custom_option_size'])) {
                $cart_item_data['custom_options'] = array(
                    'wood' => sanitize_text_field($_POST['custom_option_wood']),
                    'size' => sanitize_text_field($_POST['custom_option_size']),
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

    }

    new Custom_Product_Options();
}
