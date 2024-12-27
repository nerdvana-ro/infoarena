<?php

class EvalTestResult {
  public int $score;
  public string $message;
  public int $time;         // milliseconds
  public int $memory;       // kb
  public ?int $graderTime;
  public ?int $graderMemory;

  function __construct(int $score, string $message, float $time, int $memory,
                       ?float $graderTime, ?int $graderMemory) {
    $this->score = $score;
    $this->message =  $message;
    $this->time = round($time * 1000);
    $this->memory = $memory;
    $this->graderTime = ($graderTime === null)
      ? null : round($graderTime * 1000);
    $this->graderMemory = $graderMemory;
  }
}
