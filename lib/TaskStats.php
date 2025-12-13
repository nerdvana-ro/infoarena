<?php

/**
 * This class collects the top 5 jobs on three metrics: running time, memory
 * usage and code size.
 **/

class TaskStats {
  const int TOP_SIZE = 5;

  const int T_TIME = 0;
  const int T_MEMORY = 1;
  const int T_LENGTH = 2;
  const int NUM_TYPES = 3;

  public int $type;
  public array $jobs;

  function __construct(int $type, array $jobs) {
    $this->type = $type;
    $this->jobs = $jobs;
  }

  function getName(): string {
    switch ($this->type) {
      case self::T_TIME: return 'timp';
      case self::T_MEMORY: return 'memorie';
      case self::T_LENGTH: return 'mărime';
    }
  }

  function getDescription(): string {
    switch ($this->type) {
      case self::T_TIME: return 'Clasament după timpul de execuție';
      case self::T_MEMORY: return 'Clasament după memoria folosită';
      case self::T_LENGTH: return 'Clasament după dimensiunea sursei';
    }
  }

  function getUnit(): string {
    switch ($this->type) {
      case self::T_TIME: return 'ms';
      case self::T_MEMORY: return 'kb';
      case self::T_LENGTH: return 'b';
    }
  }

  function getMetric(int $row): ?string {
    $job = $this->jobs[$row];
    switch ($this->type) {
      case self::T_TIME: return $job->max_time;
      case self::T_MEMORY: return $job->max_memory;
      case self::T_LENGTH: return strlen($job->file_contents);
    }
  }

  static function loadForTask(string $taskId): array {
    $results = [];

    // This always runs 3×5 queries even if there aren't enough jobs.
    for ($type = 0; $type < self::NUM_TYPES; $type++) {
      $jobs = [];
      $badUserIds = [];
      for ($i = 0; $i < self::TOP_SIZE; $i++) {
        $job = self::getJob($taskId, $type, $badUserIds);
        if ($job) {
          $jobs[] = $job;
          $badUserIds[] = $job->user_id;
        }
      }

      if (count($jobs)) {
        $results[] = new TaskStats($type, $jobs);
      }
    }

    return $results;
  }

  private static function getSortCriterion(int $type): string {
    switch ($type) {
      case self::T_TIME: return 'max_time';
      case self::T_MEMORY: return 'max_memory';
      case self::T_LENGTH: return 'length(j.file_contents)';
    }
  }

  private static function getJob(string $taskId, int $type, array $badUserIds): ?Job {
    $crit = self::getSortCriterion($type);

    $job = Model::factory('Job')
      ->table_alias('j')
      ->select('j.*')
      ->join('ia_round', [ 'j.round_id', '=', 'r.id' ], 'r')
      ->where('j.task_id', $taskId)
      ->where('j.score', Config::EVAL_MAX_SCORE)
      ->where('j.status', 'done')
      ->where('r.public_eval', true)
      ->where_not_in('j.user_id', $badUserIds ?: [0])
      ->order_by_expr($crit)
      ->order_by_asc('j.submit_time')
      ->find_one();

    return $job ?: null;
  }
}
