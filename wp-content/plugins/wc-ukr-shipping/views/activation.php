<!doctype html>
<html>
<head>
  <title>Активация - WooCommerce Ukraine Shipping</title>
  <meta charset="<?php bloginfo( 'charset' ); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php wp_head(); ?>
</head>
<body class="activation-wrap">
  <div class="activation-form">
    <div class="activation-form__logo-wrap">
      <div class="activation-form__logo-inner">
        <div class="activation-form__logo"></div>
      </div>
    </div>
    <div class="activation-form__content">
      <h1>Добро пожаловать</h1>
      <p>Спасибо, что выбрали данный плагин.</p>
      <p>Перед началом работы Вам следует загрузить актуальную информацию об областях, городах и отделениях Новой Почты. Сделайте же это, нажав на кнопку ниже!</p>
      <p><strong>Удачных продаж!</strong></p>
      <div id="api-status" class="activation-form__status"></div>
      <div class="activation-form__load-wrap">
        <a href="#" id="load-btn" class="activation-form__load-btn">Загрузить актуальные отделения</a>
      </div>
    </div>
  </div>
  <?php wp_footer(); ?>
  <script>
    'use strict';

    (function ($) {

      let loading = false,
        prevent = true,
        ajaxUrl = '<?= $ajax_url; ?>',
        $status = document.getElementById('api-status');

      document.getElementById('load-btn').onclick = function (event) {
        if (prevent) {
          event.preventDefault();
        }

        if (loading) {
          return;
        }

        loading = true;
        this.innerHTML = '<span class="activation-form__loader"></span>';
        loadAreas();
      };

      function loadAreas() {
        $status.style.padding = '30px 0 15px';
        $status.innerHTML += '<div>Начинаю загрузку географических областей...</div>';

        $.ajax({
          method: 'POST',
          url: ajaxUrl,
          data: { action: 'wc_ukr_shipping_np_load_areas' },
          dataType: 'json',
          success: function (json) {
            if (json.result) {
              $status.innerHTML += '<div>Загрузка географических областей успешно завершена!</div>';
              loadCities();
            }
          }
        });
      }

      function loadCities() {
        $status.innerHTML += '<div>Начинаю загрузку городов...</div>';

        $.ajax({
          method: 'POST',
          url: ajaxUrl,
          data: { action: 'wc_ukr_shipping_np_load_cities' },
          dataType: 'json',
          success: function (json) {
            if (json.result) {
              $status.innerHTML += '<div>Загрузка городов успешно завершена!</div>';
              loadWarehouses();
            }
          }
        });
      }

      function loadWarehouses(cityRef) {
        $status.innerHTML += '<div>Начинаю загрузку отделений...</div>';

        $.ajax({
          method: 'POST',
          url: ajaxUrl,
          data: { action: 'wc_ukr_shipping_np_load_warehouses' },
          dataType: 'json',
          success: function (json) {
            if (json.result) {
              $status.innerHTML += '<div>Загрузка отделений успешно завершена!</div>';

              let $loadBtn = document.getElementById('load-btn');

              $loadBtn.href = '<?= admin_url(); ?>';
              $loadBtn.innerHTML = 'Перейти в Админ-панель.';
              prevent = false;
            }
          }
        });
      }

    })(jQuery);
  </script>
</body>
</html>