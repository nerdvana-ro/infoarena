<?php

class IsolateResult {
  // It is the isolate caller's responsibility to handle the case of a missing
  // output file.
  const SUCCESS = 0;
  const TLE = 1;
  const MLE = 2;
  const WALL_TLE = 3;
  const KILLED_BY_SIGNAL = 4;
  const NONZERO_EXIT_STATUS = 5;

  public int $status;
  public string $message;
  public int $memory;
  public float $time;
  public float $wallTime;

  function __construct(int $status, string $message, int $memory,
                       float $time, float $wallTime) {
    $this->status = $status;
    $this->message = $message;
    $this->memory = $memory;
    $this->time = $time;
    $this->wallTime = $wallTime;
  }

  function success(): bool {
    return $this->status == self::SUCCESS;
  }
}
