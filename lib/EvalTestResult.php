<?php

class EvalTestResult {
  public int $score;
  public string $message;
  public int $time;         // milliseconds
  public int $memory;       // kb
  public ?int $graderTime;
  public ?int $graderMemory;

  function __construct(int $score, string $message, int $time, int $memory,
                       ?int $graderTime, ?int $graderMemory) {
    $this->score = $score;
    $this->message =  $message;
    $this->time = $time;
    $this->memory = $memory;
    $this->graderTime = $graderTime;
    $this->graderMemory = $graderMemory;
  }
}
