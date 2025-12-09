<?php

const KEEP_USER_IDS = [ 92, 11423, 208, 76, 20, 829, 514, 23, 118, 219 ];

main_00019();

function main_00019(): void {
  $map = load_task_ratings();

  foreach ($map as $taskId => $ratings) {
    $keep = null;
    foreach ($ratings as $r) {
      printf("%s: %s: %d %d %d\n", $r->task_id, $r->user_id, $r->idea, $r->theory, $r->coding);
      $pos = find_user($r->user_id);

      if ($pos === null) {
        printf("  --> deleting rating from unknown user id {$r->user_id}\n");
        $r->delete();
      } else if ($keep) {
        $keepPos = find_user($keep->user_id);
        if ($keepPos < $pos) {
          printf("  --> deleting rating from user id {$r->user_id}\n");
          $r->delete();
        } else {
          printf("  --> deleting rating from user id {$keep->user_id}\n");
          $keep->delete();
          $keep = $r;
        }
      } else {
        $keep = $r;
      }
    }
  }
}

function load_task_ratings(): array {
  $ratings = Model::factory('TaskRatings')
    ->order_by_asc('task_id')
    ->find_many();

  $map = [];
  foreach ($ratings as $r) {
    $map[$r->task_id][] = $r;
  }
  return $map;
}

function find_user($id): ?int {
  foreach (KEEP_USER_IDS as $pos => $val) {
    if ($val == $id) {
      return $pos;
    }
  }

  return null;
}
