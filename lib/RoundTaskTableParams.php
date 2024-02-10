<?php

class RoundTaskTableParams extends TaskTableParams {
  const A_ALL = 0;
  const A_UNTOUCHED = 1;
  const A_ATTEMPTED = 2;
  const A_SOLVED = 3;
  const NUM_ATTEMPTED = 4;

  const A_NAMES = [
    'toate',
    'neîncercate',
    'încercate',
    'rezolvate',
  ];

  public string $roundId;
  public int $userId;
  public int $attempted;

  public bool $showNumbers;

  function __construct() {
    parent::__construct();
    $this->attempted = Request::getInt('attempted');
  }

  function getDefaultSortField(): string {
    return 'number';
  }

  function getDefaultSortAsc(): bool {
    return true;
  }

  static function getAttemptedText(int $attempted) {
    return self::A_NAMES[$attempted];
  }
}
