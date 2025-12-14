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
    return count($this->testResults)
      ? max(array_column($this->testResults, 'time'))
      : 0;
  }

  function getMaxMemory(): int {
    return count($this->testResults)
      ? max(array_column($this->testResults, 'memory'))
      : 0;
  }
}
