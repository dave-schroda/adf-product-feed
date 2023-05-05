<?php
function custom_product_options_create_settings_page()
{
    $hook_suffix = add_submenu_page(
        'woocommerce',
        __('Custom Product Options Settings', 'custom-product-options'),
        __('Custom Product Options', 'custom-product-options'),
        'manage_options',
        'custom-product-options-settings',
        'custom_product_options_settings_page_content'
    );

    // Enqueue the settings CSS file when the settings page is loaded
    add_action("admin_print_styles-{$hook_suffix}", 'custom_product_options_enqueue_settings_styles');
}

// Add this new function to enqueue the settings CSS file
function custom_product_options_enqueue_settings_styles()
{
    wp_enqueue_style('custom-product-options-settings', plugin_dir_url(__FILE__) . 'admin/css/settings.css', array(), '1.0.0');
}

function custom_product_options_settings_page_content()
{
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form action="options.php" method="post">
            <?php
            // Output security fields for the registered setting
            settings_fields('custom_product_options_settings');
            // Output setting sections and their fields
            do_settings_sections('custom_product_options_settings');
            // Output save settings button
            submit_button(__('Save Settings', 'custom-product-options'));
            ?>
        </form>
    </div>
    <?php
}

function custom_product_options_register_settings()
{
    register_setting('custom_product_options_settings', 'markup_percentage');

    add_settings_section(
        'custom_product_options_section',
        'Amish Product Options Settings',
        null,
        'custom_product_options_settings'
    );

    add_settings_field(
        'markup_percentage',
        'Markup Percentage',
        'custom_product_options_markup_percentage_callback',
        'custom_product_options_settings',
        'custom_product_options_section'
    );
}

add_action('admin_init', 'custom_product_options_register_settings');

function custom_product_options_markup_percentage_callback()
{
    $markup_percentage = get_option('markup_percentage', 100);
    echo '<input type="number" min="0" max="1000" step="1" name="markup_percentage" value="' . esc_attr($markup_percentage) . '">';
}
