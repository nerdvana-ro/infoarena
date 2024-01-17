<?php

function get_bool_arg(array $args, string $key, bool $default = false): bool {
  return Util::toBool($args[$key] ?? $default);
}

function macro_tasks($args) {
  $roundId = $args['round_id'] ?? '';
  $round = Round::get_by_id($roundId);
  if (!$round) {
    return macro_error("Valoare incorectă pentru round_id: „{$roundId}”");
  }
  if (!Identity::mayViewRoundTasks($round->as_array())) {
    // No error message here. A contestant can simply visit the round page
    // before the round starts.
    return '';
  }

  $showFilters = $round->isArchive() &&
    get_bool_arg($args, 'show_perspective_form', true);
  $showNumbers = get_bool_arg($args, 'show_numbers');
  $showRatings = get_bool_arg($args, 'show_ratings');
  $showSolvedBy = get_bool_arg($args, 'show_solved_by');
  $showScores = get_bool_arg($args, 'score');
  $showPagination = get_bool_arg($args, 'pagination');
  $cssClass = $args['css_class'] ?? '';

  if ($showScores) {
    $asUsername = Request::get('as_username');
    $asUser = User::get_by_username($asUsername);
    $perspectiveUserId = $asUser->id ?? Identity::getId();
  } else {
    $asUsername = '';
    $asUser = null;
    $perspectiveUserId = 0;
  }

  $attempted = Request::get('attempted', 0);

  $params = new RoundTaskTableParams();
  $params->roundId = $roundId;
  $params->userId = $perspectiveUserId;
  $params->attempted = $attempted;
  $params->cssClass = $cssClass;
  $params->showNumbers = $showNumbers;
  $params->showRatings = $showRatings;
  $params->showSolvedBy = $showSolvedBy;
  $params->showScores = $showScores;
  $params->showPagination = $showPagination;
  $params->pageNo = 1;
  $params->pageSize = Config::PAGE_SIZE;
  $params->sortField = 'number';
  $params->sortAsc = true;

  $table = new RoundTaskTable($params);
  $table->run();
  $taskTableHtml = $table->getHtml();

  Smart::assign([
    'asUser' => $asUser,
    'asUsername' => $asUsername,
    'showFilters' => $showFilters,
    'taskTableHtml' => $taskTableHtml,
  ]);
  return Smart::fetch('macro/tasks.tpl');
}
