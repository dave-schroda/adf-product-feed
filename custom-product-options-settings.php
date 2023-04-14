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

function custom_product_options_settings_init() {
  // Register a settings section
  add_settings_section(
    'custom_product_options_settings_section',
    __( 'Custom Product Options Settings', 'custom-product-options' ),
    '',
    'custom_product_options_settings_group'
  );

  // Register a settings field
  add_settings_field(
    'markup_percentage',
    __( 'Markup Percentage', 'custom-product-options' ),
    'custom_product_options_markup_percentage_input_callback',
    'custom_product_options_settings_group',
    'custom_product_options_settings_section'
  );

  // Register the settings section and fields
  register_setting(
    'custom_product_options_settings_group',
    'markup_percentage',
    array(
      'type' => 'number',
      'sanitize_callback' => 'sanitize_text_field',
      'default' => '0',
    )
  );
}
add_action( 'admin_init', 'custom_product_options_settings_init' );

function custom_product_options_markup_percentage_input_callback() {
  $value = get_option( 'markup_percentage' );
  ?>
  <input type="number" name="markup_percentage" id="markup_percentage" value="<?php echo esc_attr( $value ); ?>" class="regular-text" min="0" max="100" step="0.01">
  <?php
}

// Save the submitted form
if (isset($_POST['markup_percentage'])) {
  update_option('markup_percentage', $_POST['markup_percentage']);
  echo '<div class="notice notice-success"><p>Markup percentage saved.</p></div>';
}
