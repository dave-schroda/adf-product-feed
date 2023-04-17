<?php
// class-adf-product-feed-ajax.php
class ADF_Product_Feed_Ajax {
    public function __construct() {
        add_action('wp_ajax_adf_update_product_options', array($this, 'adf_update_product_options'));
        add_action('wp_ajax_nopriv_adf_update_product_options', array($this, 'adf_update_product_options'));
        add_filter('woocommerce_get_item_data', array($this, 'display_options_in_cart'), 10, 2);
        add_action('woocommerce_checkout_create_order_line_item', array($this, 'add_options_to_order_items'), 10, 4);
        add_action('woocommerce_before_calculate_totals', array($this, 'set_cart_item_prices'), 10, 1);
    }

    public function get_product_options($product) {
        // Assuming $product is an instance of WC_Product
        $sku = $product->get_sku();
        $json_url = plugin_dir_path( __FILE__ ) . 'product-csv-files/' . $sku . '.json';

        if ( file_exists( $json_url ) ) {
            $json_data = file_get_contents( $json_url );
            // Process the JSON data as needed
            $product_options = json_decode($json_data, true);
        } else {
            // Handle the case where the JSON file doesn't exist
            $product_options = array();
        }
        return $product_options;
    }

    public function adf_update_product_options() {
        if (!isset($_POST['product_id']) || !isset($_POST['options'])) {
            wp_send_json_error('Invalid request');
        }

        $product_id = intval($_POST['product_id']);
        $options = $_POST['options'];

        // Retrieve product options and price from JSON file
        $product_options = $this->get_product_options($product_id);
        $final_price = $product_options['price'];

        foreach ($options as $option_key => $option_value) {
            if (isset($product_options['options'][$option_key][$option_value])) {
                $final_price += $product_options['options'][$option_key][$option_value];
            }
        }

        // Update cart item
        $cart = WC()->cart;
        $cart_item_key = $this->find_product_in_cart($cart, $product_id);
        if ($cart_item_key) {
            $cart_item = $cart->get_cart_item($cart_item_key);
            $cart_item['data']->set_price(floatval($final_price));
        } else {
            $cart_item_data = array(
                'final_price' => floatval($final_price),
            );
            $cart_item_key = $cart->add_to_cart($product_id, 1, 0, array(), $cart_item_data);
            $cart_item = $cart->get_cart_item($cart_item_key);
        }

        // Add selected options to cart item
        $cart_item['adf_options'] = $options;
        $cart->set_session();
        $cart->calculate_totals();

        wp_send_json_success('Options updated successfully');
    }

    // Add this function to class-adf-product-feed-ajax.php
    private function find_product_in_cart($cart, $product_id) {
        foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
            if ($cart_item['product_id'] == $product_id) {
                return $cart_item_key;
            }
        }
        return false;
    }

    public function display_options_in_cart($item_data, $cart_item) {
        if (isset($cart_item['adf_options'])) {
            foreach ($cart_item['adf_options'] as $key => $value) {
                $item_data[] = array(
                    'key'     => ucfirst($key),
                    'value'   => $value,
                    'display' => '',
                );
            }
        }
        return $item_data;
    }

    public function add_options_to_order_items($item, $cart_item_key, $values, $order) {
        if (isset($values['options'])) {
            foreach ($values['options'] as $key => $value) {
                $item->add_meta_data(ucfirst($key), $value);
            }
        }
    }

    public function set_cart_item_prices($cart_obj) {
        if (is_admin() && !defined('DOING_AJAX')) {
            return;
        }

        foreach ($cart_obj->get_cart() as $cart_item_key => $cart_item) {
            if (isset($cart_item['final_price'])) {
                $cart_item['data']->set_price($cart_item['final_price']);
            }
        }
    }

}

new ADF_Product_Feed_Ajax();
