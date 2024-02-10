<?php

/**
 * Parameters for Rankings.
 **/

class RankingsParams extends PagerParams {
  public string $roundDescription;
  public bool $detailRound;
  public bool $detailTask;

  function getDefaultSortField(): string {
    return 'rank';
  }

  function getDefaultSortAsc(): bool {
    return true;
  }

}
