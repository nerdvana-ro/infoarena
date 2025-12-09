<?php

class TaskRatings extends Base {

  public static $_table = 'ia_task_ratings';

  const int MAX_VALUE = 7;

  // Checks to see if a value is an int between 1 and MAX_VALUE.
  static function isRatingValue($rating_value) {
    if (!is_whole_number($rating_value)) {
      return false;
    }

    $intVal = intval($rating_value);
    return ($intVal >= 1) && ($intVal <= self::MAX_VALUE);
  }

  static function getForTask(string $taskId): ?TaskRatings {
    $tr = Model::factory('TaskRatings')
      ->where('task_id', $taskId)
      ->find_one();
    return $tr ?: null;
  }

  static function saveRatings(string $taskId, int $userId, array $ratings): void {
    $tr = self::getForTask($taskId);
    if (!$tr) {
      $tr = Model::factory('TaskRatings')->create();
      $tr->task_id = $taskId;
    }
    $tr->user_id = $userId;
    $tr->idea = $ratings['idea'];
    $tr->theory = $ratings['theory'];
    $tr->coding = $ratings['coding'];
    $tr->save();

    $task = Task::get_by_id($taskId);
    $task->rating = $tr->getStarRating();
    $task->save();
  }

  function getStarRating(): float {
    $sum = $this->idea + $this->theory + $this->coding;

    if (!$sum) {
      return 0.0;
    }

    // Scores of 1/1/1 correspond to ½ star. Scores of MAX/MAX/MAX correspond to
    // SCALE stars. Do the math.
    return 0.5 + (StarRating::SCALE - 0.5) * ($sum - 3) /
      (3 * self::MAX_VALUE - 3);
  }
}
