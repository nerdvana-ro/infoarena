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

  // Returns the relative URL and the query string.
  static function addUrlParameter(string $url, string $name, string $value): string {
    $path = parse_url($url, PHP_URL_PATH);
    $str = parse_url($url, PHP_URL_QUERY) ?? '';
    parse_str($str, $args);

    $args[$name] = $value;
    $args = array_filter($args); // Remove parameters with empty values.
    $query = http_build_query($args);
    return $query ? ($path . '?' . $query) : $path;
  }

  static function addRequestParameter(string $name, string $value): string {
    $uri = $_SERVER['REQUEST_URI'];
    return self::addUrlParameter($uri, $name, $value);
  }

  static function toBool(mixed $x): bool {
    return filter_var($x, FILTER_VALIDATE_BOOLEAN);
  }
}
