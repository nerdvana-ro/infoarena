<?php

function ajax_run(): void {
  $params = new SearchTaskTableParams();
  $params->populateFromRequest();

  $response = [];

  $table = new SearchTaskTable($params);
  $table->run();
  $response['html'] = $table->getHtml();

  header('Content-Type: application/json');
  print json_encode($response);
}
