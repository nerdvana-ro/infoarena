<?php

require_once Config::ROOT . 'common/db/task_statistics.php';

function controller_statistici_problema() {
  $taskId = request('task');
  $task = Task::get_by_id($taskId);
  if (!$task) {
    FlashMessage::addError('Problema nu există.');
    Util::redirectToHome();
  }

  Identity::enforceViewTaskStats($task);

  $stats = TaskStats::loadForTask($task->id);

  RecentPage::addCurrentPage('Statisticile problemei ' . $task->id);
  Smart::assign([
    'averageWrongSubmissions' => task_statistics_get_average_wrong_submissions($task->id),
    'solvedPercentage' => task_statistics_get_solved_percentage($task->id),
    'stats' => $stats,
    'task' => $task,
  ]);

  if (Identity::isLoggedIn()) {
    $userId = Identity::getId();
    Smart::assign([
      'userWrongSubmissions' => task_statistics_get_user_wrong_submissions($task->id, $userId),
    ]);
  }

  Smart::display('task/stats.tpl');
}
