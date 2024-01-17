<?php

class RoundTaskTable extends TaskTable {

  function __construct(RoundTaskTableParams $params) {
    parent::__construct($params);

    $round = Round::get_by_id($params->roundId);

    $this->params->showScores &=
      Identity::isLoggedIn() &&
      Identity::mayViewRoundScores($round->as_array());
  }

  function buildQuery(): ORMWrapper {
    $joinClause = sprintf(
      "(s.task_id = t.id and s.round_id = rt.round_id and s.user_id = %s)",
      $this->params->userId);

    $query = Model::factory('Task')
      ->table_alias('t')
      ->select('t.*')
      ->select('s.score')
      ->select('rt.order_id', 'number')
      ->join('ia_round_task', [ 't.id', '=', 'rt.task_id' ], 'rt')
      ->raw_join('left join ia_score_user_round_task', $joinClause, 's')
      ->where('rt.round_id', $this->params->roundId);

    $query = $this->addAttemptedFilter($query);
    return $query;
  }

  private function addAttemptedFilter(ORMWrapper $query): ORMWrapper {
    switch ($this->params->attempted) {
      case RoundTaskTableParams::A_UNTOUCHED:
        return $query->where_null('s.score');
      case RoundTaskTableParams::A_ATTEMPTED:
        return $query->where_lt('s.score', 100);
      case RoundTaskTableParams::A_SOLVED:
        return $query->where('s.score', 100);
      default:
        return $query;
    }
  }

}
