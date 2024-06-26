<?php

require_once(Config::ROOT . "common/db/task.php");

// Display solution submission form for a given task
//
// Arguments:
//      task_id (required)            Task identifier (without prefix)
//
// Examples:
//      TaskSubmit(task_id="adunare")
function macro_tasksubmit($args) {
  $task_id = getattr($args, 'task_id');

  // validate arguments
  if (!is_task_id($task_id)) {
    return macro_error("Expecting parameter `task_id`");
  }

  // fetch & validate task
  $task = Task::get_by_id($task_id);
  if (!$task) {
    return macro_error("Invalid task identifier");
  }

  if (Identity::isAnonymous()) {
    $url = html_escape(url_login());
    return macro_message("Trebuie să te autentifici pentru a trimite soluții. <a href=\"{$url}\">Click aici</a>", true);
  }

  // Permission check. Should never fail right now.
  if (!$task->canSubmit()) {
    return macro_message("Nu se (mai) pot trimite soluții la această problemă.", true);
  }

  // Display form
  ob_start();
  ?>

  <a href="<?= html_escape(url_monitor()."?task=".$task->id."&user=".Identity::getUsername()) ?>">Vezi soluțiile trimise de tine</a>
<?php
  require_once(Config::ROOT . "www/views/submit_form.php");
  display_submit_form(true, $task_id);

  $buffer = ob_get_contents();
  ob_end_clean();

  // done
  return $buffer;
}

?>
