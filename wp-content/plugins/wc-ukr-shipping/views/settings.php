<div class="wcus-top-links">
  <a target="_blank" href="https://kirillbdev.pro/plugins/wc-ukr-shipping/?ref=plugin" class="wcus-btn wcus-btn--site wcus-btn--md wcus-top-links__link">
    <?= wc_ukr_shipping_import_svg('home.svg'); ?>
    Наш сайт
  </a>
  <a target="_blank" href="https://kirillbdev.pro/dokumentaciya-wc-nova-poshta-shipping/" class="wcus-btn wcus-btn--docs wcus-btn--md wcus-top-links__link">
    <?= wc_ukr_shipping_import_svg('docs.svg'); ?>
    Документация
  </a>
  <a target="_blank" href="https://kirillbdev.pro/wc-ukr-shipping-pro/?ref=plugin" class="wcus-btn wcus-btn--site wcus-btn--md wcus-top-links__link">
    <?= wc_ukr_shipping_import_svg('star.svg'); ?>
    Premium версия
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

      <ul class="wcus-tabs">
        <li data-pane="wcus-pane-general" class="active">Общие</li>
        <li data-pane="wcus-pane-translates">Переводы</li>
      </ul>
      <div id="wcus-pane-general" class="wcus-tab-pane active">
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
          <label for="wc_ukr_shipping_np_block_pos">Позиция блока на странице оформления заказа</label>
          <select id="wc_ukr_shipping_np_block_pos"
                  name="wc_ukr_shipping[np_block_pos]"
                  class="wcus-form-control">
            <option value="billing" <?= wc_ukr_shipping_get_option('wc_ukr_shipping_np_block_pos') === 'billing' ? 'selected' : ''; ?>>Основная секция</option>
            <option value="additional" <?= wc_ukr_shipping_get_option('wc_ukr_shipping_np_block_pos') === 'additional' ? 'selected' : ''; ?>>Дополнительная секция</option>
          </select>
          <div class="wcus-form-group__tooltip">Выбор опции "Дополнительная секция" расположит блок выбора отделения перед полем "Примечание к заказу"</div>
        </div>

        <div class="wcus-form-group">
          <label class="wcus-checkbox">
            <input type="checkbox" name="wc_ukr_shipping[address_shipping]" value="1" <?= (int)get_option('wc_ukr_shipping_address_shipping', 1) === 1 ? 'checked' : ''; ?>>
            <span class="wcus-checkbox__title">Включить блок Адресной доставки</span>
          </label>
        </div>

        <div class="wcus-form-group">
          <label for="wc_ukr_shipping_np_new_ui">Использовать новые ui компоненты</label>
          <select id="wc_ukr_shipping_np_new_ui"
                  name="wc_ukr_shipping[np_new_ui]"
                  class="wcus-form-control">
            <option value="1" <?= (int)wc_ukr_shipping_get_option('wc_ukr_shipping_np_new_ui') === 1 ? 'selected' : ''; ?>>Да</option>
            <option value="0" <?= (int)wc_ukr_shipping_get_option('wc_ukr_shipping_np_new_ui') === 0 ? 'selected' : ''; ?>>Нет</option>
          </select>
        </div>

        <div class="wcus-form-group">
          <label for="wc_ukr_shipping_np_save_warehouse">Сохранять последнее выбранное отделение пользователя</label>
          <select id="wc_ukr_shipping_np_save_warehouse"
                  name="wc_ukr_shipping[np_save_warehouse]"
                  class="wcus-form-control">
            <option value="1" <?= 1 === (int)wc_ukr_shipping_get_option('wc_ukr_shipping_np_save_warehouse') ? 'selected' : ''; ?>>Да</option>
            <option value="0" <?= 0 === (int)wc_ukr_shipping_get_option('wc_ukr_shipping_np_save_warehouse') ? 'selected' : ''; ?>>Нет</option>
          </select>
        </div>

        <div class="wcus-form-group">
          <label for="wc_ukr_shipping_np_translates_type">Загрузать переводы из</label>
          <select id="wc_ukr_shipping_np_translates_type"
                  name="wc_ukr_shipping[np_translates_type]"
                  class="wcus-form-control">
            <option value="<?= WCUS_TRANSLATE_TYPE_PLUGIN; ?>" <?= WCUS_TRANSLATE_TYPE_PLUGIN === (int)wc_ukr_shipping_get_option('wc_ukr_shipping_np_translates_type') ? 'selected' : ''; ?>>Настроек плагина</option>
            <option value="<?= WCUS_TRANSLATE_TYPE_MO_FILE; ?>" <?= WCUS_TRANSLATE_TYPE_MO_FILE === (int)wc_ukr_shipping_get_option('wc_ukr_shipping_np_translates_type') ? 'selected' : ''; ?>>mo файлов перевода</option>
          </select>
          <div class="wcus-form-group__tooltip">Если вы используете языковые плагины по типу WPML - выберите значение переводов из mo файлов. Так вы получите переводы на 3 языка по-умолчанию (русский, украинский, английский), а также возможность переводить строки по вашему желанию (используя например WPML String Translations).</div>
        </div>

        <div class="wcus-form-group">
          <label for="wc_ukr_shipping_spinner_color">Цвет спинера во front-end</label>
          <input name="wc_ukr_shipping[spinner_color]" id="wc_ukr_shipping_spinner_color" type="text" value="<?= get_option('wc_ukr_shipping_spinner_color', '#dddddd'); ?>" />
        </div>

        <div class="wcus-sub-title">Информация об отделениях Новой Почты:</div>
        <div class="wcus-settings__db">
          <button class="wcus-settings__update-data wcus-btn wcus-btn--outline wcus-btn--sm">
            <?= wc_ukr_shipping_import_svg('refresh.svg'); ?>
            Обновить отделения
          </button>
        </div>
        <div id="wcus-updating-data-state" class="wcus-settings__db"></div>
      </div>

      <div id="wcus-pane-translates" class="wcus-tab-pane">
        <div class="wcus-form-group">
          <label for="wc_ukr_shipping_np_method_title">Название метода доставки</label>
          <input type="text" id="wc_ukr_shipping_np_method_title"
                 name="wc_ukr_shipping[np_method_title]"
                 class="wcus-form-control"
                 value="<?= wc_ukr_shipping_get_option('wc_ukr_shipping_np_method_title'); ?>">
        </div>

        <div class="wcus-form-group">
          <label for="wc_ukr_shipping_np_block_title">Заголовок блока доставки</label>
          <input type="text" id="wc_ukr_shipping_np_block_title"
                 name="wc_ukr_shipping[np_block_title]"
                 class="wcus-form-control"
                 value="<?= wc_ukr_shipping_get_option('wc_ukr_shipping_np_block_title'); ?>">
        </div>

        <div class="wcus-form-group">
          <label for="wc_ukr_shipping_np_placeholder_area">Placeholder выбора области</label>
          <input type="text" id="wc_ukr_shipping_np_placeholder_area"
                 name="wc_ukr_shipping[np_placeholder_area]"
                 class="wcus-form-control"
                 value="<?= wc_ukr_shipping_get_option('wc_ukr_shipping_np_placeholder_area'); ?>">
        </div>

        <div class="wcus-form-group">
          <label for="wc_ukr_shipping_np_placeholder_city">Placeholder выбора города</label>
          <input type="text" id="wc_ukr_shipping_np_placeholder_city"
                 name="wc_ukr_shipping[np_placeholder_city]"
                 class="wcus-form-control"
                 value="<?= wc_ukr_shipping_get_option('wc_ukr_shipping_np_placeholder_city'); ?>">
        </div>

        <div class="wcus-form-group">
          <label for="wc_ukr_shipping_np_placeholder_warehouse">Placeholder выбора отделения</label>
          <input type="text" id="wc_ukr_shipping_np_placeholder_warehouse"
                 name="wc_ukr_shipping[np_placeholder_warehouse]"
                 class="wcus-form-control"
                 value="<?= wc_ukr_shipping_get_option('wc_ukr_shipping_np_placeholder_warehouse'); ?>">
        </div>

        <div class="wcus-form-group">
          <label for="wc_ukr_shipping_np_address_title">Заголовок для адресной доставки</label>
          <input type="text" id="wc_ukr_shipping_np_address_title"
                 name="wc_ukr_shipping[np_address_title]"
                 class="wcus-form-control"
                 value="<?= wc_ukr_shipping_get_option('wc_ukr_shipping_np_address_title'); ?>">
        </div>

        <div class="wcus-form-group">
          <label for="wc_ukr_shipping_np_address_placeholder">Placeholder адресной доставки</label>
          <input type="text" id="wc_ukr_shipping_np_address_placeholder"
                 name="wc_ukr_shipping[np_address_placeholder]"
                 class="wcus-form-control"
                 value="<?= wc_ukr_shipping_get_option('wc_ukr_shipping_np_address_placeholder'); ?>">
        </div>

        <div class="wcus-form-group">
          <label for="wc_ukr_shipping_np_not_found_text">Текст пустого результата поиска</label>
          <input type="text" id="wc_ukr_shipping_np_not_found_text"
                 name="wc_ukr_shipping[np_not_found_text]"
                 class="wcus-form-control"
                 value="<?= wc_ukr_shipping_get_option('wc_ukr_shipping_np_not_found_text'); ?>">
        </div>
      </div>

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
