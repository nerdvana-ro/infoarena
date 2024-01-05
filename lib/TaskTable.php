<?php

/**
 * This class loads pages of tasks and returns HTML-formatted data. Used once
 * when loading the page, then via Ajax for subsequent page loads.
 **/

class TaskTable {

  private TaskTableParams $params;
  private ORMWrapper $query;

  function __construct(TaskTableParams $params) {
    $this->params = $params;

    $round = Round::get_by_id($params->roundId);

    $this->params->showScores &=
      Identity::isLoggedIn() &&
      Identity::mayViewRoundScores($round->as_array());

    $this->prepareQuery();
  }

  private function prepareQuery(): void {
    $joinClause = sprintf(
      "(s.task_id = t.id and s.round_id = rt.round_id and s.user_id = %s)",
      $this->params->userId);

    $this->query = Model::factory('Task')
      ->table_alias('t')
      ->select('t.*')
      ->select('s.score')
      ->select_expr('row_number() over(order by rt.order_id)', 'number')
      ->join('ia_round_task', [ 't.id', '=', 'rt.task_id' ], 'rt')
      ->raw_join('left join ia_score_user_round_task', $joinClause, 's')
      ->where('rt.round_id', $this->params->roundId);

    $this->addAttemptedFilter();
  }

  private function addAttemptedFilter(): void {
    $att = $this->params->attempted;
    if ($att == TaskTableParams::A_UNTOUCHED) {
      $this->query = $this->query->where_null('s.score');
    } else if ($att == TaskTableParams::A_ATTEMPTED) {
      $this->query = $this->query->where_lt('s.score', 100);
    } else if ($att == TaskTableParams::A_SOLVED) {
      $this->query = $this->query->where('s.score', 100);
    }
  }

  private function addSortOrder(): void {
    if ($this->params->sortField) {
      $field = $this->params->sortField;
      if ($this->params->sortAsc) {
        $this->query = $this->query->order_by_asc($field);
      } else {
        $this->query = $this->query->order_by_desc($field);
      }
    }
  }

  private function addPagination(): void {
    if ($this->params->showPagination) {
      $this->query = $this->query
        ->limit($this->params->pageSize)
        ->offset(($this->params->pageNo - 1) * $this->params->pageSize);
    }
  }

  private function count(): int {
    $clone = clone $this->query;
    return $clone->count();
  }

  function getPageCount(): int {
    if ($this->params->showPagination) {
      $c = $this->count();
      return ceil($c / $this->params->pageSize);
    } else {
      return 1;
    }
  }

  function getTasks(): array {
    $this->addSortOrder();
    $this->addPagination();
    return $this->query->find_many();
  }

  function getHtml(): string {
    $numPages = $this->getPageCount();
    $tasks = $this->getTasks();

    Smart::assign([
      'tasks' => $tasks,
      'params' => $this->params,
      'numPages' => $numPages,
    ]);
    return Smart::fetch('bits/taskTable.tpl');
  }
}
