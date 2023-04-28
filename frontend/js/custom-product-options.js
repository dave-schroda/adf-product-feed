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
        console.log('Updating product price...');
        var finalPrice = 0;

        var selectedWood = $('input[name="wood"]:checked').val();
        var selectedOptions = {};

        // Get selected options dynamically
        $('.custom-option-radio:checked').not('.wood-option-radio').each(function () {
            var value = $(this).val();
            value = value.replace(/&quot;/g, '\"');
            selectedOptions[$(this).attr('name')] = value;
        });

        console.log("Selected options: ", selectedOptions);

        for (var i = 0; i < jsonData.length; i++) {
            var item = jsonData[i];

            for (var j = 0; j < item.options.length; j++) {
                var option = item.options[j];

                var matchFound = true;

                // Check each selected option against the current JSON option
                for (var prop in selectedOptions) {
                    if (selectedOptions[prop] !== option[prop]) {
                        matchFound = false;
                        break;
                    }
                }

                if (matchFound && selectedWood in option.price) {
                    finalPrice = option.price[selectedWood];
                    break;
                } else if (!matchFound) {
                    console.log("Mismatch detected!");
                    console.log("Selected options: ", selectedOptions);
                    console.log("Current JSON option: ", option);
                }
            }

            // If the final price has been set, break the outer loop as well
            if (finalPrice !== 0) {
                break;
            }
        }

        console.log(customProductOptionsData.markup_percentage);
        console.log(selectedWood, finalPrice);

        finalPrice = Math.ceil(finalPrice * (customProductOptionsData.markup_percentage / 100));
        priceElement.text('$' + finalPrice.toFixed(2));
    }

    function checkOptionsSelected() {
        var allSelected = true;

        var optionGroups = [];
        $('.custom-option-radio, .wood-option-radio').each(function () {
            var groupName = $(this).attr('name');
            if ($.inArray(groupName, optionGroups) === -1) {
                optionGroups.push(groupName);
            }
        });

        for (var i = 0; i < optionGroups.length; i++) {
            var group = optionGroups[i];
            if ($('input[name="' + group + '"]:checked').length === 0) {
                allSelected = false;
                break;
            }
        }

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
        $('.custom-option-radio:checked').each(function() {
            var selectedValue = $(this).val();
            if (selectedValue) {
                selectedOptions[$(this).attr('name')] = selectedValue;
            }
        });

        // Loop over each radio group, excluding wood options
        var radioGroups = ['leaves', 'table_size'];
        radioGroups.forEach(function(group) {
            // Default all options to be disabled
            $('input[name="' + group + '"]').prop('disabled', true);

            // Loop over each option in the current radio group
            $('input[name="' + group + '"]').each(function() {
                var optionValue = $(this).val();

                // Check if the current option is available in the JSON data considering the other selected options
                jsonData.forEach(function(item) {
                    item.options.forEach(function(option) {
                        // Prepare a copy of selected options with the current option replaced
                        var currentOptions = Object.assign({}, selectedOptions, {[group]: optionValue});

                        // Exclude the wood selection from the current options
                        delete currentOptions['wood'];

                        // Check if the current combination of options is available
                        var isAvailable = true;
                        for (var key in currentOptions) {
                            if (!option[key] || option[key] !== currentOptions[key]) {
                                isAvailable = false;
                                break;
                            }
                        }

                        // If the wood is not in the price object, then this combination is not available
                        if (selectedOptions['wood'] && !(selectedOptions['wood'] in option.price)) {
                            isAvailable = false;
                        }

                        // If the current combination is available, enable the current option
                        if (isAvailable) {
                            $('input[value="' + optionValue.replace(/"/g, '\\"') + '"]').prop('disabled', false);
                        }
                    });
                });
            });
        });
    }

    $('.custom-option-radio, .wood-option-radio').on('change', function () {
        updateOptionAvailability();
        updateProductPrice();
        updateAddToCartButton();
    });

    // pass the selected options to the cart item data when the product is added to the cart
    $('form.cart').on('submit', function (e) {
        $('.custom-option-radio').each(function () {
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
    
    // Initialize the accordion
    $(".custom-option").accordion({
        collapsible: true,
        heightStyle: "content",
        active: false,
        beforeActivate: function(event, ui) {
            if (ui.newHeader.hasClass('disabled')) {
                event.preventDefault();
            }
        }
    });

    // Add event listeners to update the accordion title and control the accordion behavior
    $('.custom-option-radio').on("change", function() {
        const selectedOptionId = $(this).attr('id');
        const selectedOptionLabel = $('label[for="' + selectedOptionId + '"]').text();

        const currentAccordion = $(this).closest('.custom-option');
        const currentAccordionIndex = $("#custom-product-options .custom-option").index(currentAccordion);
        const currentAccordionHeader = currentAccordion.find('h3');
        const nextAccordion = $("#custom-product-options .custom-option").eq(currentAccordionIndex + 1);
        const nextAccordionHeader = nextAccordion.find('h3');
        
        // Update the choice text in the current accordion header
        currentAccordionHeader.find('span.option-text').text(selectedOptionLabel);
        currentAccordionHeader.removeClass('disabled');

        // Check if the accordion has been activated for the first time
        if (currentAccordionHeader.attr('data-activated') === 'true') {
            // Close the current accordion without opening the next one
            currentAccordion.accordion("option", "active", false);
        } else {
            // Enable the next accordion
            nextAccordionHeader.removeClass('disabled');
            nextAccordion.find('.custom-option-radio').prop('disabled', false);

            // Close the current accordion and open the next one
            currentAccordion.accordion("option", "active", false);
            nextAccordion.accordion("option", "active", 0);
        }

        // Mark the accordion as activated
        currentAccordionHeader.attr('data-activated', 'true');
    });
});
