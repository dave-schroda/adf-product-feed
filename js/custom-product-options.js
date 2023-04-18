jQuery(document).ready(function ($) {
    var priceElement = $('p.price span.woocommerce-Price-amount.amount');
    var priceContainer = $('p.price');
    var priceMessage = $('.price-message');
    var priceText = priceElement.text();
    var priceMatch = priceText.match(/(\d+(?:\.\d+)?)/);
    var originalPrice = priceMatch ? parseFloat(priceMatch[0]) : 0;

    function updateProductPrice() {
        var woodSelect = $('#wood');
        var sizeSelect = $('#size');
        var selectedSizeOption = sizeSelect.find('option:selected');
        var woodPrices = selectedSizeOption.data('prices') || {};
        var selectedWoodPrice = parseFloat(woodPrices[woodSelect.val()]) || 0;
        var totalPrice = originalPrice + selectedWoodPrice;

        priceElement.text('$' + totalPrice.toFixed(2));
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

    $('.custom-option-select').on('change', function () {
        updateProductPrice();
        updateAddToCartButton();
    });

    updateProductPrice();
    updateAddToCartButton();

    // pass the selected options to the cart item data when the product is added to the cart
    $('form.cart').on('submit', function (e) {
        $('<input />').attr('type', 'hidden')
            .attr('name', 'custom_option_wood')
            .attr('value', $('#wood').val())
            .appendTo('form.cart');
        $('<input />').attr('type', 'hidden')
            .attr('name', 'custom_option_size')
            .attr('value', $('#size').val())
            .appendTo('form.cart');
    });

});
