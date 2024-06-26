<?php

require_once(Config::ROOT."common/db/user.php");
require_once(Config::ROOT."common/user.php");

// displays form to identify user. On submit it sends e-mail with confirmation
// link.
function controller_resetpass() {
  if (Identity::isLoggedIn()) {
    Util::redirectToHome();
  }

  // `data` dictionary is a dictionary with data to be displayed by form view
  $data = array();

  // here we store validation errors.
  // It is a dictionary, indexed by field names
  $errors = array();

  // submit?
  $submit = Request::isPost();

  if ($submit) {
    // 1. validate
    // check username
    $data['username'] = getattr($_POST, 'username');
    $data['email'] = getattr($_POST, 'email');
    if ($data['username']) {
      $user = user_get_by_username($data['username']);
      if (!$user) {
        $errors['username'] = 'Nu există niciun utilizator cu acest '
          .'nume de cont.';
      }
    }
    elseif ($data['email']) {
      $user = user_get_by_email($data['email']);
      if (!$user) {
        $errors['email'] = 'Nu există niciun utilizator cu această adresă '
          .'de e-mail.';
      }
    }

    if (isset($user) && $user) {
      // user was found

      // confirmation code
      $cpass = user_resetpass_key($user);

      // confirmation link
      $clink = url_absolute(url_resetpass_confirm($user['username'], $cpass));

      // email user
      $from = Config::CONTACT_EMAIL;
      $to = $user['email'];
      $subject = 'Recuperează utilizatorul și parola';
      Smart::assign([
        'user' => $user,
        'clink' => $clink,
      ]);
      $body = Smart::fetch('email/lostPassword.tpl');
      Mailer::send($from, [$to], $subject, $body);

      // notify user
      FlashMessage::addSuccess('Ți-am trimis instrucțiuni prin e-mail.');
      redirect(url_login());
    }
    else {
      FlashMessage::addError('Trebuie să completezi cel puțin unul din câmpuri.');
    }
  }
  else {
    // initial display of form
  }

  // page title
  $view = array();
  $view['title'] = 'Recuperare parolă';
  $view['form_errors'] = $errors;
  $view['form_values'] = $data;
  $view['no_sidebar_login'] = true;
  execute_view_die('views/resetpass.php', $view);
}

// checks confirmation code and resets password
function controller_resetpass_confirm($username) {
  if (Identity::isLoggedIn()) {
    Util::redirectToHome();
  }

  $cpass = request('c');

  // validate username
  if ($username) {
    $user = user_get_by_username($username);
  }
  if (!$user) {
    FlashMessage::addError('Numele de utilizator este invalid.');
    Util::redirectToHome();
  }

  // validate confirmation code
  if ($cpass != user_resetpass_key($user)) {
    FlashMessage::addError('Codul de confirmare nu este corect.');
    Util::redirectToHome();
  }

  // reset password
  $new_password = sha1(mt_rand(1000000, 9999999).Config::RESET_PASSWORD_SALT);
  $new_password = substr($new_password, 0, 6);
  $user['password'] = user_hash_password($new_password, $user['username']);
  user_update($user);

  // send email with new password
  $from = Config::CONTACT_EMAIL;
  $to = $user['email'];
  $subject = 'Parola nouă';
  Smart::assign([
    'new_password' => $new_password,
    'username' => $user['username'],
    'clink' => url_login(),
  ]);
  $body = Smart::fetch('email/newPassword.tpl');
  Mailer::send($from, [$to], $subject, $body);

  // notify user
  FlashMessage::addSuccess('Ți-am resetat parola și ți-am trimis-o prin e-mail.');
  redirect(url_login());
}
