<?php

function ajax_run(): void {
  $params = new RankingsParams();
  $params->roundDescription = Request::get('roundDescription');
  $params->detailRound = Request::get('detailRound');
  $params->detailTask = Request::get('detailTask');
  $params->showPagination = Request::getBool('showPagination');
  $params->pageNo = Request::get('pageNo');
  $params->pageSize = Request::get('pageSize');
  $params->sortField = Request::get('sortField');
  $params->sortAsc = Request::get('sortAsc');

  $rankings = new Rankings($params);
  $html = $rankings->getHtml();

  $response = [
    'html' => $html,
  ];

  header('Content-Type: application/json');
  print json_encode($response);
}
