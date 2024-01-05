<?php

function ajax_run(): void {
  $columns = Request::getArray('columns');

  $params = new TaskTableParams();
  $params->roundId = Request::get('roundId');
  $params->userId = Request::get('userId');
  $params->attempted = Request::get('attempted');
  $params->showNumbers = in_array('number', $columns);
  $params->showRatings = in_array('rating', $columns);
  $params->showSolvedBy = in_array('solved_by', $columns);
  $params->showScores = in_array('score', $columns);
  $params->showPagination = Request::getBool('showPagination');
  $params->pageNo = Request::get('pageNo');
  $params->pageSize = Request::get('pageSize');
  $params->sortField = Request::get('sortField');
  $params->sortAsc = Request::get('sortAsc');

  $response = [];

  $round = Round::get_by_id($params->roundId);
  if (!$round) {
    $response['error'] = 'Valoare incorectă pentru parametrul „roundId”.';
  } else {
    $table = new TaskTable($params);
    $table->run();
    $response['html'] = $table->getHtml();
  }

  header('Content-Type: application/json');
  print json_encode($response);
}
