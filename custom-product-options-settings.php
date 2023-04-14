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
