'use strict';

(function ($) {

  window.WCUkrShippingRouter = {

    saveOptions: function (options) {
      $.ajax({
        method: 'POST',
        url: wc_ukr_shipping_globals.ajaxUrl,
        data: {
          action: 'wc_ukr_shipping_save_settings',
          body: options.data
        },
        dataType: 'json',
        success: function (json) {
          options.success(json);
        },
        error: function (xhr) {
          console.log(xhr);
        },
        complete: function() {
          options.complete();
        }
      });
    },

    loadAreas: function (options) {
      $.ajax({
        method: 'POST',
        url: wc_ukr_shipping_globals.ajaxUrl,
        data: {
          action: 'wc_ukr_shipping_load_areas',
          body: options.data
        },
        dataType: 'json',
        success: function (json) {
          options.success(json);
        }
      });
    },

    loadCities: function (options) {
      $.ajax({
        method: 'POST',
        url: wc_ukr_shipping_globals.ajaxUrl,
        data: {
          action: 'wc_ukr_shipping_load_cities',
          body: options.data
        },
        dataType: 'json',
        success: function (json) {
          options.success(json);
        }
      });
    },

    loadWarehouses: function (options) {
      $.ajax({
        method: 'POST',
        url: wc_ukr_shipping_globals.ajaxUrl,
        data: {
          action: 'wc_ukr_shipping_load_warehouses',
          body: options.data
        },
        dataType: 'json',
        success: function (json) {
          options.success(json);
        }
      });
    },

    getAreas: function (options) {
      $.ajax({
        method: 'POST',
        url: wc_ukr_shipping_globals.ajaxUrl,
        data: {
          action: 'wc_ukr_shipping_get_areas'
        },
        dataType: 'json',
        success: function (json) {
          options.success(json);
        }
      });
    },

    getCities: function (options) {
      $.ajax({
        method: 'POST',
        url: wc_ukr_shipping_globals.ajaxUrl,
        data: {
          action: 'wc_ukr_shipping_get_cities',
          body: {
            ref: options.areaRef
          }
        },
        dataType: 'json',
        success: function (json) {
          options.success(json);
        }
      });
    },

    getWarehouses: function (options) {
      $.ajax({
        method: 'POST',
        url: wc_ukr_shipping_globals.ajaxUrl,
        data: {
          action: 'wc_ukr_shipping_get_warehouses',
          body: {
            ref: options.cityRef
          }
        },
        dataType: 'json',
        success: function (json) {
          options.success(json);
        }
      });
    }

  };

})(jQuery);