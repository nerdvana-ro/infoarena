<?php

class MacroArgs {
  private array $args;

  function __construct(array $args) {
    $this->args = $args;
  }

  function get(string $key, string $default = ''): string {
    return $this->args[$key] ?? $default;
  }

  // Returns true for truish values (true, 1, 'true').
  function getBool(string $key, bool $default = false): bool {
    return Util::toBool($this->args[$key] ?? $default);
  }
}
