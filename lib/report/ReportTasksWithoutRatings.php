<?php

class ReportTasksWithoutRatings extends Report {

  function getDescription(): string {
    return 'Probleme fără ratinguri';
  }

  function getVariable(): string {
    return 'Count.tasksWithoutRatings';
  }

  function getTemplateName(): string {
    return 'report/taskList.tpl';
  }

  function getSupportedActions(): array {
    return [ ];
  }

  function buildQuery(): ORM {
    return Model::factory('Task')
      ->table_alias('t')
      ->select('t.id')
      ->select('t.title')
      ->left_outer_join('ia_task_ratings', [ 't.id', '=', 'r.task_id' ], 'r')
      ->where_null('r.id');
  }

  function getLiveCount(): int {
    return $this->buildQuery()->count();
  }

  function getTasks(): array {
    return $this->buildQuery()->find_many();
  }

}
