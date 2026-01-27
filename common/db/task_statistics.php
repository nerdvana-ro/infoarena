<?php

require_once Config::ROOT . 'common/db/db.php';
require_once Config::ROOT . 'common/db/task.php';

function task_statistics_get_average_wrong_submissions($task_id) {
    $query = sprintf("
        SELECT IFNULL(AVG(`incorrect_submits`),0.0)
        FROM `ia_score_user_round_task`
        WHERE `task_id` = '%s'
        AND `submits` > 0",
        db_escape($task_id));
    return db_query_value($query, 0.0);
}

// Returns the number of incorrect submissions of $user_id for $task_id.
// Using SUM() because db_query_value() crashes if it finds more than 1 row.
// This happens when a user has submits in multiple rounds for the same task.
// Added COALESCE so we get a clean 0 instead of NULL if there's no data.
function task_statistics_get_user_wrong_submissions($task_id, $user_id) {
    $query = sprintf("
        SELECT COALESCE(SUM(`incorrect_submits`), 0)
        FROM `ia_score_user_round_task`
        WHERE `task_id` = '%s'
        AND `user_id` = %d
        AND `submits` > 0",
        db_escape($task_id),
        $user_id);
    return db_query_value($query, 0);
}

// Returns the ratio between the number of users who solved the problem and
// the number of users who attempted the problem measured in percentages.
function task_statistics_get_solved_percentage($task_id) {
    $query = sprintf("
        SELECT solved.count / attempted.count
        FROM (
           SELECT COUNT(*) AS count
           FROM `ia_score_user_round_task`
           WHERE `task_id` = '%s'
           AND `score` = 100
           AND `submits` > 0
          ) AS solved,
          (
           SELECT COUNT(*) AS count
           FROM `ia_score_user_round_task`
           WHERE `task_id` = '%s'
           AND `submits` > 0
          ) AS attempted",
        db_escape($task_id),
        db_escape($task_id));
    $percentage = db_query_value($query, 0.0);
    $percentage = round($percentage * 100, 1);
    return $percentage;
}

function task_get_solved_by($task_id) {
    $query = sprintf("SELECT `solved_by` FROM ia_task
                WHERE `id`='%s'", db_escape($task_id));
    $result = db_query($query);
    if ($row = db_next_row($result)) {
        return $row['solved_by'];
    } else {
        return 0;
    }
}

function task_was_solved_by($task_id, $user_id) {
    $query = sprintf("SELECT COUNT(1) FROM ia_task_users_solved
                    WHERE `task_id`='%s'
                    AND `user_id`=%s",
                    db_escape($task_id),
                    db_escape($user_id));
    $result = db_query_value($query);
    if ($result == 1) {
        return true;
    } else {
        return false;
    }
}

function task_mark_solved($task_id, $user_id) {
    $query = sprintf("INSERT INTO ia_task_users_solved
                    VALUES ('%s', %s)",
                    db_escape($task_id),
                    db_escape($user_id));
    db_query($query);
}

function task_mark_not_solved($task_id, $user_id) {
    $query = sprintf("DELETE FROM ia_task_users_solved
                    WHERE `task_id`='%s'
                    AND `user_id`=%s",
                    db_escape($task_id),
                    db_escape($user_id));
    db_query($query);
}

function task_update_solved_count($task_id, $count) {
    $query = sprintf("UPDATE ia_task
                    SET `solved_by`=$count
                    WHERE `id`='%s'",
                    db_escape($task_id));
    db_query($query);
}

function task_max_submission($task_id, $user_id) {
    $query = sprintf("SELECT `round_id`, `score`, `status` FROM ia_job
                    WHERE `task_id`='%s' AND `user_id`=%s",
                    db_escape($task_id),
                    db_escape($user_id));
    $result = db_query($query);
    $score = 0;
    while ($row = db_next_row($result)) {
        if (!is_null($row['round_id']) && $row['status'] === 'done') {
            $score = max($score, $row['score']);
        }
    }
    db_free($result);
    return $score;
}

function task_update_solved_by($task_id, $user_id) {
    $have_now = task_get_solved_by($task_id);
    $overall_solved = task_max_submission($task_id, $user_id) == 100;
    if ($overall_solved === true) {
        if (!task_was_solved_by($task_id, $user_id)) {
            task_mark_solved($task_id, $user_id);
            $have_now++;
            task_update_solved_count($task_id, $have_now);
        }
    } else {
        if (task_was_solved_by($task_id, $user_id)) {
            task_mark_not_solved($task_id, $user_id);
            $have_now--;
            task_update_solved_count($task_id, $have_now);
        }
    }
}
