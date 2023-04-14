jQuery(function ($) {
  console.log('Script is running');

  function custom_product_options_get_option(option_name) {
    return customJsData[option_name];
  }

  let data; // Declare data as a global variable

  async function fetchCsvData(jsonFileUrl) {
    try {
      console.log('Fetching data from:', jsonFileUrl);
      const response = await fetch(jsonFileUrl);
      console.log('Response:', response);
      const data = await response.json();
      console.log('Data:', data);

      // Convert the values to numbers
      const updatedData = data.map(item => {
        const updatedItem = {...item};
        for (const key in updatedItem) {
          if (key !== 'Size') {
            updatedItem[key] = parseFloat(updatedItem[key]);
          }
        }
        return updatedItem;
      });

      // Get the markup percentage from the options page
      const markupPercentage = parseFloat(custom_product_options_get_option('markup_percentage'));

      // Loop through the data and update the prices with the markup percentage
      const formattedData = updatedData.map(item => {
        const formattedItem = {...item};
        for (const key in formattedItem) {
          if (key !== 'Size') {
            formattedItem[key] = formattedItem[key] * (1 + markupPercentage / 100);
          }
        }
        return formattedItem;
      });

      return formattedData;
    } catch (error) {
      console.error('Error fetching CSV data:', error);
    }
  }

  function updatePrice(data) {
    const woodSelect = document.getElementById('woodSelect');
    const sizeSelect = document.getElementById('sizeSelect');
    const originalPriceElement = document.querySelector('.price del .woocommerce-Price-amount');

    if (!woodSelect || !sizeSelect || !originalPriceElement) {
      return;
    }

    const selectedWood = woodSelect.value;
    const selectedSize = sizeSelect.value;

    if (!selectedWood || !selectedSize || selectedWood === 'Select Wood' || selectedSize === 'Select Size') {
      originalPriceElement.innerHTML = '<span class="woocommerce-Price-currencySymbol">$</span>Please select both wood and size.';
      return;
    }

    const product = data.find(item => item.Size === selectedSize);
    if (product) {
      const price = parseFloat(product[selectedWood]);
      if (!isNaN(price)) {
        originalPriceElement.innerHTML = `<span class="woocommerce-Price-currencySymbol">$</span>${price.toFixed(2)}`;

        // Trigger a custom event to inform the plugin that the price has changed
        const priceChangeEvent = new CustomEvent('priceChange', { detail: { price } });
        originalPriceElement.dispatchEvent(priceChangeEvent);
      } else {
        originalPriceElement.innerHTML = 'Product not found.';
      }
    } else {
      originalPriceElement.innerHTML = 'Product not found.';
    }
  }

  async function displayCsvOptions(jsonFile, elementId) {
    console.log('Displaying CSV options for:', jsonFile);
    const formattedData = await fetchCsvData(`${customJsData.pluginUrl}${jsonFile}`);
    console.log('JSON data:', formattedData);

    const woodOptions = Array.from(new Set(Object.keys(formattedData[0]).filter(key => key !== 'Size')));
    const sizeOptions = Array.from(new Set(formattedData.map(item => item.Size)));

    const container = document.getElementById(elementId);

    const woodSelect = document.createElement('select');
    woodSelect.id = 'woodSelect';

    const woodDefaultOption = document.createElement('option');
    woodDefaultOption.value = 'Select Wood';
    woodDefaultOption.text = 'Select Wood';
    woodSelect.appendChild(woodDefaultOption);

    woodOptions.forEach(wood => {
      const option = document.createElement('option');
      option.value = wood;
      option.text = wood;
      woodSelect.appendChild(option);
    });

    const sizeSelect = document.createElement('select');
    sizeSelect.id = 'sizeSelect';

    const sizeDefaultOption = document.createElement('option');
    sizeDefaultOption.value = 'Select Size';
    sizeDefaultOption.text = 'Select Size';
    sizeSelect.appendChild(sizeDefaultOption);

    sizeOptions.forEach(size => {
      const option = document.createElement('option');
      option.value = size;
      option.text = size;
      sizeSelect.appendChild(option);
    });

    container.appendChild(woodSelect);
    container.appendChild(sizeSelect);

    const updatePriceWithScope = () => updatePrice(formattedData);
    woodSelect.addEventListener('change', updatePriceWithScope);
    sizeSelect.addEventListener('change', updatePriceWithScope);
  }

  function init() {
    const skuElement = document.querySelector('.sku');
    if (skuElement) {
      const sku = skuElement.textContent.trim();
      console.log('Product SKU:', sku);
      displayCsvOptions(`product-csv-files/${sku}.json`, 'custom-options');
    } else {
      console.error('SKU not found on the product page');
    }
  }

  $(document).ready(init);

  // listen for the priceChange event to update the offer
  const originalPriceElement = document.querySelector('.price del .woocommerce-Price-amount');
  if (originalPriceElement) {
    originalPriceElement.addEventListener('priceChange', (event) => {
      const price = event.detail.price;
      const offer = document.querySelector('.price ins .woocommerce-Price-amount');
      if (offer) {
        offer.innerHTML = `<span class="woocommerce-Price-currencySymbol">$</span>${price.toFixed(2)}`;
      }
    });
  }

})();
