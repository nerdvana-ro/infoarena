<?php

class EvalResult {
  public int $score = 0;
  public string $message = '';
  public string $log = '';
  public array $testResults = [];

  function setPending(): void {
    $this->message = 'Evaluare incompletă';
  }

  function setComplete(): void {
    $this->message = 'Evaluare completă';
  }

  function getMaxTime(): int {
    return max(array_column($this->testResults, 'time'));
  }

  function getMaxMemory(): int {
    return max(array_column($this->testResults, 'memory'));
  }
}
