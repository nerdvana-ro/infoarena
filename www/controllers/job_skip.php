<?php
require_once(Config::ROOT."common/db/job.php");
require_once(Config::ROOT."common/db/task.php");

function controller_job_skip() {
  if (!Request::isPost()) {
    FlashMessage::addError('Nu pot ignora joburi printr-un request de tip GET.');
    redirect(url_monitor());
  }

  Identity::enforceSkipJobs();

  $job_ids = explode(',', request('skipped-jobs'));
  $count = 0;

  foreach ($job_ids as $id) {
    $job = Job::get_by_id($id);
    if ($job && $job->status != 'skipped') {
      $job->status = 'skipped';
      $job->save();
      $count++;
    }
  }

  FlashMessage::addSuccess('Am ignorat ' . $count . ' joburi.');
  Util::redirect(Util::getReferrer());
}
