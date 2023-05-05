<?php
	class Custom_Product_Options_Builder
	{	
        function generate_select_boxes()
        {
            global $product;
            $sku = $product->get_sku();
            $product_name = $product->get_name();
            $formatted_product_name = str_replace(' ', '_', $product_name);

            // Map SKU prefixes to manufacturer folder names
            $manufacturer_folders = array(
                'IH' => 'interior-hardwoods',
                'WP' => 'west-point-woodworking',
                // Add more manufacturer folder mappings here as needed
            );

            $manufacturer_folder = '';
            foreach ($manufacturer_folders as $prefix => $folder) {
                if (strpos($sku, $prefix) === 0) {
                    $manufacturer_folder = $folder;
                    break;
                }
            }

            // If a matching folder is not found, return early
            if ($manufacturer_folder === '') {
                return;
            }

            $json_file_url = plugins_url('product-csv-files/' . $manufacturer_folder . '/' . $formatted_product_name . '.json', dirname(__FILE__));

            // Enqueue your script and pass the JSON file path
		    wp_localize_script('custom-product-options-script', 'php_vars', array('json_file_url' => $json_file_url));

            // Display the JSON file path on the product page
            echo '<!-- JSON file path: ' . htmlspecialchars($json_file_url) . ' -->';

			$response = wp_remote_get($json_file_url);

			if (is_wp_error($response)) {
			    error_log('Failed to get JSON file: ' . $response->get_error_message());
			} else {
			    $json_data_raw = wp_remote_retrieve_body($response);
			    $json_data = json_decode($json_data_raw, true);
			}

			if ($json_data !== null) {
	            echo '<!-- Generating radio buttons -->';
				echo '<div id="custom-product-options">';
				echo '<input type="hidden" id="selected-price" name="selected_price" value="0">';
				echo '<p class="price-message">See price after choosing your options</p>';
				echo '<style> p.price { display: none; } </style>';

				// Wood radio buttons
				if (isset($json_data[0]['wood']) && is_array($json_data[0]['wood'])) {
				    echo '<div class="custom-option">';
				    echo '<h3 class="accordion-header">Wood: <span class="option-text">Choose an option</span></h3><div class="accordion-section">';

				    // Add a hidden field with the group label
				    echo '<input type="hidden" name="label_wood" value="Wood">';

				    foreach ($json_data[0]['wood'] as $wood_key => $wood_name) {
				        echo '<div class="wood-radio-option">';
				        echo '<input type="radio" id="' . esc_attr($wood_key) . '" class="custom-option-radio wood-option-radio" name="wood" value="' . esc_attr($wood_key) . '" data-price="0">';
				        echo '<label for="' . esc_attr($wood_key) . '">' . esc_html($wood_name) . '</label>';
				        echo '</div>';
				    }

				    echo '</div></div>';
				}

				// Options radio buttons
				if (isset($json_data[0]['options']) && is_array($json_data[0]['options'])) {
				    foreach ($json_data[0]['options'][0] as $option_key => $option_value) {
				        // Skip the "price" key
				        if ($option_key === 'price') {
				            continue;
				        }

				        $option_name = ucwords(str_replace('_', ' ', $option_key));
				        echo '<div class="custom-option">';
				        echo '<h3 class="accordion-header disabled">' . esc_html($option_name) . ': <span class="option-text">Choose an option</span></h3><div class="accordion-section">';

				        // Add a hidden field with the group label
				        echo '<input type="hidden" name="label_' . esc_attr($option_key) . '" value="' . esc_html($option_name) . '">';

				        $unique_options = [];
				        foreach ($json_data[0]['options'] as $option) {
				            $option_value = $option[$option_key];
				            // Store unique option values
				            if (!in_array($option_value, $unique_options)) {
				                $unique_options[] = $option_value;
				            }
				        }

				        foreach ($unique_options as $unique_option_value) {
				            echo '<div class="option-radio-option">';
				            echo '<input type="radio" id="' . esc_attr($unique_option_value) . '" class="custom-option-radio" name="' . esc_attr($option_key) . '" value="' . esc_attr($unique_option_value) . '">';
				            echo '<label for="' . esc_attr($unique_option_value) . '">' . esc_html($unique_option_value) . '</label>';
				            echo '</div>';
				        }

				        echo '</div></div>';
				    }
				} else {
				    echo '<!-- Options data not found in JSON data -->';
				}
			}
		}
	}