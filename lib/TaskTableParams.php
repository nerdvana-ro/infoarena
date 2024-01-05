<?php

/**
 * Parameters for TaskTable.
 **/

class TaskTableParams {
  const A_ALL = 0;
  const A_UNTOUCHED = 1;
  const A_ATTEMPTED = 2;
  const A_SOLVED = 3;
  const NUM_ATTEMPTED = 4;

  static function getAttemptedText(int $attempted) {
    switch ($attempted) {
      case 0: return 'toate';
      case 1: return 'neîncercate';
      case 2: return 'încercate';
      case 3: return 'rezolvate';
    }
  }

  public string $roundId;
  public int $userId;
  public int $attempted;

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
