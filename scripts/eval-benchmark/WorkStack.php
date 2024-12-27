<?php

/**
 * Keeps track of the current task, job and test. Also wraps getters around
 * some array fields.
 **/

class WorkStack {
  private static int $taskCount;
  private static Task $task;
  private static int $taskNo;

  private static int $jobCount;
  private static Job $job;
  private static string $jobOwner;
  private static int $jobNo;

  static function setTaskCount(int $taskCount): void {
    self::$taskCount = $taskCount;
    self::$taskNo = 0;
  }

  static function getTaskCount(): int {
    return self::$taskCount;
  }

  static function setTask(Task $task): void {
    self::$task = $task;
    self::$taskNo++;
  }

  static function getTask(): Task {
    return self::$task;
  }

  static function getTaskTimeLimit(): float {
    return self::$task->getTimeLimit();
  }

  static function getTaskTestCount(): int {
    return self::$task->test_count;
  }

  static function getTaskNo(): int {
    return self::$taskNo;
  }

  static function setJobCount(int $jobCount): void {
    self::$jobCount = $jobCount;
    self::$jobNo = 0;
  }

  static function getJobCount(): int {
    return self::$jobCount;
  }

  static function setJob(Job $job, string $jobOwner): void {
    self::$job = $job;
    self::$jobOwner = $jobOwner;
    self::$jobNo++;
  }

  static function getJob(): Job {
    return self::$job;
  }

  static function getJobOwner(): string {
    return self::$jobOwner;
  }

  static function getJobNo(): int {
    return self::$jobNo;
  }
}
