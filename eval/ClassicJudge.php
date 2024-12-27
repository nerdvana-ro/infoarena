<?php

require_once Config::ROOT . 'common/task.php';

class ClassicJudge {

  private Job $job;
  private Task $task;
  private string $saveDir;
  private EvalResult $result;

  public function __construct(Job $job, Task $task) {
    $this->task = $task;
    $this->job = $job;
    $this->saveDir = sprintf(Config::EVAL_SAVE_DIR, $job->id);
    $this->result = new EvalResult();
  }

  function getGraderSrc():string {
    return $this->saveDir . 'grader.' . $this->task->getGraderExtension();
  }

  function getGraderBin():string {
    return $this->saveDir . 'grader';
  }

  function getUserExtension(): string {
    $cid = $this->job->compiler_id;
    return Config::COMPILERS[$cid]['extension'];
  }

  function getUserSrc():string {
    return $this->saveDir . 'main.' . $this->getUserExtension();
  }

  function getUserBin():string {
    return $this->saveDir . 'main';
  }

  function getTestSaveDir(int $testNo): string {
    return $this->saveDir . 'test-' . $testNo . '/';
  }

  private function cleanSaveDir(): void {
    system('rm -rf ' . $this->saveDir);
    @mkdir($this->saveDir, 0700);
  }

  // Compile the custom grader if the problem has one.
  private function compileGrader(): void {
    if (!$this->task->evaluator) {
      return;
    }

    if (!EvalDownloader::saveGrader($this->task, $this->getGraderSrc())) {
      log_print('Missing grader for ' . $this->task->title);
      throw new EvalTaskOwnerError(
        sprintf('Lipsește graderul. Pagina cu enunțul problemei ' .
                'trebuie să conțină un atașament "grader_%s".',
                $this->task->evaluator)
      );
    }

    $comp = new Compiler(
      $this->getGraderSrc(),
      $this->getGraderBin(),
      $this->saveDir . 'grader-compile-stdout',
      $this->saveDir . 'grader-compile-stderr');
    $comp->run();

    if (!$comp->success()) {
      log_print('Task grader compile error.');
      throw new EvalTaskOwnerError("Eroare de compilare în grader:\n" .
                                   $comp->getMessage());
    }
  }

  private function compileUserSource(): void {
    $cid = $this->job->compiler_id;
    $userSrc = $this->getUserSrc();
    file_put_contents($userSrc, $this->job->file_contents);

    $comp = new Compiler(
      $userSrc,
      $this->getUserBin(),
      $this->saveDir . 'main-compile-stdout',
      $this->saveDir . 'main-compile-stderr');
    $comp->setCompilerId($cid);
    $comp->run();

    if (!$comp->success()) {
      log_print('User program compile error.');
      throw new EvalUserCompileError($comp->getMessage());
    }

    $this->result->log = "Compilare:\n" . $comp->getMessage() . "\n";
  }

  private function downloadInFile(int $testNo): void {
    $dest = Config::ISOLATE_BOX . $this->task->id . '.in';
    if (!EvalDownloader::saveTestInput($this->task, $testNo, $dest)) {
      log_print("Test $testNo: input not found");
      throw new EvalTaskOwnerError(
        "Lipsește intrarea testului {$testNo}.\n" .
        "Pagina cu enunțul problemei trebuie să conțină un atașament " .
        "'grader_test{$testNo}.in'");
    }
  }

  private function downloadOkFile(int $testNo): void {
    if (!$this->task->use_ok_files) {
      return;
    }

    if (!EvalDownloader::ensureTestOk($this->task, $testNo)) {
      log_print("Test $testNo: .ok file not found");
      throw new EvalTaskOwnerError(
        "Lipsește fișierul .ok al testului $testNo.\n" .
        "Pagina cu enunțul problemei trebuie să conțină un atașament " .
        "'grader_test{$testNo}.ok'");
    }
  }

  private function copyOutputFiles(IsolateSandbox $iso, int $testNo): void {
    $dir = $this->getTestSaveDir($testNo);
    mkdir ($dir, 0700);
    $outFile = $this->task->id . '.out';

    $files = [
      $outFile => $outFile,
      'meta' => 'meta',
      Config::ISOLATE_STDOUT => 'main-stdout',
      Config::ISOLATE_STDERR => 'main-stderr',
    ];

    foreach ($files as $remote => $local) {
      try {
        $iso->pullFile($remote, $dir . $local);
      } catch (IsolateSandboxException $ignored) {
        // Don't make a fuss. In particular, the output file may not exist in
        // all non-success cases (TLE, MLE etc.).
      }
    }
  }

  private function copyGraderOutputFiles(IsolateSandbox $iso, int $testNo): void {
    $dir = $this->getTestSaveDir($testNo);
    $iso->pullFile(Config::ISOLATE_STDOUT, $dir . 'grader-stdout');
    $iso->pullFile(Config::ISOLATE_STDERR, $dir . 'grader-stderr');
  }

  // Runs the user binary on one test case. Returns the meta information from
  // isolate.
  private function runTest(int $testNo): IsolateResult {
    $iso = new IsolateSandbox();
    $iso->pushFile($this->getUserBin());
    $this->downloadInFile($testNo);
    $iso->setTimeLimit($this->task->getTimeLimit());
    $iso->setMemoryLimit($this->task->getMemoryLimit());
    $result = $iso->run('./main', '', [], []);
    $this->copyOutputFiles($iso, $testNo);
    return $result;
  }

