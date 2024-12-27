<?php

require_once Config::ROOT . 'eval/ClassicJudge.php';

class BenchmarkJudge extends ClassicJudge {
  private array $newResults = [];

  public function __construct(Job $job, Task $task) {
    parent::__construct($job, $task);
    $this->setDryRun();
  }

  // Note that, even if a test passed on the old hardware, it may still fail
  // on the new one (e.g. job #552652).
  protected function runTest(int $testNo): IsolateResult {
    $res = parent::runTest($testNo);

    if ($res->status == IsolateResult::SUCCESS) {
      $status = NewResult::ST_OK;
    } else if ($res->status = IsolateResult::TLE) {
      $status = NewResult::ST_TLE;
    } else {
      $status = NewResult::ST_OTHER;
    }

    $this->newResults[$testNo] = new NewResult($status, $res->time, $res->message);

    return $res;
  }

  function getResults(): array {
    return $this->newResults;
  }
}
