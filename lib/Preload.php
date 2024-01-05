<?php

/**
 * This class preloads and stores various data that we know we will need
 * later. We do this in order to reduce the number of SQL queries. For
 * example, if we need to display 50 tasks and their authors, it is more
 * efficient to collect the author IDs and load them in a single query.
 */

class Preload {

  // A map of task ID => author tags
  private static array $taskAuthors;

  static function loadTaskAuthors(array $taskIds): void {
    self::$taskAuthors = array_fill_keys($taskIds, []);

    $tags = Model::factory('Tag')
      ->table_alias('t')
      ->select('t.*')
      ->select('tt.task_id')
      ->join('ia_task_tags', [ 't.id', '=', 'tt.tag_id' ], 'tt')
      ->where_in('tt.task_id', $taskIds ?: [''])
      ->where('t.type', 'author')
      ->find_many();

    foreach ($tags as $tag) {
      self::$taskAuthors[$tag->task_id][] = $tag;
    }
  }

  static function getTaskAuthors(string $taskId): array {
    return self::$taskAuthors[$taskId];
  }
}
