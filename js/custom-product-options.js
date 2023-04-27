jQuery(document).ready(function ($) {
    var priceElement = $('p.price span.woocommerce-Price-amount.amount');
    var priceContainer = $('p.price');
    var priceMessage = $('.price-message');

    var jsonData; // Store JSON data

    // Fetch JSON data
    $.getJSON(php_vars.json_file_url, function(data) {
        jsonData = data;
        
        // Call these functions after the JSON data has loaded
        updateOptionAvailability();
        updateProductPrice();
        updateAddToCartButton();
    });

    function updateProductPrice() {
        // Iterate over all select boxes
        $(".custom-option-select").each(function() {
            // Initialize the final price to 0
            var finalPrice = 0;

            // Get the selected option
            var selectedOption = $(this).find("option:selected").val();

            // Get the selected wood
            var selectedWood = $('#wood').val();

            jsonData.forEach(function(item) {
                item.options.forEach(function(option) {
                    if (option.leaves === selectedOption || option.table_size === selectedOption) {
                        if (selectedWood in option.price) {
                            finalPrice += option.price[selectedWood];
                        }
                    }
                });
            });

            // Update the final price
            finalPrice = Math.ceil(finalPrice * (customProductOptionsData.markup_percentage / 100));
            priceElement.text('$' + finalPrice.toFixed(2));
        });
    }

    function checkOptionsSelected() {
        var allSelected = true;
        $('.custom-option-select').each(function () {
            if ($(this).val() === '') {
                allSelected = false;
                return false;
            }
        });
        return allSelected;
    }

    function updateAddToCartButton() {
        if (checkOptionsSelected()) {
            $('button.single_add_to_cart_button').prop('disabled', false);
            priceContainer.show();
            priceMessage.hide();
        } else {
            $('button.single_add_to_cart_button').prop('disabled', true);
            priceContainer.hide();
            priceMessage.show();
        }
    }

    function updateOptionAvailability() {
        // Get all selected options
        var selectedOptions = {};
        $('.custom-option-select').each(function() {
            var selectedValue = $(this).find('option:selected').val();
            if (selectedValue) {
                selectedOptions[$(this).attr('id')] = selectedValue;
            }
        });

        // Loop over each dropdown
        $('.custom-option-select').each(function () {
            var selectId = $(this).attr('id');
            var selectElement = $(this);

            // Default all options to be disabled
            selectElement.find('option').prop('disabled', true);

            // Loop over each option in the current dropdown
            selectElement.find('option').each(function() {
                var optionValue = $(this).val();

                // Skip the empty option
                if (!optionValue) {
                    return;
                }

                // Check if the current option is available in the JSON data considering the other selected options
                jsonData.forEach(function(item) {
                    item.options.forEach(function(option) {
                        // Prepare a copy of selected options with the current option replaced
                        var currentOptions = Object.assign({}, selectedOptions, {[selectId]: optionValue});
                        
                        // Check if the current combination of options is available
                        var isAvailable = true;
                        for (var key in currentOptions) {
                            if (!option[key] || option[key] !== currentOptions[key]) {
                                isAvailable = false;
                                break;
                            }
                        }

                        // If the current combination is available, enable the current option
                        if (isAvailable) {
                            selectElement.find('option[value="' + optionValue.replace(/"/g, '\\"') + '"]').prop('disabled', false);
                        }
                    });
                });
            });
        });
    }

    $('.custom-option-select').on('change', function () {
        updateOptionAvailability();
        updateProductPrice();
        updateAddToCartButton();
    });

    // pass the selected options to the cart item data when the product is added to the cart
    $('form.cart').on('submit', function (e) {
        $('.custom-option-select').each(function () {
            var currentOption = $(this);
            $('<input />').attr('type', 'hidden')
                .attr('name', 'custom_option_' + currentOption.attr('id'))
                .attr('value', currentOption.val())
                .appendTo('form.cart');
        });

        $('<input />').attr('type', 'hidden')
            .attr('name', 'selected_price') // Set the name attribute to 'selected_price'
            .attr('value', finalPrice) // Set the value attribute to the finalPrice variable
            .appendTo('form.cart');
    });

});
