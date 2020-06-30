/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 0);
/******/ })
/************************************************************************/
/******/ ([
/* 0 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
// ESM COMPAT FLAG
__webpack_require__.r(__webpack_exports__);

// CONCATENATED MODULE: ./src/js/components/select.js
let SelectComponent = function ($el, params, $) {
  if ( ! $el) {
    return;
  }

  this.$ = $;
  this.$el = $($el);
  this.params = params || {};

  this._initSelect2();
  this._initEvents();
};

SelectComponent.prototype._initSelect2 = function () {
  let $ = this.$,
      that = this;

  let params = {
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
    }
  };

  if (this.params.i10n && this.params.i10n.noResult) {
    params.language = {
      noResults: function() {
        return that.params.i10n.noResult;
      }
    };
  }

  if (typeof $.fn.select2 === 'function') {
    this.$el.select2(params);
  }
};

SelectComponent.prototype._initEvents = function () {
  let that = this,
      $ = this.$;

  that.$el.on('change', function () {
    if ( ! that.params.mirror) {
      return;
    }

    let $mirror = $('#' + that.params.mirror);

    if ($mirror.length) {
      $mirror.val(that.$el.val()).trigger('change.select2');
    }

    if (that.params.ajaxAction) {
      that._loadAjax();
    }
  });
};

SelectComponent.prototype._loadAjax = function () {
  let that = this,
      $ = this.$;

  if (that.params.events && that.params.events.beforeRequest) {
    that.params.events.beforeRequest();
  }

  window.WCUkrShippingRouter.post(that.params.ajaxAction, that.params.ajaxData(), function (json) {
    if (that.params.events && that.params.events.response) {
      that.params.events.response();
      that._processAjaxResponse(json);
    }
  });
};

SelectComponent.prototype._processAjaxResponse = function (json) {
  if (json.success) {
    if ('function' === typeof this.params.responseMapper) {
      let data = this.params.responseMapper(json.data);

      this._updateHtml(data);
    }
  }
  else {
    console.error(json.data);
  }
};

SelectComponent.prototype._updateHtml = function (data) {
  let $ = this.$,
      html = '';

  html += '<option value="">' + this.params.targetPlaceholder + '</option>';

  for (let i = 0; i < data.length; i++) {
    html += '<option value="' + data[i].value + '">' + data[i].name + '</option>';
  }

  this.params.ajaxTarget.forEach(function (target) {
    if ($('#' + target).length) {
      $('#' + target).html(html);
    }
  });
};


// CONCATENATED MODULE: ./src/js/checkout.js




(function ($) {
  let $shippingBox = $('.wc-ukr-shipping-np-fields'),
      $differentShipping = document.getElementById('ship-to-different-address-checkbox'),
      currentCountry;

  let setLoadingState = function () {
    $shippingBox.addClass('wcus-state-loading');
  };

  let unsetLoadingState = function () {
    $shippingBox.removeClass('wcus-state-loading');
  };

  let isNovaPoshtaShippingSelected = function () {
    let currentShipping = $('.shipping_method').length > 1 ?
      $('.shipping_method:checked').val() :
      $('.shipping_method').val();

    return currentShipping && currentShipping.match(/^nova_poshta_shipping.+/i);
  };

  let selectShipping = function () {
    if (currentCountry === 'UA' && isNovaPoshtaShippingSelected()) {
      if ($differentShipping && $differentShipping.checked) {
        $('#wcus_np_shipping_fields').css('display', 'block');
      }
      else {
        $('#wcus_np_billing_fields').css('display', 'block');
      }
    }
    else {
      $('.wc-ukr-shipping-np-fields').css('display', 'none');
    }
  };

  if ($differentShipping) {
    $differentShipping.addEventListener('click', function () {
      if ( ! isNovaPoshtaShippingSelected()) {
        return;
      }

      if (this.checked) {
        $('#wcus_np_shipping_fields').css('display', 'block');
        $('#wcus_np_billing_fields').css('display', 'none');
      }
      else {
        $('#wcus_np_shipping_fields').css('display', 'none');
        $('#wcus_np_billing_fields').css('display', 'block');
      }
    });
  }

  let disableDefaultBillingFields = function () {
    if (isNovaPoshtaShippingSelected() && wc_ukr_shipping_globals.disableDefaultBillingFields === 'true') {
      // Billing
      $('#billing_address_1_field').css('display', 'none');
      $('#billing_address_2_field').css('display', 'none');
      $('#billing_city_field').css('display', 'none');
      $('#billing_state_field').css('display', 'none');
      $('#billing_postcode_field').css('display', 'none');

      // Shipping
      $('#shipping_address_1_field').css('display', 'none');
      $('#shipping_address_2_field').css('display', 'none');
      $('#shipping_city_field').css('display', 'none');
      $('#shipping_state_field').css('display', 'none');
      $('#shipping_postcode_field').css('display', 'none');
    }
    else {
      // Billing
      $('#billing_address_1_field').css('display', 'block');
      $('#billing_address_2_field').css('display', 'block');
      $('#billing_city_field').css('display', 'block');
      $('#billing_state_field').css('display', 'block');
      $('#billing_postcode_field').css('display', 'block');

      // Shipping
      $('#shipping_address_1_field').css('display', 'block');
      $('#shipping_address_2_field').css('display', 'block');
      $('#shipping_city_field').css('display', 'block');
      $('#shipping_state_field').css('display', 'block');
      $('#shipping_postcode_field').css('display', 'block');
    }
  };

  let initialize = function () {
    let $customAddressCheckbox = $('.j-wcus-np-custom-address');

    let showCustomAddress = function () {
      $('.j-wcus-warehouse-block').slideUp(400);
      $('.j-wcus-np-custom-address-block').slideDown(400);
    };

    let hideCustomAddress = function () {
      $('.j-wcus-warehouse-block').slideDown(400);
      $('.j-wcus-np-custom-address-block').slideUp(400);
    };

    if ($customAddressCheckbox.length) {
      $customAddressCheckbox.on('click', function () {
        let $relation = document.getElementById(this.dataset['relationSelect']);

        if ($relation) {
          $relation.checked = this.checked;
        }

        if (this.checked) {
          showCustomAddress();
        }
        else {
          hideCustomAddress();
        }
      });
    }
  };

  $(function() {
    $('.wc-ukr-shipping-np-fields').css('display', 'none');

    $(document.body).bind('update_checkout', function (event, args) {
      setLoadingState();
    });

    $(document.body).bind('updated_checkout', function (event, args) {
      currentCountry = $('#billing_country').length ? $('#billing_country').val() : 'UA';
      selectShipping();
      disableDefaultBillingFields();
      unsetLoadingState();
    });

    let getAreaComponentParams = function (type) {
      let mirrorComponent = 'billing' === type
        ? 'wcus_np_shipping_area'
        : 'wcus_np_billing_area';

      let refTarget = '#wcus_np_' + type + '_area';

      return {
        mirror: mirrorComponent,
        ajaxAction: 'wc_ukr_shipping_get_cities',
        ajaxData: function () {
          return {
            ref: $(refTarget).val(),
            nonce: wc_ukr_shipping_globals.nonce
          };
        },
        ajaxTarget: [
          'wcus_np_billing_city',
          'wcus_np_shipping_city'
        ],
        targetPlaceholder: wc_ukr_shipping_globals.i10n.placeholder_city,
        responseMapper: function (data) {
          let result = [];

          for (let i = 0; i < data.length; i++) {
            result.push({
              value: data[i]['ref'],
              name: wc_ukr_shipping_globals.lang === 'ru' ?
                data[i]['description_ru'] :
                data[i]['description']
            });
          }

          return result;
        },
        events: {
          beforeRequest: function () {
            setLoadingState();
          },
          response: function () {
            unsetLoadingState();
          }
        },
        i10n: {
          noResult: wc_ukr_shipping_globals.i10n.not_found
        }
      };
    };

    let getCityComponentParams = function (type) {
      let mirrorComponent = 'billing' === type
        ? 'wcus_np_shipping_city'
        : 'wcus_np_billing_city';

      let refTarget = '#wcus_np_' + type + '_city';

      return {
        mirror: mirrorComponent,
        ajaxAction: 'wc_ukr_shipping_get_warehouses',
        ajaxData: function () {
          return {
            ref: $(refTarget).val(),
            nonce: wc_ukr_shipping_globals.nonce
          };
        },
        ajaxTarget: [
          'wcus_np_billing_warehouse',
          'wcus_np_shipping_warehouse'
        ],
        targetPlaceholder: wc_ukr_shipping_globals.i10n.placeholder_warehouse,
        responseMapper: function (data) {
          let result = [];

          for (let i = 0; i < data.length; i++) {
            result.push({
              value: data[i]['ref'],
              name: wc_ukr_shipping_globals.lang === 'ru' ?
                data[i]['description_ru'] :
                data[i]['description']
            });
          }

          return result;
        },
        events: {
          beforeRequest: function () {
            setLoadingState();
          },
          response: function () {
            unsetLoadingState();
          }
        },
        i10n: {
          noResult: wc_ukr_shipping_globals.i10n.not_found
        }
      };
    };

    let getWarehouseComponentParams = function (type) {
      let mirrorComponent = 'billing' === type
        ? 'wcus_np_shipping_warehouse'
        : 'wcus_np_billing_warehouse';

      return {
        mirror: mirrorComponent,
        i10n: {
          noResult: wc_ukr_shipping_globals.i10n.not_found
        }
      };
    };

    let npBillingArea = new SelectComponent(
      document.getElementById('wcus_np_billing_area'),
      getAreaComponentParams('billing'),
      $
    );

    let npShippingArea = new SelectComponent(
      document.getElementById('wcus_np_shipping_area'),
      getAreaComponentParams('shipping'),
      $
    );

    let npBillingCity = new SelectComponent(
      document.getElementById('wcus_np_billing_city'),
      getCityComponentParams('billing'),
      $
    );

    let npShippingCity = new SelectComponent(
      document.getElementById('wcus_np_shipping_city'),
      getCityComponentParams('shipping'),
      $
    );

    let npBillingWarehouse = new SelectComponent(
      document.getElementById('wcus_np_billing_warehouse'),
      getWarehouseComponentParams('billing'),
      $
    );

    let npShippingWarehouse = new SelectComponent(
      document.getElementById('wcus_np_shipping_warehouse'),
      getWarehouseComponentParams('shipping'),
      $
    );

    initialize();
  });

})(jQuery);

/***/ })
/******/ ]);