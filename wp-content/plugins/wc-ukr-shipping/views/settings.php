<?php
  if ( ! defined('ABSPATH')) {
      exit;
  }
?>

<?= \kirillbdev\WCUSCore\Foundation\View::render('partial/top_links'); ?>

<div id="wc-ukr-shipping-settings" class="wcus-settings">
  <div class="wcus-settings__header">
    <h1 class="wcus-settings__title"><?= __('options_page_heading', WCUS_TRANSLATE_DOMAIN); ?></h1>
    <button type="submit" form="wc-ukr-shipping-settings-form" class="wcus-settings__submit wcus-btn wcus-btn--primary wcus-btn--md"><?= __('options_btn_save', WCUS_TRANSLATE_DOMAIN); ?></button>
    <div id="wcus-settings-success-msg" class="wcus-settings__success wcus-message wcus-message--success"></div>
  </div>
  <div class="wcus-settings__content">
    <form id="wc-ukr-shipping-settings-form" action="/" method="POST">

      <ul class="wcus-tabs">
        <li data-pane="wcus-pane-general" class="active"><?= __('options_tab_common', WCUS_TRANSLATE_DOMAIN); ?></li>
        <li data-pane="wcus-pane-translates"><?= __('options_tab_translates', WCUS_TRANSLATE_DOMAIN); ?></li>
      </ul>
      <div id="wcus-pane-general" class="wcus-tab-pane active">
        <div class="wcus-form-group">
          <label for="wc_ukr_shipping_np_api_key"><?= __('options_label_api_key', WCUS_TRANSLATE_DOMAIN); ?></label>
          <input type="text" id="wc_ukr_shipping_np_api_key"
                 name="wc_ukr_shipping[np_api_key]"
                 class="wcus-form-control"
                 value="<?= get_option('wc_ukr_shipping_np_api_key', ''); ?>">
        </div>

        <div class="wcus-form-group">
          <label for="wc_ukr_shipping_np_price"><?= __('options_label_fixed_price', WCUS_TRANSLATE_DOMAIN); ?></label>
          <input type="number" id="wc_ukr_shipping_np_price"
                 name="wc_ukr_shipping[np_price]"
                 class="wcus-form-control"
                 min="0"
                 step="0.000001"
                 value="<?= get_option('wc_ukr_shipping_np_price', 0); ?>">
        </div>

        <div class="wcus-form-group">
          <label for="wc_ukr_shipping_np_lang"><?= __('options_label_warehouse_lang', WCUS_TRANSLATE_DOMAIN); ?></label>
          <select id="wc_ukr_shipping_np_lang"
                  name="wc_ukr_shipping[np_lang]"
                  class="wcus-form-control">
            <option value="ru" <?= get_option('wc_ukr_shipping_np_lang', 'uk') === 'ru' ? 'selected' : ''; ?>><?= __('options_warehouse_lang_ru', WCUS_TRANSLATE_DOMAIN); ?></option>
            <option value="uk" <?= get_option('wc_ukr_shipping_np_lang', 'uk') === 'uk' ? 'selected' : ''; ?>><?= __('options_warehouse_lang_ua', WCUS_TRANSLATE_DOMAIN); ?></option>
          </select>
        </div>

        <div class="wcus-form-group">
          <label for="wc_ukr_shipping_np_block_pos"><?= __('options_label_field_position', WCUS_TRANSLATE_DOMAIN); ?></label>
          <select id="wc_ukr_shipping_np_block_pos"
                  name="wc_ukr_shipping[np_block_pos]"
                  class="wcus-form-control">
            <option value="billing" <?= wc_ukr_shipping_get_option('wc_ukr_shipping_np_block_pos') === 'billing' ? 'selected' : ''; ?>><?= __('options_field_position_main', WCUS_TRANSLATE_DOMAIN); ?></option>
            <option value="additional" <?= wc_ukr_shipping_get_option('wc_ukr_shipping_np_block_pos') === 'additional' ? 'selected' : ''; ?>><?= __('options_field_position_additional', WCUS_TRANSLATE_DOMAIN); ?></option>
          </select>
          <div class="wcus-form-group__tooltip"><?= __('options_tooltip_field_position', WCUS_TRANSLATE_DOMAIN); ?></div>
        </div>

        <div class="wcus-form-group wcus-form-group--horizontal">
          <label class="wcus-switcher">
            <input type="hidden" name="wc_ukr_shipping[address_shipping]" value="0">
            <input type="checkbox" name="wc_ukr_shipping[address_shipping]" value="1" <?= (int)get_option('wc_ukr_shipping_address_shipping', 1) === 1 ? 'checked' : ''; ?>>
            <span class="wcus-switcher__control"></span>
          </label>
          <div class="wcus-control-label"><?= __('options_label_address_shipping', WCUS_TRANSLATE_DOMAIN); ?></div>
        </div>

        <div class="wcus-form-group wcus-form-group--horizontal">
          <label class="wcus-switcher">
            <input type="hidden" name="wc_ukr_shipping[np_new_ui]" value="0">
            <input type="checkbox" name="wc_ukr_shipping[np_new_ui]" value="1" <?= (int)get_option('wc_ukr_shipping_np_new_ui', 1) === 1 ? 'checked' : ''; ?>>
            <span class="wcus-switcher__control"></span>
          </label>
          <div class="wcus-control-label"><?= __('options_label_new_ui', WCUS_TRANSLATE_DOMAIN); ?></div>
        </div>

        <div class="wcus-form-group wcus-form-group--horizontal">
          <label class="wcus-switcher">
            <input type="hidden" name="wcus[show_poshtomats]" value="0">
            <input type="checkbox" name="wcus[show_poshtomats]" value="1" <?= (int)get_option('wcus_show_poshtomats', 1) === 1 ? 'checked' : ''; ?>>
            <span class="wcus-switcher__control"></span>
          </label>
          <div class="wcus-control-label"><?= __('options_label_show_poshtomats', WCUS_TRANSLATE_DOMAIN); ?></div>
        </div>

        <div class="wcus-form-group">
          <label for="wc_ukr_shipping_np_translates_type"><?= __('options_label_translates_type', WCUS_TRANSLATE_DOMAIN); ?></label>
          <select id="wc_ukr_shipping_np_translates_type"
                  name="wc_ukr_shipping[np_translates_type]"
                  class="wcus-form-control">
            <option value="<?= WCUS_TRANSLATE_TYPE_PLUGIN; ?>" <?= WCUS_TRANSLATE_TYPE_PLUGIN === (int)wc_ukr_shipping_get_option('wc_ukr_shipping_np_translates_type') ? 'selected' : ''; ?>><?= __('options_translates_type_options', WCUS_TRANSLATE_DOMAIN); ?></option>
            <option value="<?= WCUS_TRANSLATE_TYPE_MO_FILE; ?>" <?= WCUS_TRANSLATE_TYPE_MO_FILE === (int)wc_ukr_shipping_get_option('wc_ukr_shipping_np_translates_type') ? 'selected' : ''; ?>><?= __('options_translates_type_mo_files', WCUS_TRANSLATE_DOMAIN); ?></option>
          </select>
          <div class="wcus-form-group__tooltip"><?= __('options_tooltip_translates_type', WCUS_TRANSLATE_DOMAIN); ?></div>
        </div>

        <div class="wcus-form-group">
          <label for="wc_ukr_shipping_spinner_color"><?= __('options_label_spinner_color', WCUS_TRANSLATE_DOMAIN); ?></label>
          <input name="wc_ukr_shipping[spinner_color]" id="wc_ukr_shipping_spinner_color" type="text" value="<?= get_option('wc_ukr_shipping_spinner_color', '#dddddd'); ?>" />
        </div>

        <div class="wcus-sub-title"><?= __('options_label_warehouse_update', WCUS_TRANSLATE_DOMAIN); ?></div>
        <div class="wcus-settings__db">
          <button class="wcus-settings__update-data wcus-btn wcus-btn--outline wcus-btn--sm">
            <?= wc_ukr_shipping_import_svg('refresh.svg'); ?>
            <?= __('options_btn_warehouse_update', WCUS_TRANSLATE_DOMAIN); ?>
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
