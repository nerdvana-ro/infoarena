<?php

class Request {

  static function get(string $name, $default = '') {
    // PHP does this to submitted variable names...
    // https://www.php.net/manual/en/language.variables.external.php
    $name = str_replace('.', '_', $name);

    return $_REQUEST[$name] ?? $default;
  }

  static function getInt(string $name, $default = 0): int {
    return (int)self::get($name, $default);
  }

  static function getFloat(string $name, $default = 0): float {
    return (float)self::get($name, $default);
  }

  static function getBool(string $name, $default = false): bool {
    return Util::toBool(self::get($name, $default));
  }

  static function has(string $name): bool {
    return array_key_exists($name, $_REQUEST);
  }

  /* Use when the parameter is expected to have array type. */
  static function getArray(string $name): array {
    return self::get($name, []);
  }

  /* Returns an array of values from a parameter in CSV format. We use
   * underscore (_) because the comma is reserved. */
  static function getCsv($name) {
    $val = self::get($name);
    return preg_split('/_/', $val, -1, PREG_SPLIT_NO_EMPTY);
  }

  static function isWeb(): bool {
    return php_sapi_name() != 'cli';
  }

  static function isPost(): bool {
    return $_SERVER['REQUEST_METHOD'] == 'POST';
  }

}
