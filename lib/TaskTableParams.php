<?php

/**
 * Parameters for TaskTable.
 **/

abstract class TaskTableParams {
  public string $cssClass;
  public bool $showRatings;
  public bool $showSolvedBy;
  public bool $showScores;

  public bool $showPagination;
  public int $pageNo;
  public int $pageSize;

  public string $sortField;
  public bool $sortAsc;

  function populateFromRequest(): void {
    $columns = Request::getArray('columns');
    $this->cssClass = '';
    $this->showRatings = in_array('rating', $columns);
    $this->showSolvedBy = in_array('solved_by', $columns);
    $this->showScores = in_array('score', $columns);
    $this->showPagination = Request::getBool('showPagination');
    $this->pageNo = Request::get('pageNo');
    $this->pageSize = Request::get('pageSize');
    $this->sortField = Request::get('sortField');
    $this->sortAsc = Request::get('sortAsc');
  }
}
