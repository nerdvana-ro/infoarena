$(function() {

  var root = $('#page-table');
  var table = root.find('table');
  var form = $('#' + table.data('formId'));
  var showPagination = table.data('showPagination');
  var pageSizeSelect = root.find('.controls select');
  var pag = [];

  function buildPag() {
    pag.sortField = table.data('sortField');
    pag.sortAsc = table.data('sortAsc');
    pag.page = table.data('page');
    pag.pageSize = pageSizeSelect.val() ?? 0;
  }

  function pagToStr() {
    var dir = pag.sortAsc ? 'asc' : 'desc';
    if (showPagination) {
      return [pag.sortField, dir, pag.pageSize, pag.page].join('-');
    } else {
      return [pag.sortField, dir].join('-');
    }
  }

  function removeEmptyInputs() {
    form.find('input,select').each(function() {
      if ($(this).val() === '') {
        $(this).remove();
      }
    });
  }

  function submitForm(element) {
    form.append($('<input>', {
      type: 'hidden',
      name: 'pag',
      value: pagToStr(),
    }));

    removeEmptyInputs();
    form.submit();
  }

  function headerClick() {
    if ('disabledSort' in $(this).data()) {
      return;
    }

    var field = $(this).data('field');
    if (field == pag.sortField) {
      pag.sortAsc = !pag.sortAsc;
    } else {
      pag.sortField = field;
      pag.sortAsc = true;
    }
    pag.page = 1;

    submitForm();
  }

  function pageClick() {
    pag.page = $(this).data('dest');
    submitForm();
  }

  function selectChange() {
    pag.pageSize = pageSizeSelect.val() ?? 0;
    pag.page = 1;
    submitForm();
  }

  function attemptedChange() {
    pag.page = 1;
    submitForm();
  }

  function init() {
    buildPag();
    $(document).on('click', '#page-table th', headerClick);
    $(document).on('click', '#page-table .pagination a', pageClick);
    $(document).on('change', '#page-table .controls select', selectChange);
    $('#task-filters select').on('change', attemptedChange);
  }

  init();
});