  private function runDiffGrader(int $testNo, IsolateResult $res): EvalTestResult {
    $dir = $this->getTestSaveDir($testNo);
    $outFile = $dir . $this->task->id . '.out';;
    $okFile = EvalDownloader::getTestOk($this->task, $testNo);

    if (is_readable($outFile)) {
      $diff_output = shell_exec("diff -qBbEa $outFile $okFile");
      if ($diff_output == '') {
        log_print("Test $testNo: Diff eval: Files identical");
        $msg = 'OK';
        $score = 100 / $this->task->test_count;
      } else {
        log_print("Test $testNo: Diff eval: Files differ");
        $msg = 'Incorect';
        $score = 0;
      }
    } else {
      log_print("Test $testNo: Diff eval: output missing");
      $msg = 'Fișier de ieșire lipsă';
      $score = 0;
    }

    return new EvalTestResult(
      $score, $msg, $res->time * 1000, $res->memory, null, null);
  }

  // $res = result from the user binary isolate box.
  private function runCustomGrader(int $testNo, IsolateResult $res): EvalTestResult {
    $iso = new IsolateSandbox();

    if (Config::EVAL_GRADER_NEEDS_SOURCE) {
      $dest = $this->task->id . '.' . $this->getUserExtension();
      $iso->pushFile($this->getUserSrc(), $dest);
    }

    $iso->pushFile($this->getGraderBin());
    $iso->pushFile(EvalDownloader::getTestIn($this->task, $testNo), $this->task->id . '.in');
    $iso->pushFile(EvalDownloader::getTestOk($this->task, $testNo), $this->task->id . '.ok');
    $dir = $this->getTestSaveDir($testNo);
    $outFile = $dir . $this->task->id . '.out';
    if (file_exists($outFile)) {
      $iso->pushFile($outFile);
    }
    $iso->setTimeLimit(Config::EVAL_GRADER_TIME_LIMIT);
    $iso->setMemoryLimit(Config::EVAL_GRADER_MEMORY_LIMIT);
    $gres = $iso->run('./grader', '', [], []);

    if (!$gres->success()) {
      log_print("Test $testNo: Task eval failed");
      throw new EvalTaskOwnerError(
        "A apărut o eroare în rularea evaluatorului pe testul $testNo: " .
        "{$gres->message} ({$gres->time} s, {$gres->memory} kB).");
    }

    $this->copyGraderOutputFiles($iso, $testNo);

    $stdout = trim($iso->getStdout());
    if ($stdout === '' || !is_whole_number($stdout)) {
      log_print("Test $testNo: Task eval score broken or empty.");
      throw new EvalTaskOwnerError(
        "Evaluatorul nu a returnat un număr la stdout " .
        "pe testul $testNo (se ignoră spații, newline, etc.).");
    }
    $score = (int)$stdout;
    if ($score < 0 || $score > Config::EVAL_MAX_SCORE) {
      log_print("Test $testNo: Invalid score returned by evaluator");
      throw new EvalTaskOwnerError(
        "Evaluatorul a returnat un scor invalid ({$score}).");
    }

    $msg = trim($iso->getStderr());
    if (($msg == '') || (strlen($msg) > Config::EVAL_MAX_GRADER_MESSAGE)) {
      log_print("Test $testNo: Task eval message broken");
      throw new EvalTaskOwnerError(
        'Evaluatorul a returnat un mesaj gol sau mai lung de ' .
        Config::EVAL_MAX_GRADER_MESSAGE . ' de caractere la stdout');
    }

    log_print("Test $testNo: Eval gave {$score} points and said {$msg}");
    return new EvalTestResult(
      $score, $msg, $res->time * 1000, $res->memory, $gres->time * 1000, $gres->memory);
  }

  // Judge the correctness of the output data
  private function judgeOutput(int $testNo, IsolateResult $res): EvalTestResult {
    $this->downloadOkFile($testNo);

    if ($this->task->evaluator) {
      return $this->runCustomGrader($testNo, $res);
    } else {
      return $this->runDiffGrader($testNo, $res);
    }
  }

  // Run the user's submission on a single test case and judge the results.
  private function judgeTest(int $testNo): EvalTestResult {
    $result = $this->runTest($testNo);

    if ($result->success()) {
      return $this->judgeOutput($testNo, $result);
    } else {
      $fmt = 'Test %d: User program failed: %d ms, %d kb, isolate message %s';
      log_print(sprintf($fmt, $testNo, $result->time, $result->memory, $result->message));

      return new EvalTestResult(
        0,
        $result->message,
        $result->time * 1000,
        $result->memory,
        null,
        null);
    }
  }

  private function judgeTestGroups(): void {
    $groups = $this->task->getTestGroups();

    foreach ($groups as $groupIndex => $group) {
      $solvedGroup = true;
      $groupScore = 0;

      foreach ($group as $testNo) {
        $res = $this->judgeTest($testNo);
        $this->result->testResults[$testNo] = $res;
        job_test_update($this->job->id, $testNo, $groupIndex + 1, $res);
        $solvedGroup &= ($res->score > 0);
        $groupScore += $res->score;
      }

      if ($solvedGroup) {
        $this->result->score += $groupScore;
      }
    }
  }

  private function finalize(): void {
    $this->result->setComplete();
    if (($this->result->score < 0) ||
        ($this->result->score > Config::EVAL_MAX_SCORE)) {
      throw new EvalTaskOwnerError(
        'Evaluatorul a returnat un scor invalid.');
    }
  }

  public function grade() {
    try {
      $this->result->setPending();
      $this->cleanSaveDir();
      $this->compileUserSource();
      $this->compileGrader();
      $this->judgeTestGroups();
      $this->finalize();
    } catch (IsolateSandboxException $e) {
      throw new EvalSystemError($e->getMessage());
    }

    return $this->result;
  }

}
