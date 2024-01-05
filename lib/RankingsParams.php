<?php

/**
 * Parameters for Rankings.
 **/

class RankingsParams {
  public string $roundDescription;

  public bool $detailRound;
  public bool $detailTask;

  public bool $showPagination;
  public int $pageNo;
  public int $pageSize;

  public string $sortField;
  public bool $sortAsc;
}
