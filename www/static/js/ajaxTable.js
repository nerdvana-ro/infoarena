$(function() {

  function pageSizeChange() {
    var formId = $(this).data('formId');
    var form = $('#' + formId);
    var size = $(this).val();
    form.find('input[name="pag"]').val(size);
    form.submit();
  }

  function init() {
    $(document).on('change', '.page-size-select select', pageSizeChange);
  }

  init();

});
