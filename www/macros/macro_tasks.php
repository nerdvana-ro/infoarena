<?php

function macro_tasks(array $args): string {
  $args = new MacroArgs($args);

  $roundId = $args->get('round_id');
  $round = Round::get_by_id($roundId);
  if (!$round) {
    return macro_error("Valoare incorectă pentru round_id: „{$roundId}”");
  }
  if (!Identity::mayViewRoundTasks($round->as_array())) {
    // No error message here. A contestant can simply visit the round page
    // before the round starts.
    return '';
  }

  $params = new RoundTaskTableParams();
  $params->roundId = $roundId;
  $params->showNumbers = $args->getBool('show_numbers');

  $params->cssClass = $args->get('css_class');
  $params->showRatings = $args->getBool('show_ratings');
  $params->showScores = $args->getBool('score');
  $params->showSolvedBy = $args->getBool('show_solved_by');
  $params->showPagination = $args->getBool('pagination');

  $showFilters = $round->isArchive() &&
    $args->getBool('show_perspective_form', true);

  if ($params->showScores) {
    $asUsername = Request::get('as_username');
    $asUser = User::get_by_username($asUsername);
    $params->userId = $asUser->id ?? Identity::getId();
  } else {
    $asUsername = '';
    $asUser = null;
    $params->userId = 0;
  }

  $table = new RoundTaskTable($params);
  $table->run();

  Smart::assign([
    'asUser' => $asUser,
    'asUsername' => $asUsername,
    'params' => $params,
    'showFilters' => $showFilters,
    'taskTable' => $table,
  ]);
  return Smart::fetch('macro/tasks.tpl');
}
