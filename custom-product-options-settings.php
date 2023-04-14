<?php
function custom_product_options_settings_page() {
  // Register settings
  register_setting(
    'custom_product_options_settings_group',
    'markup_percentage',
    array(
      'type' => 'string',
      'sanitize_callback' => 'custom_product_options_sanitize_markup_percentage'
    )
  );

  // Render the markup percentage field
  $value = get_option( 'markup_percentage' );
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
            <input type="number" name="markup_percentage" id="markup_percentage" value="<?php echo esc_attr( $value ); ?>" class="regular-text" min="0" max="100" step="0.01">
            <p class="description"><?php esc_html_e( 'Enter the markup percentage to add to the price of each product.', 'custom-product-options' ); ?></p>
          </td>
        </tr>
      </table>
      <?php submit_button(); ?>
    </form>
  </div>
  <?php
}

function custom_product_options_sanitize_markup_percentage( $input ) {
  $new_input = filter_var( $input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );
  if ( is_numeric( $new_input ) && $new_input >= 0 && $new_input <= 100 ) {
    return $new_input;
  } else {
    return get_option( 'markup_percentage' );
  }
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
