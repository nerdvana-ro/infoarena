<?php

class Cgroups {
  // Poll for 5 seconds total.
  const int POLL_MILLIS = 50;
  const int POLL_TIMEOUT = 5_000;

  static function getKey(string $file, string $key): int {
    $lines = file($file);
    foreach ($lines as $line) {
      $line = trim($line);
      $parts = explode(' ', $line, 2);
      if ($parts[0] == $key) {
        // log_print("Read cgroup key {$file}:{$key} = {$parts[1]}.");
        return $parts[1];
      }
    }
    return 0;
  }

  // Poll until we get the same nonzero value three times in a row.
  static function pollForKey(string $file, string $key): int {
    $cnt = 0;
    $val2 = 0;
    $val1 = 0;
    $val0 = 0;

    do {
      usleep(self::POLL_MILLIS * 1_000); // convert to microseconds
      $val2 = $val1;
      $val1 = $val0;
      $val0 = self::getKey($file, $key);
      $cnt++;
    } while (($cnt * self::POLL_MILLIS <= self::POLL_TIMEOUT) &&
             (($val0 == 0) || ($val0 != $val1) || ($val1 != $val2)));

    $millis = $cnt * self::POLL_MILLIS;
    log_print("Cgroup key {$file}:{$key} = {$val0} stabilized in $millis ms.");

    return $val0;
  }

  // Returns the page cache size in kB.
  static function getPageCacheSize(): int {
    $bytes = self::pollForKey(Config::CGROUP_MEMORY_STAT_FILE, 'file');
    return $bytes >> 10;
  }
}
