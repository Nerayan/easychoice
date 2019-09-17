<div class="wcus-top-links">
  <a target="_blank" href="https://kirillbdev.pro/plugins/wc-ukr-shipping/?ref=plugin" class="wcus-btn wcus-btn--site wcus-btn--md wcus-top-links__link">
    <?= wc_ukr_shipping_import_svg('home.svg'); ?>
    Наш сайт
  </a>
</div>
<div id="wc-ukr-shipping-settings" class="wcus-settings">
  <div class="wcus-settings__header">
    <h1 class="wcus-settings__title">Настройки - WC Ukr Shipping</h1>
    <button type="submit" form="wc-ukr-shipping-settings-form" class="wcus-settings__submit wcus-btn wcus-btn--primary wcus-btn--md">Сохранить</button>
    <div id="wcus-settings-success-msg" class="wcus-settings__success wcus-message wcus-message--success">
      <?= wc_ukr_shipping_import_svg('success.svg'); ?>
      Настройки успешно сохранены.
    </div>
  </div>
  <div class="wcus-settings__content">
    <form id="wc-ukr-shipping-settings-form" action="/" method="POST">
      <?php wp_nonce_field('wp_rest'); ?>

      <div class="wcus-form-group">
        <label for="wc_ukr_shipping_np_api_key">API ключ</label>
        <input type="text" id="wc_ukr_shipping_np_api_key"
               name="wc_ukr_shipping[np_api_key]"
               class="wcus-form-control"
               value="<?= get_option('wc_ukr_shipping_np_api_key', ''); ?>">
      </div>

      <div class="wcus-form-group">
        <label for="wc_ukr_shipping_np_price">Фиксированная цена за доставку</label>
        <input type="number" id="wc_ukr_shipping_np_price"
               name="wc_ukr_shipping[np_price]"
               class="wcus-form-control"
               min="0"
               step="0.000001"
               value="<?= get_option('wc_ukr_shipping_np_price', 0); ?>">
      </div>

      <div class="wcus-form-group">
        <label for="wc_ukr_shipping_np_lang">Язык отображения городов и отделений</label>
        <select id="wc_ukr_shipping_np_lang"
               name="wc_ukr_shipping[np_lang]"
               class="wcus-form-control">
          <option value="ru" <?= get_option('wc_ukr_shipping_np_lang', 'uk') === 'ru' ? 'selected' : ''; ?>>Русский</option>
          <option value="uk" <?= get_option('wc_ukr_shipping_np_lang', 'uk') === 'uk' ? 'selected' : ''; ?>>Українська</option>
        </select>
      </div>

      <div class="wcus-form-group">
        <label for="wc_ukr_shipping_spinner_color">Цвет спинера во front-end</label>
        <input name="wc_ukr_shipping[spinner_color]" id="wc_ukr_shipping_spinner_color" type="text" value="<?= get_option('wc_ukr_shipping_spinner_color', '#5867dd'); ?>" />
      </div>

      <div class="wcus-form-group">
        <label for="wc_ukr_shipping_np_method_title">Заголовок во front-end</label>
        <input type="text" id="wc_ukr_shipping_np_method_title"
               name="wc_ukr_shipping[np_method_title]"
               class="wcus-form-control"
               value="<?= get_option('wc_ukr_shipping_np_method_title', 'Доставка службой Новая почта'); ?>">
      </div>

      <div class="wcus-form-group">
        <label class="wcus-checkbox">
          <input type="checkbox" name="wc_ukr_shipping[address_shipping]" value="1" <?= (int)get_option('wc_ukr_shipping_address_shipping', 1) === 1 ? 'checked' : ''; ?>>
          <span class="wcus-checkbox__title">Включить блок Адресной доставки</span>
        </label>
      </div>

      <div class="wcus-form-group">
        <label for="wc_ukr_shipping_np_address_title">Заголовок для адресной доставки</label>
        <input type="text" id="wc_ukr_shipping_np_address_title"
               name="wc_ukr_shipping[np_address_title]"
               class="wcus-form-control"
               value="<?= get_option('wc_ukr_shipping_np_address_title', 'Нужна адресная доставка'); ?>">
      </div>

      <div class="wcus-form-group">
        <label class="wcus-checkbox">
          <input type="checkbox" name="wc_ukr_shipping[send_statistic]" value="1" <?= (int)get_option('wc_ukr_shipping_send_statistic', 0) === 1 ? 'checked' : ''; ?>>
          <span class="wcus-checkbox__title">Разрешить отправлять анонимную статистику</span>
        </label>
        <div class="wcus-form-group__tooltip">
          <strong>Помогите нам сделать плагин лучше!</strong> Разрешите отправлять анонимную статистику о вашем сайте, для того, чтобы лучше понимать использование тех или иных функций плагина. <strong>Ваши личные данные никоим образом не попадут в данную статистику.</strong>
        </div>
      </div>

      <div class="wcus-sub-title">Информация об отделениях Новой Почты:</div>
      <div class="wcus-settings__db">
        <button class="wcus-settings__update-data wcus-btn wcus-btn--outline wcus-btn--sm">
          <?= wc_ukr_shipping_import_svg('refresh.svg'); ?>
          Обновить отделения
        </button>
      </div>
      <div id="wcus-updating-data-state" class="wcus-settings__db"></div>
    </form>
  </div>
