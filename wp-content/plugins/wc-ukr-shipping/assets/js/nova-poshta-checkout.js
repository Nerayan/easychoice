'use strict';

(function ($) {
  let $shippingBox = $('#nova_poshta_shipping_fields'),
      currentCountry;

  let setLoadingState = function () {
    $shippingBox.addClass('wcus-state-loading');
  };

  let unsetLoadingState = function () {
    $shippingBox.removeClass('wcus-state-loading');
  };

  $('.woocommerce-shipping-fields').css('display', 'none');

  let isNovaPoshtaShippingSelected = function () {
    let currentShipping = $('.shipping_method').length > 1 ?
      $('.shipping_method:checked').val() :
      $('.shipping_method').val();

    return currentShipping && currentShipping.match(/^nova_poshta_shipping.+/i);
  };

  let selectShipping = function () {
    if (currentCountry === 'UA' && isNovaPoshtaShippingSelected()) {
      $('#nova_poshta_shipping_fields').css('display', 'block');
      $('.woocommerce-shipping-fields').css('display', 'none');
    }
    else {
      $('#nova_poshta_shipping_fields').css('display', 'none');
      $('.woocommerce-shipping-fields').css('display', 'block');
    }
  };

  let disableDefaultBillingFields = function () {
    if (isNovaPoshtaShippingSelected() && wc_ukr_shipping_globals.disableDefaultBillingFields === 'true') {
      $('#billing_address_1_field').css('display', 'none');
      $('#billing_address_2_field').css('display', 'none');
      $('#billing_city_field').css('display', 'none');
      $('#billing_state_field').css('display', 'none');
      $('#billing_postcode_field').css('display', 'none');
    }
    else {
      $('#billing_address_1_field').css('display', 'block');
      $('#billing_address_2_field').css('display', 'block');
      $('#billing_city_field').css('display', 'block');
      $('#billing_state_field').css('display', 'block');
      $('#billing_postcode_field').css('display', 'block');
    }
  };

  let initialize = function () {
    $('#nova_poshta_shipping_area').on('change', function () {
      let $city = $('#nova_poshta_shipping_city'),
        $warehouse = $('#nova_poshta_shipping_warehouse');

      setLoadingState();

      WCUkrShippingRouter.getCities({
        areaRef: this.value,
        nonce: wc_ukr_shipping_globals.nonce,
        success: function (json) {
          unsetLoadingState();

          if (json.success === true) {
            $warehouse.html('<option value="">' + wc_ukr_shipping_globals.i10n.placeholder_warehouse + '</option>');

            let html = '<option value="">' + wc_ukr_shipping_globals.i10n.placeholder_city + '</option>';

            for (let i = 0; i < json.data.length; i++) {
              let city = json.data[i],
                cityName = wc_ukr_shipping_globals.lang === 'ru' ?
                  city['description_ru'] :
                  city['description'];

              html += '<option value="' + city['ref'] + '">' + cityName + '</option>';
            }

            $city.html(html);
          }
          else {
            alert(json.data);
          }
        }
      });
    });

    $('#nova_poshta_shipping_city').on('change', function () {
      let $warehouse = $('#nova_poshta_shipping_warehouse');

      setLoadingState();

      WCUkrShippingRouter.getWarehouses({
        cityRef: this.value,
        nonce: wc_ukr_shipping_globals.nonce,
        success: function (json) {
          unsetLoadingState();

          if (json.success === true) {
            let html = '<option value="">' + wc_ukr_shipping_globals.i10n.placeholder_warehouse + '</option>';

            for (let i = 0; i < json.data.length; i++) {
              let warehouse = json.data[i],
                warehouseName = wc_ukr_shipping_globals.lang === 'ru' ?
                  warehouse['description_ru'] :
                  warehouse['description'];

              html += '<option value="' + warehouse['ref'] + '">' + warehouseName + '</option>';
            }

            $warehouse.html(html);
          }
          else {
            alert(json.data);
          }
        }
      });
    });

    let $customAddressCheckbox = document.getElementById('np_custom_address');

    let showCustomAddress = function () {

      if ($customAddressCheckbox.checked) {
        $('#nova-poshta-shipping-info').slideUp(400);
        $('#np_custom_address_block').slideDown(400);
      }
      else {
        $('#nova-poshta-shipping-info').slideDown(400);
        $('#np_custom_address_block').slideUp(400);
      }

    };

    if ($customAddressCheckbox) {
      showCustomAddress();
      $customAddressCheckbox.onclick = showCustomAddress;
    }

    if (typeof $.fn.select2 === 'function') {
      $('.wc-ukr-shipping-select').select2({
        sorter: function (data) {
          data.sort(function (a, b) {
            let $search = $('.select2-search__field');

            if (0 === $search.length || '' === $search.val()) {
              return data;
            }

            let textA = a.text.toLowerCase(),
                textB = b.text.toLowerCase(),
                search = $search.val().toLowerCase();

            if (textA.indexOf(search) < textB.indexOf(search)) {
              return -1;
            }

            if (textA.indexOf(search) > textB.indexOf(search)) {
              return 1;
            }

            return 0;
          });

          return data;
        },
        language: {
          noResults: function() {
            return wc_ukr_shipping_globals.i10n.not_found;
          }
        }
      });
    }
  };

  $(function() {
    $('#nova_poshta_shipping_fields').css('display', 'none');

    $(document.body).bind('update_checkout', function (event, args) {
      setLoadingState();
    });

    $(document.body).bind('updated_checkout', function (event, args) {
      currentCountry = $('#billing_country').length ? $('#billing_country').val() : 'UA';
      selectShipping();
      disableDefaultBillingFields();
      unsetLoadingState();
    });

    initialize();
  });

})(jQuery);