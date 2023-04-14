<?php
/**
 * Enqueue custom scripts
 */
function enqueue_custom_scripts() {
    wp_enqueue_script('custom-js', plugin_dir_url(__FILE__) . 'js/custom-product-options.js', array('jquery'), '1.0.0', true);

    // Pass the wp-content URL to the custom JavaScript file
    wp_localize_script('custom-js', 'customJsData', array(
        'wpContentUrl' => content_url(),
    ));
}
add_action('wp_enqueue_scripts', 'enqueue_custom_scripts');
