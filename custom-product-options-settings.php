<?php
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

function custom_product_options_settings() {
  add_submenu_page(
    'woocommerce',
    'Custom Product Options Settings',
    'Custom Product Options',
    'manage_options',
    'custom-product-options-settings',
    'custom_product_options_settings_page'
  );

  // Register settings
  add_action('admin_init', 'custom_product_options_register_settings');
}
add_action('admin_menu', 'custom_product_options_settings');

// Register settings
function custom_product_options_register_settings() {
  register_setting(
    'custom_product_options_settings_group',
    'markup_percentage',
    array(
      'type' => 'number',
      'sanitize_callback' => function ($value) {
        $value = filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        return intval($value);
      }
    )
  );
}


add_action( 'admin_init', 'custom_product_options_settings' );

// Render the markup percentage field
function custom_product_options_markup_percentage_field() {
  $value = get_option( 'markup_percentage' );
  ?>
  <input type="number" name="markup_percentage" id="markup_percentage" value="<?php echo esc_attr( $value ); ?>" class="regular-text" min="0" max="100" step="0.01">
  <?php
}

// Define the markup percentage as a global variable
global $custom_markup_percentage;
$custom_markup_percentage = get_option('markup_percentage', 1);

function custom_product_options_get_option($option_name) {
  global $custom_markup_percentage;

  $option_value = get_option($option_name, 0);

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
