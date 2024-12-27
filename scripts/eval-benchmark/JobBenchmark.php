<?php

class JobBenchmark {
  const GOOD_LANGUAGES = [ 'c', 'c-32', 'cpp', 'cpp-32' ];

  private Database $db;
  private Job $job;
  private string $owner;
  private array $tests;
  private int $numTaskTests;
  private int $numJobTests;
  private BenchmarkJudge $judge;
  private array $timePairs = [];

  function __construct(Job $job, Database $db) {
    $this->job = $job;
    $this->db = $db;
    $this->owner = $this->db->getUser($this->job->user_id);
    WorkStack::setJob($this->job, $this->owner);
    $this->judge = new BenchmarkJudge($this->job, WorkStack::getTask());
  }

  function run(): void {
    Log::default('Benchmarking job %d/%d (ID #%d, user %s).',
                 [ WorkStack::getJobNo(), WorkStack::getJobCount(),
                   $this->job->id, $this->owner ]);

    $this->tests = $this->db->loadTests($this->job->id);
    $this->numJobTests = count($this->tests);
    $this->numTaskTests = WorkStack::getTaskTestCount();

    if ($this->sanityCheck()) {
      $this->benchmarkAllTests();
    }
  }

  private function sanityCheck(): bool {
    if ($this->numJobTests != $this->numTaskTests) {
      $this->reportBadTestCount();
      return false;
    }
    $lang = $this->job->compiler_id;
    if (!in_array($lang, self::GOOD_LANGUAGES)) {
      Log::warn('SKIPPING: not handling %s code', [ $lang ], 1);
      return false;
    }

    return true;
  }

  private function reportBadTestCount(): void {
    Log::warn('SKIPPING (task specifies %d tests, job has %d)',
              [ $this->numTaskTests, $this->numJobTests ],
              1);
  }

  private function benchmarkAllTests(): void {
    Log::default('Running %d tests', [ $this->numJobTests ], 1);
    try {
      $this->judge->run();
      $this->buildTimePairs();
    } catch (EvalException $e) {
      Log::warn('Aborting job because %s (%s).',
                [ $e->getShortMessage(), $e->getMessage() ],
                1);
    }
  }

  private function buildTimePairs(): void {
    $newResults = $this->judge->getResults();

    foreach ($this->tests as $oldT) {
      $n = $oldT['test_number'];
      $ta = new TestAction($oldT);
      $action = $ta->recommend();

      if ($action != TestAction::ACTION_USE) {
        $this->reportUnusableTest($oldT, $action);
      } else if (!isset($newResults[$n])) {
        $this->reportNoNewResult($oldT);
      } else if ($newResults[$n]->status == NewResult::ST_OTHER) {
        $this->reportIgnoredAfterRun($oldT, $newResults[$n]);
      } else {
        $this->buildTimePair($oldT, $newResults[$n]);
      }
    }
  }

  private function reportUnusableTest(array $test, int $action): void {
    $verdict = TestAction::getVerdict($action);
    $fmt = 'Test #%02d: %s (points: %d, time: %g, judge message: %s)';
    $args = [
      $test['test_number'],
      $verdict,
      $test['points'],
      (float)$test['exec_time'] / 1000,
      $test['grader_message']
    ];

    if ($action == TestAction::ACTION_REPORT) {
      Log::warn($fmt, $args, 2);
    } else {
      Log::info($fmt, $args, 2);
    }
  }

  private function reportNoNewResult(array $test): void {
    $fmt = 'Test #%02d: No new result at all';
    Log::warn($fmt, [ $test['test_number'] ], 2);
  }

  private function reportIgnoredAfterRun(array $oldT, NewResult $newT): void {
    $fmt = 'Test #%02d: ignored after grading ' .
      '(old points: %d, old time: %g, old message: %s) ' .
      '(new time: %d, new message: %s)';

    $args = [
      $oldT['test_number'],
      $oldT['points'],
      (float)$oldT['exec_time'] / 1000,
      $oldT['grader_message'],
      $newT->time,
      $newT->message,
    ];

    Log::warn($fmt, $args, 2);
  }

  private function buildTimePair(array $oldT, NewResult $newT): void {
    $oldTime = (float)$oldT['exec_time'] / 1000;
    $oldTle = ($oldTime >= WorkStack::getTaskTimeLimit());
    $newTime = $newT->time;
    $newTle = ($newT->status == NewResult::ST_TLE);

    $fmt = 'Test #%02d: old time %g%s, new time %g%s';
    $args = [
      $oldT['test_number'],
      $oldTime,
      $oldTle ? ' (TLE)' : '',
      $newTime,
      $newTle ? ' (TLE)' : '',
    ];

    Log::default($fmt, $args, 2);

    $this->timePairs[] = new TimePair($oldTime, $oldTle, $newTime, $newTle);
  }

  function getTimePairs(): array {
    return $this->timePairs;
  }
}
