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

// CONCATENATED MODULE: ./src/js/modules/router.js
/* harmony default export */ var router = ({
  init: function init($) {
    this._$ = $;
    this._successHandler = null;
    this._errorHandler = null;
    this._responseHandler = null;
  },
  post: function post(action, data) {
    var that = this,
        $ = that._$;
    $.ajax({
      method: 'POST',
      url: wc_ukr_shipping_globals.ajaxUrl,
      data: Object.assign({
        action: action
      }, data),
      dataType: 'json',
      success: function success(json) {
        if ('function' === typeof that._successHandler) {
          that._successHandler(json);
        }
      },
      error: function error(jqXHR, textStatus, errorThrown) {
        if ('function' === typeof that._errorHandler) {
          that._errorHandler(jqXHR, textStatus, errorThrown);
        }
      },
      complete: function complete() {
        if ('function' === typeof that._responseHandler) {
          that._responseHandler();
        }
      }
    });
    return this;
  },
  success: function success(callback) {
    this._successHandler = callback;
    return this;
  },
  error: function error(callback) {
    this._errorHandler = callback;
    return this;
  },
  response: function response(callback) {
    this._responseHandler = callback;
    return this;
  }
});
// CONCATENATED MODULE: ./src/js/settings/utils.js
/* harmony default export */ var utils = ({
  init: function init($) {
    this._$ = $;
  },
  setLoadingState: function setLoadingState() {
    this._$('#wc-ukr-shipping-settings').addClass('wcus-state-loading');
  },
  unsetLoadingState: function unsetLoadingState() {
    this._$('#wc-ukr-shipping-settings').removeClass('wcus-state-loading');
  }
});
// CONCATENATED MODULE: ./src/js/settings/store.js
/* harmony default export */ var store = ({
  init: function init(apiKey) {
    this.locked = false;
    this.apiKey = apiKey;
  },
  lock: function lock() {
    this._locked = true;
  },
  unlock: function unlock() {
    this._locked = false;
  },
  setApiKey: function setApiKey(key) {
    this.apiKey = key;
  }
});
// CONCATENATED MODULE: ./src/js/settings/loader.js


/* harmony default export */ var settings_loader = ({
  /**
   * public
   */
  init: function init($) {
    this._$ = $;
    this.loaders = [];
    this.$logger = null;
    this.$btn = $('.wcus-settings__update-data');
    this.btnHtml = '';
    this.initEvents();
  },
  setNext: function setNext(loader) {
    this.loaders.push(loader);
    return this;
  },
  log: function log(msg) {
    this.$logger.html(this.$logger.html() + '<div>' + msg + '</div>');
  },
  loadNext: function loadNext() {
    if (!this.loaders.length) {
      this.success();
    }

    var loader = this.loaders.shift();

    if (loader) {
      loader.call(this);
    }
  },
  loadAreas: function loadAreas() {
    var loader = this;
    loader.log('Начинаю загрузку географических областей...');
    router.post('wc_ukr_shipping_load_areas', {
      _token: wc_ukr_shipping_globals.nonce
    }).success(function (json) {
      if (json.success) {
        loader.log('Загрузка географических областей успешно завершена!');
        loader.loadNext();
      } else {
        loader.error(json.data);
      }
    });
  },
  loadCities: function loadCities(page) {
    var loader = this;

    if ('undefined' === typeof page) {
      page = 1;
    }

    if (1 === page) {
      loader.log('Начинаю загрузку городов...');
    }

    router.post('wc_ukr_shipping_load_cities', {
      _token: wc_ukr_shipping_globals.nonce,
      page: page
    }).success(function (json) {
      if (json.success) {
        if (json.data.loaded === true) {
          loader.log('Загрузка городов успешно завершена!');
          loader.loadNext();
        } else {
          loader.loadCities(page + 1);
        }
      }
    });
  },
  loadWarehouses: function loadWarehouses(page) {
    var loader = this;

    if (typeof page === 'undefined') {
      page = 1;
    }

    if (page === 1) {
      loader.log('Начинаю загрузку отделений...');
    }

    router.post('wc_ukr_shipping_load_warehouses', {
      _token: wc_ukr_shipping_globals.nonce,
      page: page
    }).success(function (json) {
      if (json.success) {
        if (json.data.loaded === true) {
          loader.log('Загрузка городов успешно завершена!');
          loader.loadNext();
        } else {
          loader.loadWarehouses(page + 1);
        }
      }
    });
  },
  error: function error(data) {
    store.unlock();
    this.$btn.html(this.btnHtml);
    this.$logger.removeClass('wcus-message--log');
    this.$logger.addClass('wcus-message--error');

    if (data.exception) {
      this.$logger.html("\n        <div><strong>API service exception</strong></div>\n        <div>".concat(data.exception, "</div>\n      "));
    } else {
      var html = '';
      data.errors.forEach(function (error) {
        html += "<div>".concat(error, "</div>");
      });
      this.$logger.html("\n        <div><strong>API error</strong></div>\n        <div>".concat(html, "</div>\n      "));
    }
  },
  success: function success() {
    store.unlock();
    this.$btn.html(this.btnHtml);
    this.$logger.removeClass('wcus-message--log');
    this.$logger.html('Данные успешно обновлены.');
    this.$logger.addClass('wcus-message--ok');
  },
  initEvents: function initEvents() {
    var that = this;
    that.$btn.on('click', function (event) {
      event.preventDefault();

      if (store.locked) {
        return;
      }

      store.lock();
      that.btnHtml = that.$btn.html();
      that.$btn.html('<span class="wcus-btn-loader"></span>');

      that._$('#wcus-updating-data-state').html('');

      that.$logger = that._$('<div/>');
      that.$logger.addClass('wcus-message wcus-message--log');
      that.$logger.appendTo('#wcus-updating-data-state');
      that.loaders = [];
      that.setNext(that.loadAreas).setNext(that.loadCities).setNext(that.loadWarehouses);
      that.loadNext();
    });
  }
});
// CONCATENATED MODULE: ./src/js/settings/entry.js





(function ($) {
  'use strict';

  store.init($('#wc_ukr_shipping_np_api_key').val());
  router.init($);
  utils.init($);
  settings_loader.init($);
  $('#wc-ukr-shipping-settings-form').on('submit', function (event) {
    event.preventDefault();

    if (store.locked) {
      return;
    }

    store.lock();
    utils.setLoadingState();
    router.post('wc_ukr_shipping_save_settings', {
      body: $(this).serialize()
    }).success(function (json) {
      if (!json.success) {
        var errors = json.data.errors;

        for (var key in errors) {
          if (errors.hasOwnProperty(key)) {
            $('#' + key).addClass('wcus-form-control--invalid');
            $('#' + key).after('<div class="wcus-form-group__error">' + errors[key] + '</div>');
          }
        }
      } else {
        store.setApiKey(json.data.api_key);
        $('#wcus-settings-success-msg').addClass('active');
        setTimeout(function () {
          $('#wcus-settings-success-msg').removeClass('active');
        }, 2500);
      }
    }).error(function (jqXHR, textStatus, errorThrown) {
      console.error("WCUS error: ".concat(jqXHR.status, " ").concat(jqXHR.statusText));
    }).response(function () {
      store.unlock();
      utils.unsetLoadingState();
    });
  });
  $(function () {
    $('#wc_ukr_shipping_spinner_color').wpColorPicker();
  });
})(jQuery);

/***/ })
/******/ ]);