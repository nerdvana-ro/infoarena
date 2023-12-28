<?php

class StarRating {
  const SCALE = 5;

  static function getStarTypes(Task $task): array {
    $r = $task->rating;
    $results = [];

    for ($i = 0; $i < self::SCALE; $i++) {
      if ($r < 0.25) {
        $results[] = 'empty';
      } else if ($r < 0.75) {
        $results[] = 'half';
      } else {
        $results[] = 'full';
      }
      $r--;
    }

    return $results;
  }
}
