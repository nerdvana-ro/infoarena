/**
 * sitewide JS
 * (c) infoarena
 */

// Disable the $$...$$ notation for block math.
window.MathJax = {
  tex: {
    displayMath: [
      ['\\[', '\\]']
    ],
  }
};

$(function() {

  init();

  function init() {
    // fade away flash messages except for errors
    setTimeout(function() {
      $('.flash:not(.flash-error)').hide('slow');
    }, 10000);

    // page log (used in development mode)
    var log = $('#log');
    if (log.length) {
      // scroll down
      log.scrollTop(log.prop('scrollHeight') - log.height());

      // maximize on click
      var callback = function(event) {
        log.height(log.prop('scrollHeight'));
        log.prop('id', 'log_active');
      }
      log.one('click', callback);
    }

    $('select.autosubmit').on('change', autosubmitSelect);
    $(document).on('change', '.page-size-select select', pageSizeChange);
  }

  function autosubmitSelect() {
    $(this).closest('form').submit();
  }

  function pageSizeChange() {
    var formId = $(this).data('formId');
    var form = $('#' + formId);
    var size = $(this).val();
    form.find('input[name="pag"]').val(size);
    form.submit();
  }

});
