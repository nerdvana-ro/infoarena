<?php

require_once __DIR__ . '/../../common/round.php';
require_once __DIR__ . '/../format/form.php';
require_once __DIR__ . '/round_edit_header.php';
require_once __DIR__ . '/../macros/macro_tasks.php';
require_once __DIR__ . '/header.php';

echo round_edit_tabs($view['round_id'], 'round-edit-task-order');
?>

<script>
  function do_post() {
    var numbers = $('table.tasks tbody tr td.number')
      .map(function() {
        return $(this).text().trim();
      })
      .get()
      .join(';');

    if (numbers) {
      var formElem = document.task_order_form;
      formElem.task_order.value = numbers;
      formElem.submit();
    } else {
      return false;
    }
  }
</script>

<h1>Editare ordine probleme <?= format_link(url_textblock($round['page_name']), $round['title']) ?></h1>

<form name="task_order_form" action="<?= html_escape(url_round_edit_task_order($view['round_id']))?>" method="post">

<input type="hidden" name="task_order">

<?php
$args = array(
    'round_id' => $view['round_id'],
    'css_class' => 'dragndrop',
    'show_numbers' => true,
    'show_ratings' => true,
    'show_perspective_form' => false,
);
echo macro_tasks($args);
?>

<div class="submit">
    <ul class="form">
        <li id="field_submit">
            <input type="button" value="Salvează modificări"
             onclick="do_post()" class="button important">
        </li>
    </ul>
</div>

</form>

<?php include('footer.php'); ?>