</div>

<script>
  (function($) {
    'use strict';

    let restNonce = $('#_wpnonce').val(),
        apiKey = '<?= get_option('wc_ukr_shipping_np_api_key', ''); ?>',
        formBlocked = false;

    $('#wc-ukr-shipping-settings-form').on('submit', function (event) {
      event.preventDefault();

      if (formBlocked) {
        return;
      }

      formBlocked = true;

      $('#wc-ukr-shipping-settings').addClass('wcus-state-loading');

      WCUkrShippingRouter.saveOptions({
        data: $(this).serialize(),
        success: function (json) {
          if ( ! json.success) {
            let errors = json.data.errors;

            for (let key in errors) {
              if (errors.hasOwnProperty(key)) {
                $('#' + key).addClass('wcus-form-control--invalid');
                $('#' + key).after('<div class="wcus-form-group__error">' + errors[key] + '</div>');
              }
            }
          }
          else {
            apiKey = json.data.api_key;
            $('#wcus-settings-success-msg').addClass('active');

            setTimeout(() => {
              $('#wcus-settings-success-msg').removeClass('active');
            }, 2500);
          }
        },
        complete: function() {
          formBlocked = false;
          $('#wc-ukr-shipping-settings').removeClass('wcus-state-loading');
        }
      });

    });

    let loadAreas = function ($log, success, error) {
      formBlocked = true;
      $log.html('<div>Начинаю загрузку географических областей...</div>');

      WCUkrShippingRouter.loadAreas({
        data: { _wpnonce: restNonce },
        success: function (json) {
          formBlocked = false;

          if (json.success) {
            $log.html($log.html() + '<div>Загрузка географических областей успешно завершена!</div>');
            success();
          }
          else {
            error(json.data.errors);
          }
        }
      });
    };

    let loadCities = function ($log, success, error, page) {
      if (typeof page === 'undefined') {
        page = 1;
      }

      if (page === 1) {
        $log.html($log.html() + '<div>Начинаю загрузку городов...</div>');
      }

      WCUkrShippingRouter.loadCities({
        data: {
          _wpnonce: restNonce,
          page: page
        },
        success: function (json) {
          if (json.success) {
            if (json.data.loaded === true) {
              $log.html($log.html() + '<div>Загрузка городов успешно завершена!</div>');
              success();
            }
            else {
              loadCities($log, success, error, page + 1);
            }
          }
          else {
            error(json.data.errors);
          }
        }
      });
    };

    let loadWarehouses = function ($log, success, error, page) {
      if (typeof page === 'undefined') {
        page = 1;
      }

      if (page === 1) {
        $log.html($log.html() + '<div>Начинаю загрузку отделений...</div>');
      }

      WCUkrShippingRouter.loadWarehouses({
        data: {
          _wpnonce: restNonce,
          page: page
        },
        success: function (json) {
          if (json.success) {
            if (json.data.loaded === true) {
              $log.html($log.html() + '<div>Загрузка городов успешно завершена!</div>');
              success();
            }
            else {
              loadWarehouses($log, success, error, page + 1);
            }
          }
          else {
            error(json.data.errors);
          }
        }
      });
    };

    $('.wcus-settings__update-data').on('click', function (event) {
      event.preventDefault();

      if (apiKey === '') {
        alert('Укажите API ключ и сохраните настройки.');

        return;
      }

      if (formBlocked) {
        return;
      }

      formBlocked = true;

      let $btn = $(this),
          btnHtml = $btn.html(),
          $log = $('<div/>');

      $log.addClass('wcus-message wcus-message--log');
      $btn.html('<span class="wcus-btn-loader"></span>');
      $('#wcus-updating-data-state').html('');
      $log.appendTo('#wcus-updating-data-state');

      let errorCb = function (errors) {
        formBlocked = false;
        $btn.html(btnHtml);
        console.log(errors);
      };

      loadAreas($log, function () {

        loadCities($log, function () {

          loadWarehouses($log, function () {

            formBlocked = false;
            $btn.html(btnHtml);
            $log.removeClass('wcus-message--log');
            $log.html('Данные успешно обновлены.');
            $log.addClass('wcus-message--ok');

          }, errorCb);

        }, errorCb);

      }, errorCb);
    });

    $(function() {
      $('#wc_ukr_shipping_spinner_color').wpColorPicker();
    });
  })(jQuery);
</script>
