<?php

class Tag extends Base {

  public static $_table = 'ia_tags';

  function getTaskSearchUrl(): string {
    return sprintf('%scauta-probleme?tag_ids=%d',
                   Config::URL_PREFIX,
                   $this->id);
  }

  static function getWithCounts(array $types, array $tagIds): array {
    $query = Model::factory('Tag')
      ->table_alias('tag')
      ->select('tag.*')
      ->select_expr('count(*)', 'num_tasks')
      ->join('ia_task_tags', [ 'tag.id', '=', 'tt.tag_id' ], 'tt')
      ->join('ia_task', [ 'tt.task_id', '=', 't.id' ], 't')
      ->where('t.security', 'public')
      ->where_in('tag.type', $types ?: [ '' ])
      ->group_by('tag.id')
      ->order_by_asc('tag.name');

    $tagSubquery = 't.id in (select task_id from ia_task_tags where tag_id = %d)';
    foreach ($tagIds as $tagId) {
      $where = sprintf($tagSubquery, $tagId);
      $query = $query->where_raw($where);
    }

    return $query->find_many();
  }

}
