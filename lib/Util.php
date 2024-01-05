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

  /**
   * Determines the range of page links to display.
   *
   * @param int $n Total number of pages
   * @param int $k Current page
   * @return int[] An array of two elements, the left and right end of the range.
   *
   * Example: $n = 100, $k = 20 => returns [18, 22]
   */
  static function getPaginationRange($n, $k) {
    // By default display two pages left and two pages right of $k
    $l = max($k - 2, 1);
    $r = min($k + 2, $n);

    // Extend while needed and while there is room to extend on either side.
    while (($r - $l < 4) && ($r - $l < $n - 1)) {
      if ($l == 1) {
        $r++;
      } else {
        $l--;
      }
    }

    return [$l, $r];
  }
}
