<?php

/**
 * This class loads pages of tasks and returns HTML-formatted data. Used once
 * when loading the page, then via Ajax for subsequent page loads.
 **/

abstract class TaskTable {

  protected TaskTableParams $params;
  private int $numResults;
  private array $tasks;

  function __construct(TaskTableParams $params) {
    $this->params = $params;
  }

  abstract function buildQuery(): ORMWrapper;

  function run(): void {
    $query = $this->buildQuery();
    $this->numResults = $query->count();

    $query = $this->buildQuery();
    $query = $this->addSortOrder($query);
    $query = $this->addPagination($query);
    $this->tasks = $query->find_many();

    $taskIds = array_column($this->tasks, 'id');
    Preload::loadTaskAuthors($taskIds);
  }

  private function addSortOrder(ORMWrapper $query): ORMWrapper {
    $field = $this->params->sortField;
    if (!$field) {
      return $query;
    }

    return $this->params->sortAsc
      ? $query->order_by_asc($field)
      : $query->order_by_desc($field);
  }

  private function addPagination(ORMWrapper $query): ORMWrapper {
    if (!$this->params->showPagination) {
      return $query;
    }

    return $query
      ->limit($this->params->pageSize)
      ->offset(($this->params->pageNo - 1) * $this->params->pageSize);
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
