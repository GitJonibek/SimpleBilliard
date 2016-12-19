;(function ($) {
  var self = this;
  var short_units = {};
  var $modal;
  var current_unit;
  var kr_id;
  var form;

  $.fn.modalEditKr = function (options) {
    init(options);
    return this;
  }
  function init(options) {
    kr_id = options.kr_id;
    //noinspection JSUnresolvedVariable
    var url = "/goals/ajax_get_edit_key_result_modal/key_result_id:" + kr_id;
    $modal = $('<div class="modal on fade" tabindex="-1"></div>');
    // $(form).unbind("submit");
    $.get(url, function (data) {
      $modal.append(data);
      modalFormCommonBindEvent($modal);
      form = $modal.find("#KrEditForm");
      // フォームサブミット
      $(form).on('submit', submit);


      $modal.on('shown.bs.modal', showInitModal);
      var $select_unit = $($modal.find('.js-select-value-unit'));
      short_units = $select_unit.data('short_units');
      current_unit = $select_unit.val();
      $modal.find('.js-display-short-unit').html(short_units[current_unit]);


      $modal.on('change', '.js-select-value-unit', changeUnit);
      $modal.on('hidden.bs.modal', function (e) {
        $(self).empty();
      });
      $modal.find('form').bootstrapValidator(getValidatorOptions);
      $modal.modal();
      $('body').addClass('modal-open');
    });

  }

  function getValidatorOptions() {
    return {
      live: 'enabled',

      fields: {
        "data[KeyResult][start_date]": {
          validators: {
            callback: {
              message: cake.message.notice.e,
              callback: function (value, validator) {
                var m = new moment(value, 'YYYY/MM/DD', true);
                return m.isBefore($('[name="data[KeyResult][end_date]"]').val());
              }
            },
            date: {
              format: 'YYYY/MM/DD',
              message: cake.message.validate.date_format
            }
          }
        },
        "data[KeyResult][end_date]": {
          validators: {
            callback: {
              message: cake.message.notice.f,
              callback: function (value, validator) {
                var m = new moment(value, 'YYYY/MM/DD', true);
                return m.isAfter($('[name="data[KeyResult][start_date]"]').val());
              }
            },
            date: {
              format: 'YYYY/MM/DD',
              message: cake.message.validate.date_format
            }
          }
        }
      }
    };
  }

  function showInitModal(e) {
    $modal.find('.input-group.date').datepicker({
      format: "yyyy/mm/dd",
      todayBtn: 'linked',
      language: "ja",
      autoclose: true,
      todayHighlight: true
      //endDate:"2015/11/30"
    })
      .on('hide', function (e) {
        $(form).bootstrapValidator('revalidateField', "data[KeyResult][start_date]");
        $(form).bootstrapValidator('revalidateField', "data[KeyResult][end_date]");
      });
  }

  function changeUnit(e) {
    var selected_unit = e.target.value;
    $modal.find('.js-display-short-unit').html(short_units[selected_unit]);

    /* 元の単位から変更した場合、注意メッセージ表示 */
    var warning_unit_change = $modal.find('.js-show-warning-unit-change').show();
    var start_value = $modal.find('.js-start-value');
    if (current_unit != selected_unit) {
      warning_unit_change.show();
      $(start_value).prop('disabled', false);
    } else {
      warning_unit_change.hide();
      $(start_value).prop('disabled', true);
    }
    /* 単位が「完了/未完了」の場合、開始/現在/目標値を非表示にする */
    var no_value = 2;
    var unit_values = $modal.find('.js-unit-values');
    if (selected_unit == no_value) {
      unit_values.hide();
    } else {
      unit_values.show();
    }
  }

  function submit(e) {
    e.preventDefault;
    e.stopImmediatePropagation();

    if (!confirm(cake.translation["Would you like to save?"])) {
      /* キャンセルの時の処理 */
      return false;
    }
    return false;
    var self = this;
    $(this).find(".changed").removeClass("changed");

    var form_data = $(this).serializeArray();

    $.ajax({
      url: "/api/v1/key_result/"+kr_id,
      type: 'PUT',
      data: form_data,
      success: function (data) {
        location.href = "/";
      },
      error: function (res, textStatus, errorThrown) {
      }
    });
  }
})(jQuery);
