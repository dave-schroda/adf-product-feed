jQuery(document).ready(function ($) {
    var priceElement = $('p.price span.woocommerce-Price-amount.amount');
    var priceContainer = $('p.price');
    var priceMessage = $('.price-message');
    var finalPrice = 0; // Declare finalPrice at a higher scope

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
        finalPrice = 0;

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

    function updateOptionAvailability() {
        console.log('Updating option availability...');

        // Get all selected options
        var selectedOptions = {};
        $('.custom-option-radio:checked, .wood-option-radio:checked').each(function() {
            var selectedValue = $(this).val();
            selectedOptions[$(this).attr('name')] = selectedValue;
        });

        // Identify unique groups of options
        var optionGroups = [];
        $('.custom-option-radio, .wood-option-radio').each(function () {
            var groupName = $(this).attr('name');
            if ($.inArray(groupName, optionGroups) === -1) {
                optionGroups.push(groupName);
            }
        });

        // Loop over each radio group
        optionGroups.forEach(function(group) {
            // Loop over each option in the current radio group
            $('input[name="' + group + '"]').each(function() {
                var optionValue = $(this).val();

                // Check if there's any combination in the JSON data that includes the currently selected options and this option value
                var isOptionAvailable = $.isEmptyObject(selectedOptions); // If no options have been selected yet, consider the option as available
                jsonData.forEach(function(item) {
                    item.options.forEach(function(option) {
                        var isCombinationValid = true;
                        var combinedOptions = Object.assign({}, selectedOptions, {[group]: optionValue});
                        for (var key in combinedOptions) {
                            if (key === "wood" && !(combinedOptions[key] in option.price)) {
                                isCombinationValid = false;
                                break;
                            } else if (key !== "wood" && option[key] !== combinedOptions[key]) {
                                isCombinationValid = false;
                                break;
                            }
                        }
                        // Only consider the option as available if it is valid
                        if (isCombinationValid) {
                            isOptionAvailable = true;
                        }
                    });
                });

                // If the option is available, enable it, otherwise disable it
                var $thisOption = $(this);
                $thisOption.prop('disabled', !isOptionAvailable);
                
                // If the option is selected but now disabled, uncheck it and trigger a change event
                if (!isOptionAvailable && $thisOption.is(':checked')) {
                    $thisOption.prop('checked', false);
                }
            });
        });
    }

    // Call the function whenever a radio button is clicked
    $('.custom-option-radio, .wood-option-radio').on('click', function() {
        // Allow the radio button selection to update first
        setTimeout(updateOptionAvailability, 0);
    });


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
            if ($('input[name="' + group + '"]:checked:not(:disabled)').length === 0) {
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

    $('.custom-option-radio, .wood-option-radio').on('change', function () {
        updateOptionAvailability(); // and here
        updateProductPrice();
        updateAddToCartButton();
    });

    // pass the selected options to the cart item data when the product is added to the cart
    $('form.cart').on('submit', function (e) {
        $('.custom-option-radio').each(function () {
            var currentOption = $(this);
            if (currentOption.is(':checked')) { // Check if the current option is selected
                var optionName = currentOption.attr('name');
                var optionLabel = $('input[name="label_' + optionName + '"]').val();
                $('<input />').attr('type', 'hidden')
                    .attr('name', 'custom_option_' + optionName)
                    .attr('value', currentOption.val())
                    .appendTo('form.cart');
                $('<input />').attr('type', 'hidden')
                    .attr('name', 'label_' + optionName)
                    .attr('value', optionLabel)
                    .appendTo('form.cart');
            }
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
        let selectedOptionId = $(this).attr('id');
        selectedOptionId = selectedOptionId.replace(/"/g, '\\"'); // Escape the double quotes
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
