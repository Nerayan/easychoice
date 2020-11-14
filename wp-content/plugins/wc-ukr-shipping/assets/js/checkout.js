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
/******/ 	return __webpack_require__(__webpack_require__.s = 1);
/******/ })
/************************************************************************/
/******/ ([
/* 0 */,
/* 1 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
// ESM COMPAT FLAG
__webpack_require__.r(__webpack_exports__);

// CONCATENATED MODULE: ./src/js/components/select.js
function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

var WCUSSelect = /*#__PURE__*/function () {
  function WCUSSelect($el, params, $) {
    _classCallCheck(this, WCUSSelect);

    if (!$el) {
      return;
    }

    this._$ = $;
    this._$el = $($el);
    this._params = params || {};

    this._initSelect2();

    this._initEvents();
  }

  _createClass(WCUSSelect, [{
    key: "setValue",
    value: function setValue(val) {
      if (!this._$el) {
        return;
      }

      this._$el.val(val).trigger('change.select2');
    }
  }, {
    key: "getValue",
    value: function getValue() {
      return this._$el.val();
    }
  }, {
    key: "setOptions",
    value: function setOptions(options) {
      var html = '';

      if (this._params.placeholder) {
        options.unshift({
          value: '',
          name: this._params.placeholder
        });
      }

      options.forEach(function (option) {
        html += "<option value=\"".concat(option.value, "\">").concat(option.name, "</option>");
      });
      this.setOptionsHtml(html);

      if (this._hasMirror()) {
        this._getMirror().setOptionsHtml(html);
      }

      if (this._params.ajax) {
        this._params.ajax.target().setOptions([]);
      }
    }
  }, {
    key: "setOptionsHtml",
    value: function setOptionsHtml(html) {
      if (!this._$el) {
        return;
      }

      this._$el.html(html);
    }
  }, {
    key: "_initSelect2",
    value: function _initSelect2() {
      var that = this;

      if ('function' === typeof this._$.fn.select2) {
        this._$el.select2({
          sorter: function sorter(data) {
            data.sort(function (a, b) {
              var $search = that._$('.select2-search__field');

              if (0 === $search.length || '' === $search.val()) {
                return data;
              }

              var textA = a.text.toLowerCase(),
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
            noResults: function noResults() {
              return wc_ukr_shipping_globals.i10n.not_found;
            }
          }
        });
      }
    }
  }, {
    key: "_initEvents",
    value: function _initEvents() {
      var that = this;

      that._$el.on('change', function () {
        if (that._hasMirror()) {
          that._getMirror().setValue(that.getValue());
        }

        if (that._params.ajax) {
          that._getAjaxData();
        }
      });
    }
  }, {
    key: "_getAjaxData",
    value: function _getAjaxData() {
      var that = this;

      that._trigger('beforeRequest');

      var data = {
        action: that._params.ajax.action,
        _token: wc_ukr_shipping_globals.nonce,
        value: that.getValue()
      };

      if ('function' === typeof that._params.ajax.params) {
        data = Object.assign(data, that._params.ajax.params());
      }

      that._$.ajax({
        method: 'POST',
        url: wc_ukr_shipping_globals.ajaxUrl,
        data: data,
        dataType: 'json',
        success: function success(json) {
          that._trigger('response', {
            data: json.data
          });

          if (json.success) {
            that._params.ajax.target().setOptions(json.data.items);
          }
        },
        error: function error(jqXHR, textStatus, errorThrown) {
          that._trigger('response');

          console.error("WCUS Error: ".concat(jqXHR.status, " ").concat(jqXHR.statusText));
        }
      });
    }
  }, {
    key: "_hasMirror",
    value: function _hasMirror() {
      return 'function' === typeof this._params.mirror;
    }
  }, {
    key: "_getMirror",
    value: function _getMirror() {
      return this._params.mirror();
    }
  }, {
    key: "_trigger",
    value: function _trigger(event, params) {
      if (this._params.events && 'function' === typeof this._params.events[event]) {
        this._params.events[event](this, params || {});
      }
    }
  }]);

  return WCUSSelect;
}();


// CONCATENATED MODULE: ./src/js/checkout/utils.js
/* harmony default export */ var utils = ({
  init: function init($) {
    this._$ = $;
    this.$differentShipping = this._$('#ship-to-different-address-checkbox');
  },
  setLoadingState: function setLoadingState() {
    this._$('.wc-ukr-shipping-np-fields').addClass('wcus-state-loading');
  },
  unsetLoadingState: function unsetLoadingState() {
    this._$('.wc-ukr-shipping-np-fields').removeClass('wcus-state-loading');
  },
  getFieldType: function getFieldType() {
    if (this.$differentShipping.length && this.$differentShipping.prop('checked')) {
      return 'shipping';
    }

    return 'billing';
  },
  isCustomAddress: function isCustomAddress() {
    var type = this.getFieldType();
    return this._$('#wcus_np_' + type + '_custom_address_active').prop('checked') ? 1 : 0;
  },
  getAreaRef: function getAreaRef() {
    var type = this.getFieldType();
    return this._$('#wcus_np_' + type + '_area').val();
  },
  getCityRef: function getCityRef() {
    var type = this.getFieldType();
    return this._$('#wcus_np_' + type + '_city').val();
  },
  mergeSelectParams: function mergeSelectParams(params) {
    var that = this;
    return Object.assign({
      events: {
        beforeRequest: function beforeRequest(select) {
          that.setLoadingState();
        },
        response: function response(select) {
          that.unsetLoadingState();
        }
      },
      i10n: {
        noResult: wc_ukr_shipping_globals.i10n.not_found
      }
    }, params);
  }
});
// CONCATENATED MODULE: ./src/js/checkout/checkout.js





(function ($) {
  var $differentShipping = document.getElementById('ship-to-different-address-checkbox'),
      currentCountry;
  utils.init($);

  function calculateShipping() {
    if (!utils.getCityRef()) {
      return;
    }

    $('#place_order').prop('disabled', true);
    $.ajax({
      method: 'POST',
      url: wc_ukr_shipping_globals.ajaxUrl,
      data: {
        action: 'wcus_api_v2_calculate_cost',
        _token: wc_ukr_shipping_globals.nonce,
        wcus_ajax: 1,
        wcus_area_ref: utils.getAreaRef(),
        wcus_city_ref: utils.getCityRef(),
        wcus_address_shipping: utils.isCustomAddress(),
        wcus_payment_method: $('input[name="payment_method"]:checked').val(),
        wcus_shipping_name: $('#wcus-shipping-name').val()
      },
      dataType: 'json',
      success: function success(json) {
        $('#place_order').prop('disabled', false);

        if (json.success) {
          $('#wcus-shipping-cost').html(json.data.shipping);
          $('#wcus-order-total').html("<strong>".concat(json.data.total, "</strong>"));
        }
      },
      error: function error(jqXHR, textStatus, errorThrown) {
        $('#place_order').prop('disabled', false);
        console.error("WCUS Error: ".concat(jqXHR.status, " ").concat(jqXHR.statusText));
      }
    });
  }

  var isNovaPoshtaShippingSelected = function isNovaPoshtaShippingSelected() {
    var currentShipping = $('.shipping_method').length > 1 ? $('.shipping_method:checked').val() : $('.shipping_method').val();
    return currentShipping && currentShipping.match(/^nova_poshta_shipping.+/i);
  };

  var selectShipping = function selectShipping() {
    if (currentCountry === 'UA' && isNovaPoshtaShippingSelected()) {
      if ($differentShipping && $differentShipping.checked) {
        $('#wcus_np_shipping_fields').css('display', 'block');
      } else {
        $('#wcus_np_billing_fields').css('display', 'block');
      }
    } else {
      $('.wc-ukr-shipping-np-fields').css('display', 'none');
    }
  };

  if ($differentShipping) {
    $differentShipping.addEventListener('click', function () {
      if (!isNovaPoshtaShippingSelected()) {
        return;
      }

      if (this.checked) {
        $('#wcus_np_shipping_fields').css('display', 'block');
        $('#wcus_np_billing_fields').css('display', 'none');
      } else {
        $('#wcus_np_shipping_fields').css('display', 'none');
        $('#wcus_np_billing_fields').css('display', 'block');
      }
    });
  }

  var disableDefaultBillingFields = function disableDefaultBillingFields() {
    if (isNovaPoshtaShippingSelected() && wc_ukr_shipping_globals.disableDefaultBillingFields === 'true') {
      // Billing
      $('#billing_address_1_field').css('display', 'none');
      $('#billing_address_2_field').css('display', 'none');
      $('#billing_city_field').css('display', 'none');
      $('#billing_state_field').css('display', 'none');
      $('#billing_postcode_field').css('display', 'none'); // Shipping

      $('#shipping_address_1_field').css('display', 'none');
      $('#shipping_address_2_field').css('display', 'none');
      $('#shipping_city_field').css('display', 'none');
      $('#shipping_state_field').css('display', 'none');
      $('#shipping_postcode_field').css('display', 'none');
    } else {
      // Billing
      $('#billing_address_1_field').css('display', 'block');
      $('#billing_address_2_field').css('display', 'block');
      $('#billing_city_field').css('display', 'block');
      $('#billing_state_field').css('display', 'block');
      $('#billing_postcode_field').css('display', 'block'); // Shipping

      $('#shipping_address_1_field').css('display', 'block');
      $('#shipping_address_2_field').css('display', 'block');
      $('#shipping_city_field').css('display', 'block');
      $('#shipping_state_field').css('display', 'block');
      $('#shipping_postcode_field').css('display', 'block');
    }
  };

  var initialize = function initialize() {
    var $customAddressCheckbox = $('.j-wcus-np-custom-address');

    var showCustomAddress = function showCustomAddress() {
      $('.j-wcus-warehouse-block').slideUp(400);
      $('.j-wcus-np-custom-address-block').slideDown(400);
    };

    var hideCustomAddress = function hideCustomAddress() {
      $('.j-wcus-warehouse-block').slideDown(400);
      $('.j-wcus-np-custom-address-block').slideUp(400);
    };

    if ($customAddressCheckbox.length) {
      $customAddressCheckbox.on('click', function () {
        var $relation = document.getElementById(this.dataset['relationSelect']);

        if ($relation) {
          $relation.checked = this.checked;
        }

        if (this.checked) {
          showCustomAddress();
        } else {
          hideCustomAddress();
        }

        calculateShipping();
      });
    }
  };

  $(function () {
    $('.wc-ukr-shipping-np-fields').css('display', 'none');
    $(document.body).bind('update_checkout', function (event, args) {
      utils.setLoadingState();
    });
    $(document.body).bind('updated_checkout', function (event, args) {
      currentCountry = $('#billing_country').length ? $('#billing_country').val() : 'UA';
      selectShipping();
      disableDefaultBillingFields();
      utils.unsetLoadingState();
    });
    var bArea, sArea, bCity, sCity, bWarehouse, sWarehouse;
    bArea = new WCUSSelect(document.getElementById('wcus_np_billing_area'), utils.mergeSelectParams({
      mirror: function mirror() {
        return sArea;
      },
      ajax: {
        action: 'wcus_api_v2_get_cities',
        target: function target() {
          return bCity;
        }
      },
      placeholder: wc_ukr_shipping_globals.i10n.placeholder_area
    }), $);
    sArea = new WCUSSelect(document.getElementById('wcus_np_shipping_area'), utils.mergeSelectParams({
      mirror: function mirror() {
        return bArea;
      },
      ajax: {
        action: 'wcus_api_v2_get_cities',
        target: function target() {
          return sCity;
        }
      },
      placeholder: wc_ukr_shipping_globals.i10n.placeholder_area
    }), $);
    bCity = new WCUSSelect(document.getElementById('wcus_np_billing_city'), utils.mergeSelectParams({
      mirror: function mirror() {
        return sCity;
      },
      ajax: {
        action: 'wcus_api_v2_get_warehouses',
        target: function target() {
          return bWarehouse;
        }
      },
      events: {
        beforeRequest: function beforeRequest(select) {
          utils.setLoadingState();
          calculateShipping();
        },
        response: function response(select, data) {
          utils.unsetLoadingState();
        }
      },
      placeholder: wc_ukr_shipping_globals.i10n.placeholder_city
    }), $);
    sCity = new WCUSSelect(document.getElementById('wcus_np_shipping_city'), utils.mergeSelectParams({
      mirror: function mirror() {
        return bCity;
      },
      ajax: {
        action: 'wcus_api_v2_get_warehouses',
        target: function target() {
          return sWarehouse;
        }
      },
      events: {
        beforeRequest: function beforeRequest(select) {
          utils.setLoadingState();
          calculateShipping();
        },
        response: function response(select, data) {
          utils.unsetLoadingState();
        }
      },
      placeholder: wc_ukr_shipping_globals.i10n.placeholder_city
    }), $);
    bWarehouse = new WCUSSelect(document.getElementById('wcus_np_billing_warehouse'), utils.mergeSelectParams({
      mirror: function mirror() {
        return sWarehouse;
      },
      placeholder: wc_ukr_shipping_globals.i10n.placeholder_warehouse
    }), $);
    sWarehouse = new WCUSSelect(document.getElementById('wcus_np_shipping_warehouse'), utils.mergeSelectParams({
      mirror: function mirror() {
        return bWarehouse;
      },
      placeholder: wc_ukr_shipping_globals.i10n.placeholder_warehouse
    }), $);
    $('body').on('change', 'input[name="payment_method"]', function () {
      calculateShipping();
    });
    initialize();
  });
})(jQuery);

/***/ })
/******/ ]);