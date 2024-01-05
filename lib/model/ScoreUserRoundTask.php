<?php

class ScoreUserRoundTask extends Base {

  public static $_table = 'ia_score_user_round_task';

  static function getByUserIdRoundId(string $userId, string $roundId): array {
    return Model::factory('ScoreUserRoundTask')
      ->where('user_id', $userId)
      ->where('round_id', $roundId)
      ->find_many();
  }

  function updateScore(float $score): void {
    score_update($this->user_id, $this->task_id, $this->round_id, $score);
  }

  // Returns a map of userId => roundId => taskId => score. We keep both the
  // roundId and taskId in case multiple rounds use the same task.
  static function loadByRoundIdsUserIds(array $roundIds, array $userIds): array {
    $records = Model::factory('ScoreUserRoundTask')
      ->where_in('round_id', $roundIds)
      ->where_in('user_id', $userIds)
      ->find_many();

    $results = [];
    foreach ($records as $rec) {
      $results[$rec->user_id][$rec->round_id][$rec->task_id] = (float)$rec->score;
    }
    return $results;
  }
}
