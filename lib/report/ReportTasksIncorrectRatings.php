<?php

class ReportTasksIncorrectRatings extends Report {

  function getDescription(): string {
    return "Probleme cu ratinguri mai mari de " . TaskRatings::MAX_VALUE;
  }

  function getVariable(): string {
    return 'Count.tasksIncorrectRatings';
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
      ->select('t.*')
      ->join('ia_task_ratings', [ 't.id', '=', 'r.task_id' ], 'r')
      ->where_any_is([
        [ 'r.idea' => TaskRatings::MAX_VALUE, ],
        [ 'r.theory' => TaskRatings::MAX_VALUE, ],
        [ 'r.coding' => TaskRatings::MAX_VALUE, ],
      ], [
        'r.idea' => '>',
        'r.theory' => '>',
        'r.coding' => '>',
      ]);
  }

  function getLiveCount(): int {
    return $this->buildQuery()->count();
  }

  function getTasks(): array {
    return $this->buildQuery()->find_many();
  }

}
