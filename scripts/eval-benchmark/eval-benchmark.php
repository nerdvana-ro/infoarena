<?php

require_once __DIR__ . '/../../Config.php';
require_once __DIR__ . '/../../common/common.php';
require_once __DIR__ . '/../../common/log.php';
require_once __DIR__ . '/../../eval/Exceptions.php';
require_once __DIR__ . '/../../lib/Core.php';

spl_autoload_register(function($className) {
  $fileName = sprintf('%s/%s.php', __DIR__, $className);
  if (file_exists($fileName)) {
    require_once $fileName;
  }
});

// For example, we don't want commas as decimal delimiters.
setlocale(LC_ALL, 'en_US.utf8');

$main = new Main();
try {
  $main->run();
} catch (BException $e) {
  Log::fatal($e->getMessage(), $e->getArgs());
}
