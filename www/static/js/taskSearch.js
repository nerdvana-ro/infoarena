$(function() {

  var form = $('#tag-filters');

  function tagClick() {
    console.log($(this).attr('href'));
    return true;
  }

  function init() {
    $(document).on('click', '#task-filter-menu a', tagClick);
  }

  init();
});
