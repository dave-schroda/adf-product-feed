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

            $json_file_path = plugin_dir_path(dirname(__FILE__)) . 'product-csv-files/' . $manufacturer_folder . '/' . $formatted_product_name . '.json';

            // Display the JSON file path on the product page
            echo '<!-- JSON file path: ' . htmlspecialchars($json_file_path) . ' -->';

            // Read the JSON file and decode it into an array
			$json_data_raw = file_get_contents($json_file_path);
			$json_data = json_decode($json_data_raw, true);

			if ($json_data !== null) {
	            echo '<!-- Generating select boxes -->';
	            echo '<div class="custom-product-options">';
	            echo '<input type="hidden" id="selected-price" name="selected_price" value="0">';
	            echo '<p class="price-message">See price after choosing your options</p>';
	            echo '<style> p.price { display: none; } </style>';

	            // Wood select box
				if (isset($json_data[0]['wood']) && is_array($json_data[0]['wood'])) {
				    echo '<div class="custom-option">';
				    echo '<label for="wood">Wood</label>';
				    echo '<select id="wood" class="custom-option-select" data-option-key="wood">';
				    echo '<option value="" data-price="0">Select Wood</option>';

				    foreach ($json_data[0]['wood'] as $wood_key => $wood_name) {
				        echo '<option value="' . esc_attr($wood_key) . '" data-price="0">' . esc_html($wood_name) . '</option>';
				    }

				    echo '</select>';
				    echo '</div>';
				}

				// Options select boxes
				if (array_key_exists('options', $json_data) && is_array($json_data['options'])) {
				    foreach ($json_data['options'] as $option_key => $option) {
				        $option_name = ucwords(str_replace('_', ' ', $option_key));
				        echo '<div class="custom-option">';
				        echo '<label for="' . esc_attr($option_key) . '">' . esc_html($option_name) . '</label>';
				        echo '<select id="' . esc_attr($option_key) . '" class="custom-option-select" data-option-key="' . esc_attr($option_key) . '">';
				        echo '<option value="" data-price="0">Select ' . esc_html($option_name) . '</option>';

				        foreach ($option as $value_key => $value) {
				            echo '<option value="' . esc_attr($value_key) . '" data-prices=\'' . json_encode($value['prices']) . '\'>' . esc_html($value['display']) . '</option>';
				        }

				        echo '</select>';
				        echo '</div>';
				    }
				} else {
				    echo '<!-- Options data not found in JSON data -->';
				}
			}
		}
	}