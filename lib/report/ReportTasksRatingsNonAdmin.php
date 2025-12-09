<?php

class ReportTasksRatingsNonAdmin extends Report {

  function getDescription(): string {
    return "Probleme cu ratinguri date de non-admini";
  }

  function getVariable(): string {
    return 'Count.tasksRatingsNonAdmin';
  }

  function getTemplateName(): string {
    return 'report/tasksRatingsNonAdmin.tpl';
  }

  function getSupportedActions(): array {
    return [ ];
  }

  function buildQuery(): ORM {
    return Model::factory('Task')
      ->table_alias('t')
      ->select('t.*')
      ->select('u.full_name')
      ->join('ia_task_ratings', [ 't.id', '=', 'r.task_id' ], 'r')
      ->join('ia_user', [ 'r.user_id', '=', 'u.id' ], 'u')
      ->where_not_equal('u.security_level', 'admin');
}

  function getLiveCount(): int {
    return $this->buildQuery()->count();
  }

  function getTasks(): array {
    return $this->buildQuery()
      ->order_by_asc('t.id')
      ->find_many();
  }

}
