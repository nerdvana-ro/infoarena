<?php

class ReportRoundsNullStartTime extends Report {

  function getDescription(): string {
    return 'Runde cu timp de început nul';
  }

  function getVariable(): string {
    return 'Count.roundsNullStartTime';
  }

  function getTemplateName(): string {
    return 'report/roundsNullStartTime.tpl';
  }

  function getSupportedActions(): array {
    return [ 'round_delete' ];
  }

  function buildQuery(): ORM {
    return Model::factory('Round')
      ->where_null('start_time');
  }

  function getLiveCount(): int {
    $query = $this->buildQuery();
    return $query->count();
  }

  function getRounds(): array {
    $query = $this->buildQuery();
    return $query
      ->order_by_asc('id')
      ->find_many();
  }

}
