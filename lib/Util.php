<?php

class Util {

  static function redirect($location, $statusCode = 301): void {
    FlashMessage::saveToSession();
    header("Location: $location", true, $statusCode);
    exit;
  }

  static function redirectToHome(): void {
    self::redirect(Config::URL_PREFIX);
  }

  static function redirectToLogin(): void {
    if (!empty($_POST)) {
      Session::set('postData', $_POST);
    }
    FlashMessage::addWarning('Pentru această operație este nevoie să te autentifici.');
    Session::set('REAL_REFERRER', $_SERVER['REQUEST_URI']);
    self::redirect(Config::URL_PREFIX . 'login');
  }

  static function redirectToReferrer(): void {
    $referrer = self::getReferrer();
    if ($referrer) {
      self::redirect($referrer);
    } else {
      self::redirectToHome();
    }
  }

  // Redirects to the same page, stripping any GET parameters but preserving
  // any slash-delimited arguments.
  static function redirectToSelf(): void {
    $uri = $_SERVER['REQUEST_URI'];
    $path = parse_url($uri, PHP_URL_PATH);
    self::redirect($path);
  }

  // Looks up the referrer in $_REQUEST, then in $_SESSION, then in $_SERVER.
  // We sometimes need to pass the referrer in $_SESSION because PHP redirects
  // (in particular redirects to the login page) lose the referrer.
  static function getReferrer(): ?string {
    $referrer = Request::get('referrer');

    if (!$referrer) {
      $referrer = Session::get('REAL_REFERRER') ?? $_SERVER['HTTP_REFERER'] ?? null;
    }

    Session::unsetVar('REAL_REFERRER');
    return $referrer;
  }

  static function toBool(mixed $x): bool {
    return filter_var($x, FILTER_VALIDATE_BOOLEAN);
  }

  private static function trimPageRange(
    int $first, int $last, int $numPages): array {

    return [ max($first, 1), min($last, $numPages) ];
  }

  private static function pushPageRange(array& $ranges, array $new) {
    $last = &$ranges[count($ranges) - 1];
    if ($last[1] + 1 >= $new[0]) {
      $last[1] = $new[1];
    } else {
      $ranges[] = $new;
    }
  }

  /**
   * Determines the range of page links to display.
   *
   * @param int $n Total number of pages
   * @param int $k Current page
   * @return pair[] An array of ranges, [first, last]
   *
   * Example: $n = 100, $k = 20 => returns [[1,5], [15,25], [96,100]]
   */
  static function getPaginationRange($n, $k) {
    $NUM_FIRST = 3;
    $NUM_MIDDLE = 5;

    $beginning = self::trimPageRange(1, $NUM_FIRST, $n);
    $middle = self::trimPageRange($k - $NUM_MIDDLE, $k + $NUM_MIDDLE, $n);
    $end = self::trimPageRange($n - $NUM_FIRST + 1, $n, $n);

    $result = [ $beginning ];
    self::pushPageRange($result, $middle);
    self::pushPageRange($result, $end);

    return $result;
  }
}
