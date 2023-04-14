<?php
/**
 * Plugin Name: Custom Product Options
 * Description: A custom WordPress plugin to add product options and price calculations based on a JSON file.
 * Version: 1.0
 * Author: Your Name
 * Author URI: https://yourwebsite.com
 */

// Define the markup percentage as a global variable
global $custom_markup_percentage;
$custom_markup_percentage = get_option('adf_custom_markup_percentage');

// Add the settings menu
function custom_product_options_add_settings_link($links) {
  $settings_link = '<a href="admin.php?page=custom-product-options">' . __('Settings') . '</a>';
  array_push($links, $settings_link);
  return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'custom_product_options_add_settings_link');


function custom_product_options_settings_menu() {
  add_options_page(
    'Custom Product Options Settings', // page title
    'Custom Product Options', // menu title
    'manage_options', // capability
    'custom-product-options-settings', // menu slug
    'custom_product_options_settings_page' // callback function
  );
}
add_action('admin_menu', 'custom_product_options_settings_menu');



// Define the settings page
function custom_product_options_settings_page() {
  global $custom_markup_percentage;

  // Check user capabilities
  if (!current_user_can('manage_options')) {
    return;
  }

  // Save the submitted form
  if (isset($_POST['custom_markup_percentage'])) {
    $custom_markup_percentage = $_POST['custom_markup_percentage'];
    update_option('custom_markup_percentage', $custom_markup_percentage);
    echo '<div class="notice notice-success"><p>Markup percentage saved.</p></div>';
  }
  ?>

  <div class="wrap">
    <h1>Custom Product Options Settings</h1>

    <form method="post" action="">
      <table class="form-table">
        <tr>
          <th scope="row"><label for="custom_markup_percentage">Markup Percentage</label></th>
          <td><input type="text" name="custom_markup_percentage" id="custom_markup_percentage" value="<?php echo esc_attr($custom_markup_percentage); ?>" class="regular-text" /></td>
        </tr>
      </table>
      <?php submit_button('Save Changes'); ?>
    </form>
  </div>

  <?php
}

function custom_product_options_get_option($option_name) {
  $option_value = get_option($option_name);
  return isset($option_value) ? $option_value : null;
}

// Enqueue the JavaScript files
function custom_product_options_enqueue_scripts() {
  // Get the markup percentage from the options page
  $custom_markup_percentage = custom_product_options_get_option('markup_percentage');

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
