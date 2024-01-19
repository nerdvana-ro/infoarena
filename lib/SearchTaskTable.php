<?php

class SearchTaskTable extends TaskTable {

  function buildCountQuery(): ORMWrapper {
    $tagSubquery = 'id in (select task_id from ia_task_tags where tag_id = %d)';

    $query = Model::factory('Task')
      ->where('security', 'public');
    foreach ($this->params->tagIds as $tagId) {
      $where = sprintf($tagSubquery, $tagId);
      $query = $query->where_raw($where);
    }

    return $query;
  }

  function buildQuery(): ORMWrapper {

    $joinClause = sprintf(
      '(t.id = s.task_id and s.user_id = %s)',
      Identity::getId());

    $query = $this->buildCountQuery()
      ->table_alias('t')
      ->select('t.*')
      ->select_expr('max(s.score)', 'score')
      ->raw_join('left join ia_score_user_round_task', $joinClause, 's')
      ->where('t.security', 'public')
      ->group_by('t.id');

    return $query;
  }

  function getAjaxUrl(): string {
    return 'ajax/searchTaskTable.php';
  }
}
