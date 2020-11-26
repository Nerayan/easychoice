import $ from "jquery";
import toastr from "toastr";
import ClipboardJS from "clipboard";
import "jquery-ui-bundle";
import "select2";

toastr.options = {
  positionClass: 'toast-bottom-right',
  timeOut: 3000
};

function resolveLogsCountAndSize()
{
  var ajaxUrl = typeof ajaxurl !== 'undefined' ? ajaxurl : '/wp-admin/admin-ajax.php';
  var $spinner = $('[data-ui-component="itglx-wc1c-ajax-clear-logs"] span[role="status"]');
  var $resultContainer = $('[data-ui-component="itglx-wc1c-logs-count-and-size-text"]');

  $spinner.removeClass('d-none');

  $.ajax({
    url: ajaxUrl,
    method: "POST",
    data: {
      action: 'itglxWc1cLogsCountAndSize'
    },
    success: function (response) {
      $spinner.addClass('d-none');
      $resultContainer.html(response.data.message);
    }
  });
}

function resolveTempCountAndSize()
{
  var ajaxUrl = typeof ajaxurl !== 'undefined' ? ajaxurl : '/wp-admin/admin-ajax.php';
  var $spinner = $('[data-ui-component="itglx-wc1c-ajax-clear-temp"] span[role="status"]');
  var $resultContainer = $('[data-ui-component="itglx-wc1c-temp-count-and-size-text"]');

  $spinner.removeClass('d-none');

  $.ajax({
    url: ajaxUrl,
    method: "POST",
    data: {
      action: 'itglxWc1cTempCountAndSize'
    },
    success: function (response) {
      $spinner.addClass('d-none');
      $resultContainer.html(response.data.message);
    }
  });
}

function resolveRequestResponseData()
{
  var ajaxUrl = typeof ajaxurl !== 'undefined' ? ajaxurl : '/wp-admin/admin-ajax.php';
  var $resultContainer = $('[data-ui-component="itglx-wc1c-ajax-last-request-response-container"]');
  var $spinner = $resultContainer.find('span[role="status"]');

  $spinner.removeClass('d-none');

  $.ajax({
    url: ajaxUrl,
    method: "POST",
    data: {
      action: 'ItglxWc1cLastRequestResponse'
    },
    success: function (response) {
      $resultContainer.html(response);
    }
  });
}

$(document).ready(function () {
  $('[data-ui-component="tabs"]').tabs();
  $('[data-ui-component="select2"]').select2();

  window.itglxWcClipboard = new ClipboardJS('.copy-to-clipboard');

  window.itglxWcClipboard.on('success', function(e) {
    toastr.success($(e.trigger).data('message') + ': ' + e.text);
  });

  resolveLogsCountAndSize();
  resolveTempCountAndSize();
  resolveRequestResponseData();

  setInterval(function () {
    resolveRequestResponseData();
  }, 20000);
});

$(document).on('click', '[data-ui-component="itglx-wc1c-ajax-clear-logs"]', function () {
  var ajaxUrl = typeof ajaxurl !== 'undefined' ? ajaxurl : '/wp-admin/admin-ajax.php';
  var $button = $(this);
  var $loader = $button.find('[role="status"]');

  $loader.removeClass('d-none');

  $.ajax({
    url: ajaxUrl,
    method: "POST",
    data: {
      action: 'itglxWc1cClearLogs'
    },
    success: function (response) {
      $loader.addClass('d-none');

      if (response.success) {
        toastr.success(response.data.message);
        resolveLogsCountAndSize();
      } else {
        toastr.error(response.data.message);
      }
    }
  });

  return false;
});

$(document).on('click', '[data-ui-component="itglx-wc1c-ajax-clear-temp"]', function () {
  var ajaxUrl = typeof ajaxurl !== 'undefined' ? ajaxurl : '/wp-admin/admin-ajax.php';
  var $button = $(this);
  var $loader = $button.find('[role="status"]');

  $loader.removeClass('d-none');

  $.ajax({
    url: ajaxUrl,
    method: "POST",
    data: {
      action: 'itglxWc1cClearTemp'
    },
    success: function (response) {
      $loader.addClass('d-none');

      if (response.success) {
        toastr.success(response.data.message);
        resolveTempCountAndSize();
      } else {
        toastr.error(response.data.message);
      }
    }
  });

  return false;
});
