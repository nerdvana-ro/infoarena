<?php

/**
 * Parameters for TaskTable.
 **/

abstract class TaskTableParams {
  public string $cssClass;
  public bool $showNumbers;
  public bool $showRatings;
  public bool $showSolvedBy;
  public bool $showScores;

  public bool $showPagination;
  public int $pageNo;
  public int $pageSize;

  public string $sortField;
  public bool $sortAsc;
}
