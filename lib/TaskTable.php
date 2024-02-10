<?php

/**
 * This class loads pages of tasks given a set of parameters.
 **/

abstract class TaskTable {

  protected TaskTableParams $params;
  private int $numResults;
  private array $tasks;

  function __construct(TaskTableParams $params) {
    $this->params = $params;
  }

  abstract function buildCountQuery(): ORMWrapper;
  abstract function buildQuery(): ORMWrapper;

  function run(): void {
    $query = $this->buildCountQuery();
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

  function getNumPages(): int {
    if ($this->params->showPagination) {
      return ceil($this->numResults / $this->params->pageSize);
    } else {
      return 1;
    }
  }

  function getFirstResult(): int {
    return ($this->params->pageNo - 1) * $this->params->pageSize + 1;
  }

  function getLastResult(): int {
    return $this->getFirstResult() + count($this->tasks) - 1;
  }

  function getNumResults(): int {
    return $this->numResults;
  }

  function getTasks(): array {
    return $this->tasks;
  }
}
