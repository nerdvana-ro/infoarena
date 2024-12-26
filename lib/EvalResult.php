<?php

class EvalResult {
  public int $score = 0;
  public string $message = '';
  public string $log = '';
  public array $testResults = [];

  function setPending() {
    $this->message = 'Evaluare incompletă';
  }

  function setComplete() {
    $this->message = 'Evaluare completă';
  }
}
