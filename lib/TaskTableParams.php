<?php

/**
 * Parameters for TaskTable.
 **/

abstract class TaskTableParams extends PagerParams {
  // Controlled by the caller.
  public string $cssClass;
  public bool $showRatings;
  public bool $showScores;
  public bool $showSolvedBy;
}
