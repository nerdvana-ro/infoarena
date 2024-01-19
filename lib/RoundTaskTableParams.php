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

  static function getAttemptedText(int $attempted) {
    return self::A_NAMES[$attempted];
  }

  function populateFromRequest(): void {
    parent::populateFromRequest();

    $columns = Request::getArray('columns');
    $this->roundId = Request::get('roundId');
    $this->userId = Request::get('userId');
    $this->attempted = Request::get('attempted');
    $this->showNumbers = in_array('number', $columns);
  }
}
