<?php

require_once __DIR__ . '/../Config.php';
require_once __DIR__ . '/../lib/Core.php';

db_connect();

(count($argv) == 3) || usage($argv[0]);

$round = Round::get_by_id($argv[1]);
$round || error("Round {$argv[1]} not found.");
$destDir = $argv[2];

exportRound($round, $destDir);

/*************************************************************************/

function usage(string $cmd): void {
    error("Usage: php $cmd <round_id> <dest_dir>

Exports the sources of the round with the given ID to the given directory,
which it creates if it doesn't exist.");
}

function error(string $msg): void {
    print "$msg\n";
    exit(1);
}

function exportRound(Round $round, string $destDir): void {
  if (!file_exists($destDir) || !is_dir($destDir)) {
    mkdir($destDir) || error("Cannot create directory $destDir.");
  }

  $jobs = Model::factory('Job')
    ->where('round_id', $round->id)
    ->order_by_asc('id')
    ->find_many();

  writeHeaderInfo($destDir);
  foreach ($jobs as $job) {
    $compiler = Config::COMPILERS[$job->compiler_id];
    $sourceName = $job->id . '.' . $compiler['extension'];
    file_put_contents($destDir . '/' . $sourceName, $job->file_contents);
    appendJobInfo($job, $destDir);
  }
}

function writeHeaderInfo(string $destDir): void {
  $fields = [
    'job_id',
    'username',
    'full_name',
    'task_id',
    'submit_time',
    'score',
    'max_time',
    'max_memory',
  ];
  $s = implode(',', $fields) . "\n";
  file_put_contents($destDir . '/__job_info.txt', $s);
}

function appendJobInfo(Job $job, string $destDir): void {
  $user = User::get_by_id($job->user_id);
  $data = [
    $job->id,
    $user->username,
    $user->full_name,
    $job->task_id,
    $job->submit_time,
    $job->score,
    $job->max_time,
    $job->max_memory,
  ];
  $s = implode(',', $data) . "\n";
  file_put_contents($destDir . '/__job_info.txt', $s, FILE_APPEND);
}
