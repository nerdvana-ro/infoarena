$(function() {

  function getColumns(table) {
    results = [];
    table.find('thead tr th').each(function() {
      results.push($(this).data('field'));
    });
    return results;
  }

  function makeAjaxRequest(element) {
    var div = element.closest('.ajax-table');
    var table = div.find('table');
    var url = table.data('url');
    var columns = getColumns(table);
    var pageSize = div.find('select').val() ?? 0;

    var data = {
      attempted: div.data('attempted'),
      columns: columns,
      pageSize: pageSize,
      pageNo: table.data('pageNo'),
      roundId: div.data('roundId'),
      showPagination: table.data('showPagination'),
      sortField: table.data('sortField'),
      sortAsc: table.data('sortAsc'),
      userId: div.data('userId'),
    };

    $.ajax({
      url: url,
      data: data,
    }).done(function(data) {
      div.html(data.html);
    });
  }

  function headerClick() {
    if ('disabledSort' in $(this).data()) {
      return;
    }

    var table = $(this).closest('table');
    var oldSortField = table.data('sortField');
    var oldSortAsc = table.data('sortAsc');
    var newSortField = $(this).data('field');
    if (oldSortField == newSortField) {
      table.data('sortAsc', 1 - oldSortAsc);
    } else {
      table.data('sortField', newSortField);
      table.data('sortAsc', 1);
    }
    table.data('pageNo', 1);

    makeAjaxRequest($(this));
  }

  function pageClick() {
    var table = $(this).closest('.ajax-table').find('table');
    var page = $(this).data('dest');
    table.data('pageNo', page);

    makeAjaxRequest($(this));
    return false;
  }

  function selectChange() {
    var table = $(this).closest('.ajax-table').find('table');
    table.data('pageNo', 1);
    makeAjaxRequest($(this));
  }

  function autosubmitAttempted() {
    $(this).closest('form').submit();
  }

  function init() {
    $(document).on('click', '.ajax-table th', headerClick);
    $(document).on('click', '.ajax-table .pagination a', pageClick);
    $(document).on('change', '.ajax-table .controls select', selectChange);
    $('.task-filters select').on('change', autosubmitAttempted);
  }

  init();
});
