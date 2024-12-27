<?php

/**
 * This class can take a source file, compile it in an isolate jail and copy
 * the resulting binary to a given destination.
 **/

class Compiler {
  // Full paths to source and resulting binary.
  private string $source;
  private string $binary;
  private string $stdout;
  private string $stderr;

  private string $compilerId;

  // Result data
  private bool $success;
  private string $message;

  function __construct(string $source, string $binary, string $stdout, string $stderr) {
    $this->source = $source;
    $this->binary = $binary;
    $this->stdout = $stdout;
    $this->stderr = $stderr;
    // guess for now; can be overwritten later
    $this->compilerId = Str::getExtension($source);
  }

  function success(): bool {
    return $this->success;
  }

  function getMessage(): string {
    return $this->message;
  }

  private function setMessage(string $stdout, string $stderr): void {
    $s = $stdout . $stderr;
    $lines = explode("\n", $s);
    $lines = array_slice($lines, 0, 50);
    $this->message = implode("\n", $lines);
  }

  function setCompilerId(string $compilerId): void {
    $this->compilerId = $compilerId;
  }

  function run(): void {
    $comp = Config::COMPILERS[$this->compilerId] ?? null;
    if (!$comp) {
      throw new EvalSystemError(
        "Nu am putut să determin compilatorul pentru extensia {$this->compilerId}.");
    }

    $isoSrc = basename($this->source);
    $isoBin = basename($this->binary);

    $cmd = $comp['cmd'];
    $cmd = str_replace('%src%', $isoSrc, $cmd);
    $cmd = str_replace('%bin%', $isoBin, $cmd);

    $iso = new IsolateJail();
    $iso->unlimitProcesses();
    $iso->setTimeLimit(Config::EVAL_COMPILE_TIME_LIMIT);
    $iso->setMemoryLimit(Config::EVAL_COMPILE_MEMORY_LIMIT);
    $iso->pushFile($this->source, $isoSrc);

    $res = $iso->run($cmd, '', $comp['mounts'], []);
    $this->success = $res->success();
    $this->setMessage($iso->getStdout(), $iso->getStderr());

    if ($this->success) {
      $iso->pullFile($isoBin, $this->binary);
      $iso->pullFile(Config::ISOLATE_STDOUT, $this->stdout);
      $iso->pullFile(Config::ISOLATE_STDERR, $this->stderr);
    }
  }
}
