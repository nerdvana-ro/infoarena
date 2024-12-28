<?php

class EvalDownloader {

  private static function getDir(string $page): string {
    return sprintf('%seval/grader_cache/%s/', Config::ROOT, $page);
  }

  private static function getFullPath(string $page, string $file): string {
    return self::getDir($page) . $file;
  }

  // Returns true iff we have this file and it matches the date and size.
  private static function inCache(string $fileName, string $date, int $size): bool {
    clearstatcache();

    $timestamp = db_date_parse($date);
    $cachedMtime = @filemtime($fileName);
    $cachedSize = @filesize($fileName);

    return
      ($cachedMtime !== false) &&
      ($cachedMtime >= $timestamp) &&
      ($cachedSize !== false) &&
      ($cachedSize == $size);
  }

  private static function downloadOnce(string $page, string $file): bool {
    $full = self::getFullPath($page, $file);
    $cachefd = fopen($full, 'wb');
    if (!$cachefd) {
      log_warn("Failed to open $full for writing.");
      return false;
    }

    // Can't use url_attachment here because it's in www.
    $url = Config::URL_HOST . Config::URL_PREFIX . "$page?action=download&file=$file";

    $curl = curl_init();
    curl_setopt_array($curl, [
      CURLOPT_URL => $url,
      CURLOPT_USERPWD => Config::EVAL_USERNAME . ":" . Config::EVAL_PASSWORD,
      CURLOPT_FILE => $cachefd,
      CURLOPT_FAILONERROR => true,
    ]);

    if (!curl_exec($curl)) {
      log_warn("Failed curl download for $page/$file.");
      log_warn('Curl says: ' . curl_error($curl));
      return false;
    }
    curl_close($curl);
    if (!fclose($cachefd)) {
      log_warn("Failed closing $full");
      return false;
    }

    clearstatcache();
    $mtime = @filemtime($full);
    $size = @filesize($full);
    log_print("Downloaded new file $page/$file, mtime $mtime size $size");

    return true;
  }

  private static function download(string $page, string $file): bool {
    $attempt = 0;
    $result = false;

    do {
      $attempt++;
      $result = self::downloadOnce($page, $file);
      if (!$result) {
        log_print('Failed downloading grader file... sleep and retry.');
        sleep(1);
      }
    } while (!$result && $attempt < Config::EVAL_DOWNLOAD_RETRIES);

    return $result;
  }

  private static function ensure(string $page, string $file): bool {
    $page = normalize_page_name($page);

    // Get attachment from database.
    $att = attachment_get($file, $page);
    if (!$att) {
      log_warn("Attachment $page/$file not found.");
      return false;
    }

    $dir = self::getDir($page);
    @mkdir($dir, 0700, true);

    // My cached version timestamp
    $full = self::getFullPath($page, $file);

    if (self::inCache($full, $att['timestamp'], $att['size'])) {
      log_print("Using cached $page/$file.");
      return true;
    } else {
      log_print("Downloading $page/$file...");
      return self::download($page, $file);
    }
  }

  static function saveFile(string $page, string $file, ?string $target): bool {
    $file = 'grader_' . $file;

    if (!self::ensure($page, $file)) {
      log_warn("Failed downloading grader file $page/$file");
      return false;
    }

    if ($target) {
      $full = self::getFullPath($page, $file);
      if (!copy($full, $target)) {
        log_warn("Failed copying $full to $target");
        return false;
      }
    }

    return true;
  }

  static function saveGrader(Task $task, string $target): bool {
    return self::saveFile($task->page_name, $task->evaluator, $target);
  }

  static function saveTestInput(Task $task, int $test, string $target): bool {
    $file = "test{$test}.in";
    return self::saveFile($task->page_name, $file, $target);
  }

  static function saveTestOk(Task $task, int $test, ?string $target): bool {
    $file = "test{$test}.ok";
    return self::saveFile($task->page_name, $file, $target);
  }

  static function ensureTestOk(Task $task, int $test): bool {
    return self::saveTestOk($task, $test, null);
  }

  static function getTestIn(Task $task, int $test): string {
    $file = "grader_test{$test}.in";
    return self::getFullPath($task->page_name, $file);
  }

  static function getTestOk(Task $task, int $test): string {
    $file = "grader_test{$test}.ok";
    return self::getFullPath($task->page_name, $file);
  }
}
