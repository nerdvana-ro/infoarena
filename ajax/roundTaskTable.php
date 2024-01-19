<?php

function ajax_run(): void {
  $params = new RoundTaskTableParams();
  $params->populateFromRequest();

  $response = [];

  $round = Round::get_by_id($params->roundId);
  if (!$round) {
    $response['error'] = 'Valoare incorectă pentru parametrul „roundId”.';
  } else {
    $table = new RoundTaskTable($params);
    $table->run();
    $response['html'] = $table->getHtml();
  }

  header('Content-Type: application/json');
  print json_encode($response);
}
