<?php

class IsolateJail {
  private float $timeLimit;     // seconds
  private float $wallTimeLimit; // seconds
  private int $memoryLimit;     // kilobytes
  private bool $processLimit = true;

  function __construct() {
    $this->exec(['--cg', '--cleanup']);
    $this->exec(['--cg', '--init']);
  }

  private function exec(array $args): void {
    $argStr = implode(' ', $args);
    $cmd = sprintf('%s %s', Config::ISOLATE_COMMAND, $argStr);
    log_print("Running $cmd");
    exec($cmd);
  }

  function setTimeLimit(float $limit): void {
    $this->timeLimit = $limit;
    $this->wallTimeLimit = $limit + Config::ISOLATE_EXTRA_WALL_TIME_SEC;
  }

  function setMemoryLimit(int $limit): void {
    $this->memoryLimit = $limit;
  }

  function unlimitProcesses(): void {
    $this->processLimit = false;
  }

  private function fullPath(string $fileName): string {
    return Config::ISOLATE_BOX . $fileName;
  }

  function writeFile(string $fileName, string $contents): void {
    $fileName = $this->fullPath($fileName);
    $res = file_put_contents($fileName, $contents);
    if ($res === false) {
      throw new IsolateJailException('Could not write file inside isolate box.');
    }
  }

  private function copyWithPermissions(string $src, string $dest): void {
    if (!@copy($src, $dest)) {
      throw new IsolateJailException('Could not copy file to/from isolate box.');
    }
    $perms = fileperms($src);
    if ($perms === false) {
      throw new IsolateJailException('Could not access source file permissions.');
    }
    if (!chmod($dest, $perms)) {
      throw new IsolateJailException('Could not set file permissions.');
    }
  }

  function pushFile(string $fullSrc, string $dest = ''): void {
    if (!$dest) {
      $dest = basename($fullSrc);
    }
    $fullDest = $this->fullPath($dest);
    $this->copyWithPermissions($fullSrc, $fullDest);
  }

  function pullFile(string $src, string $fullDest): void {
    $fullSrc = $this->fullPath($src);
    $this->copyWithPermissions($fullSrc, $fullDest);
  }

  private function getMeta(): array {
    $result = [];

    $lines = file(Config::ISOLATE_META_FILE);
    foreach ($lines as $line) {
      $line = trim($line);
      $parts = explode(':', $line, 2);
      $result[$parts[0]] = $parts[1];
    }

    return $result;
  }

  function getResult(): IsolateResult {
    $meta = $this->getMeta();
    $pageCacheSize = Cgroups::getPageCacheSize();

    $exitCode = $meta['exitcode'] ?? 0;
    $signal = $meta['exitsig'] ?? 0;
    $memory = $meta['cg-mem'] ?? 0;
    log_print("Memory: {$memory} kb of which {$pageCacheSize} kb page cache.");
    $memory = max($memory - $pageCacheSize, 0);
    $time = $meta['time'] ?? 0;
    $wallTime = $meta['time-wall'] ?? 0;

    if ($time > $this->timeLimit) {
      $st = IsolateResult::TLE;
      $msg = 'Time limit exceeded.';
    } else if ($wallTime > $this->wallTimeLimit) {
      $st = IsolateResult::WALL_TLE;
      $msg = 'Wall time limit exceeded.';
    } else if (($memory > $this->memoryLimit) || isset($meta['cg-oom-killed'])) {
      $st = IsolateResult::MLE;
      $msg = 'Memory limit exceeded.';
    } else if ($signal || isset($meta['killed'])) {
      $st = IsolateResult::KILLED_BY_SIGNAL;
      $msg = $signal ? "Killed by signal {$signal}." : "Killed.";
    } else if ($exitCode) {
      $st = IsolateResult::NONZERO_EXIT_STATUS;
      $msg = "Nonzero exit status: {$exitCode}.";
    } else {
      $st = IsolateResult::SUCCESS;
      $msg = 'OK.';
    }

    return new IsolateResult($st, $msg, $memory, $time, $wallTime);
  }

  function run(string $cmd, string $cmdArgs, array $mounts,
               array $env): IsolateResult {
    if (!$this->timeLimit || !$this->memoryLimit) {
      throw new IsolateJailException('Missing time or memory limit.');
    }

    $time = number_format($this->timeLimit, 3); // sprintf is locale-aware and uses ','
    $wallTime = number_format($this->wallTimeLimit, 3);
    $memory = $this->memoryLimit + Config::ISOLATE_PAGE_CACHE_MEMORY;

    $args = [
      '--cg',
      '--run',
      '-E PATH=/usr/bin',
      '-D',
      '--dir /box=./box:rw',
      '-t ' . $time,
      '-w ' . $wallTime,
      '--cg-mem ' . $memory,
      '-M ' . Config::ISOLATE_META_FILE,
      '-o ' . Config::ISOLATE_STDOUT,
      '-r ' . Config::ISOLATE_STDERR,
    ];
    if (!$this->processLimit) {
      $args[] = '-p';
    }
    foreach ($mounts as $m) {
      $args[] = '-d ' . $m;
    }
    foreach ($env as $var => $value) {
      $args[] = "-E {$var}={$value}";
    }
    $args[] = '--';
    $args[] = $cmd;
    $args[] = $cmdArgs;
    $this->exec($args);
    return $this->getResult();
  }

  private function getStream(string $filename): string {
    $full = $this->fullPath($filename);
    $data = file_get_contents($full);
    if ($data === false) {
      throw new IsolateJailException('Could not read stdout from isolate.');
    }
    return $data;
  }

  function getStdout(): string {
    return $this->getStream(Config::ISOLATE_STDOUT);
  }

  function getStderr(): string {
    return $this->getStream(Config::ISOLATE_STDERR);
  }
}
