<?php

require_once(Config::ROOT."common/db/round.php");
require_once(Config::ROOT."www/format/pager.php");

// Displays form to register remote user to given round_id
function controller_round_register($round_id) {
  if (!is_round_id($round_id)) {
    FlashMessage::addError("Identificatorul rundei este invalid.");
    Util::redirectToHome();
  }
  $round = round_get($round_id);

  // check round_id & permissions
  if ($round) {
    Identity::enforceRegisterForRound($round);
  } else {
    FlashMessage::addError('Runda specificată nu există în baza de date.');
    Util::redirectToHome();
  }

  $user_is_registered = round_is_registered($round['id'], Identity::getId());

  if (Request::isPost()) {
    // Toggle user's registration status
    if (!$user_is_registered) {
      round_register_user($round['id'], Identity::getId());
      FlashMessage::addSuccess('Te-ai înregistrat la "'.$round['title'].'"!');
    } else {
      round_unregister_user($round['id'], Identity::getId());
      FlashMessage::addSuccess('Te-ai dezînregistrat de la "'.$round['title'].'"!');
    }
    redirect(url_textblock($round['page_name']));
  } else {
    // Display confirmation form
    $view = array(
      'round' => $round,
      'action' => url_round_register($round['id']),
    );

    if (!$user_is_registered) {
      $view["title"] = "Înregistrare la " . $round["title"];
      execute_view_die('views/round_register.php', $view);
    } else {
      $view["title"] = "Dezînregistrare de la " . $round["title"];
      execute_view_die('views/round_unregister.php', $view);
    }
  }
}

// Displays registered users to given round_id
function controller_round_register_view($round_id) {
  if (!is_round_id($round_id)) {
    FlashMessage::addError("Identificatorul rundei este invalid.");
    Util::redirectToHome();
  }
  $round = round_get($round_id);

  if (!$round) {
    FlashMessage::addError('Runda specificată nu există în baza de date.');
    Util::redirectToHome();
  }

  $options = pager_init_options();
  $view = array();
  $view['title'] = 'Utilizatori înregistrați la ' . $round['title'];
  $view['round'] = $round;
  $view['users'] = round_get_registered_users_range(
    $round['id'], $options['first_entry'], $options['display_entries']);
  $view['first_entry'] = $options['first_entry'];
  $view['total_entries'] =  round_get_registered_users_count($round['id']);
  $view['display_entries'] = $options['display_entries'];

  execute_view_die('views/round_register_view.php', $view);
}

?>
