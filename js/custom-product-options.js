jQuery(function ($) {
  console.log('Script is running');

  function custom_product_options_get_option(option_name) {
    return customJsData[option_name];
  }

  let data; // Declare data as a global variable

  async function fetchJsonData(jsonFileUrl) {
    try {
      console.log('Fetching data from:', jsonFileUrl);
      const response = await fetch(jsonFileUrl);
      console.log('Response:', response);
      const data = await response.json();

      // Get the markup percentage from the options page
      console.log('customMarkupPercentage:', customJsData.customMarkupPercentage);
      const markupPercentage = parseFloat(customJsData.customMarkupPercentage);


      // Loop through the data and update the prices with the markup percentage
      const updatedData = data.map(item => {
        const updatedItem = {...item};
        for (const key in updatedItem) {
          if (key !== 'Size') {
            updatedItem[key] = parseFloat(updatedItem[key]) * (1 + markupPercentage / 100);
          }
        }
        return updatedItem;
      });

      return updatedData;
    } catch (error) {
      console.error('Error fetching JSON data:', error);
      throw error;
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

  async function displayCsvOptions(csvFile, elementId) {
    console.log('Displaying CSV options for:', csvFile);
    const data = await fetchJsonData(`${customJsData.pluginUrl}${csvFile}`);
    console.log('JSON data:', data);

    const woodOptions = Object.keys(data[0]).filter(key => key !== 'Size');
    const sizeOptions = Array.from(new Set(data.map(item => item.Size)));

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

    const updatePriceWithScope = () => updatePrice(data);
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

  init(); // call the init function when the script is loaded

});
