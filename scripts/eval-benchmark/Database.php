<?php

require_once(Config::ROOT . 'common/log.php');
require_once(Config::ROOT . 'common/db/job.php');
require_once(Config::ROOT . 'common/db/task_statistics.php');

class Database {
  const ADMIN_USERNAMES = [ 'francu', 'Catalin.Francu', 'mihai.tutu' ];

  private array $userMap = [];
  private array $adminMap = [];

  function __construct() {
    db_connect();
  }

  function loadUsers(): void {
    $users = user_get_all();
    foreach ($users as $u) {
      $this->userMap[$u['id']] = $u['username'];
      if (in_array($u['username'], self::ADMIN_USERNAMES)) {
        $this->adminMap[$u['id']] = $u['username'];
      }
    }

    $this->logLoadedUsers();
  }

  private function logLoadedUsers(): void {
    $numUsers = count($this->userMap);
    $numAdmins = count($this->adminMap);
    $adminUsernames = implode(', ', $this->adminMap);
    Log::info('Loaded %d users, of which %d admins (%s).',
              [ $numUsers, $numAdmins, $adminUsernames ]);
  }

  function getUser(int $userId): string {
    return $this->userMap[$userId];
  }

  function loadTasks(): array {
    return Model::factory('Task')
      ->order_by_asc('id')
      ->find_many();
  }

  function loadTaskById(string $id): Task {
    $task = Task::get_by_id($id);
    if (!$task) {
      throw new BException('Task %s not found.', [ $id ]);
    }
    return $task;
  }

  function getAdminJobsQuery(string $taskId): ORMWrapper {
    $userIds = array_keys($this->adminMap);
    return Model::factory('Job')
      ->where('task_id', $taskId)
      ->where('status', 'done')
      ->where_in('user_id', $userIds);
  }

  function loadAdminJobs(string $taskId): array {
    return $this->getAdminJobsQuery($taskId)
      ->order_by_asc('id')
      ->find_many();
  }

  function countAdminJobs(string $taskId): int {
    return $this->getAdminJobsQuery($taskId)
      ->count();
  }

  function getAllJobsQuery(string $taskId): ORMWrapper {
    return Model::factory('Job')
      ->where('task_id', $taskId)
      ->where('status', 'done');
  }

  function loadAllJobs(string $taskId): array {
    return $this->getAllJobsQuery($taskId)
      ->order_by_asc('id')
      ->find_many();
  }

  function countJobs(string $taskId): int {
    return $this->getAllJobsQuery($taskId)
      ->count();
  }

  function loadTests(int $jobId): array {
    return job_test_get_all($jobId);
  }
}
