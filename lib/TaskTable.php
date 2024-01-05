<?php

/**
 * This class loads pages of tasks and returns HTML-formatted data. Used once
 * when loading the page, then via Ajax for subsequent page loads.
 **/

class TaskTable {

  private TaskTableParams $params;
  private ORMWrapper $query;
  private int $numResults;
  private array $tasks;

  function __construct(TaskTableParams $params) {
    $this->params = $params;

    $round = Round::get_by_id($params->roundId);

    $this->params->showScores &=
      Identity::isLoggedIn() &&
      Identity::mayViewRoundScores($round->as_array());
  }

  function run(): void {
    $this->prepareQuery();
    $this->addAttemptedFilter();
    $this->numResults = $this->query->count();

    // Cannot reuse $this->query because count() sets limits.
    $this->prepareQuery();
    $this->addAttemptedFilter();
    $this->addSortOrder();
    $this->addPagination();
    $this->tasks = $this->query->find_many();

    $taskIds = array_column($this->tasks, 'id');
    Preload::loadTaskAuthors($taskIds);
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

  function getPageCount(): int {
    if ($this->params->showPagination) {
      return ceil($this->numResults / $this->params->pageSize);
    } else {
      return 1;
    }
  }

  function getFirstResult() {
    return ($this->params->pageNo - 1) * $this->params->pageSize + 1;
  }

  function getLastResult() {
    return $this->getFirstResult() + count($this->tasks) - 1;
  }

  function getTasks(): array {
    return $this->tasks;
  }

  function getHtml(): string {
    Smart::assign([
      'firstResult' => $this->getFirstResult(),
      'lastResult' => $this->getLastResult(),
      'numPages' => $this->getPageCount(),
      'numResults' => $this->numResults,
      'params' => $this->params,
      'tasks' => $this->tasks,
    ]);
    return Smart::fetch('bits/taskTable.tpl');
  }
}
