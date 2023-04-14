<?php
/**
 * Plugin Name: Custom Product Options
 * Description: A custom WordPress plugin to add product options and price calculations based on a CSV file.
 * Version: 1.0
 * Author: Your Name
 * Author URI: https://yourwebsite.com
 */

// Define the markup percentage as a global variable
global $custom_markup_percentage;
$custom_markup_percentage = get_option('markup_percentage');

// Add the settings menu
function custom_product_options_add_settings_link($links) {
  $settings_link = '<a href="admin.php?page=custom-product-options-settings">' . __('Settings') . '</a>';
  array_push($links, $settings_link);
  return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'custom_product_options_add_settings_link');

function custom_product_options_settings_page() {
  ?>
  <div class="wrap">
    <h1><?php esc_html_e( 'Custom Product Options Settings', 'custom-product-options' ); ?></h1>
    <form method="post" action="options.php">
      <?php settings_fields( 'custom_product_options_settings_group' ); ?>
      <?php do_settings_sections( 'custom_product_options_settings_group' ); ?>
      <table class="form-table">
        <tr>
          <th scope="row">
            <label for="markup_percentage"><?php esc_html_e( 'Markup Percentage', 'custom-product-options' ); ?></label>
          </th>
          <td>
            <input type="number" name="markup_percentage" id="markup_percentage" value="<?php echo esc_attr( get_option( 'markup_percentage' ) ); ?>" class="regular-text" min="0" max="100" step="0.01">
            <p class="description"><?php esc_html_e( 'Enter the markup percentage to add to the price of each product.', 'custom-product-options' ); ?></p>
          </td>
        </tr>
      </table>
      <?php submit_button(); ?>
    </form>
  </div>
  <?php
}

function custom_product_options_get_option($option_name) {
  global $custom_markup_percentage;

  $option_value = get_option($option_name);

  if (!$option_value) {
    switch ($option_name) {
      case 'markup_percentage':
        if ($custom_markup_percentage) {
          $option_value = $custom_markup_percentage;
        } else {
          $option_value = 1;
        }
        break;
      default:
        $option_value = '';
        break;
    }
    add_option($option_name, $option_value);
  }

  return $option_value;
}

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
