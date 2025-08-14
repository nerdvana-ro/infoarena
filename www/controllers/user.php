<?php

require_once Config::ROOT . 'common/textblock.php';
require_once Config::ROOT . 'common/db/textblock.php';
require_once Config::ROOT . 'common/db/user.php';

// View user profile (personal page, rating evolution, statistics)
// $action is one of (view | rating | stats)
function controller_user_view(string $username, ?int $revision = null) {
  // validate username
  $user = resolve_user($username);

  // Build view.
  $pageName = Config::USER_TEXTBLOCK_PREFIX . $user->username;
  $numRevisions = textblock_get_revision_count($pageName);

  if ($revision == $numRevisions) {
    // Get rid of the ?revision=... GET argument.
    Util::redirectToSelf();
  }

  if ($revision) {
    if ($revision <= 0) {
      FlashMessage::addError("Revizia {$revision} este incorectă.");
      Util::redirectToSelf();
    }

    $textblock = textblock_get_revision($pageName, $revision);

    if (!$textblock) {
      FlashMessage::addError('Revizia "' . $revision . '" nu există.');
      Util::redirectToSelf();
    }

    FlashMessage::addTemplateWarning('revisionWarning.tpl', [
      'numRevisions' => $numRevisions,
      'revision' => $revision,
      'textblock' => $textblock,
    ]);
  } else {
    $textblock = textblock_get_revision($pageName);
  }

  Identity::enforceViewTextblock($textblock);

  $recentTitle = sprintf('Profil %s', $user->username);
  RecentPage::addCurrentPage($recentTitle);
  Smart::assign([
    'numRevisions' => $numRevisions,
    'revision' => $revision,
    'textblock' => $textblock,
    'user' => $user,
    'wikiHtml' => Wiki::processTextblock($textblock),
  ]);
  Smart::display('user/view.tpl');
}

function controller_user_view_rating(string $username): void {
  $user = resolve_user($username);

  $recentTitle = sprintf('Rating %s (%s)', $user->full_name, $user->username);
  RecentPage::addCurrentPage($recentTitle);
  Smart::assign([
    'rounds' => $user->getRatedRounds(),
    'user' => $user,
  ]);
  Smart::display('user/viewRating.tpl');
}

function controller_user_view_stats(string $username): void {
  $user = resolve_user($username);

  $recentTitle = sprintf('Statistici %s (%s)', $user->full_name, $user->username);
  RecentPage::addCurrentPage($recentTitle);
  Smart::assign([
    'rounds' => $user->getSubmittedRounds(),
    'solvedTasks' => $user->getArchiveTasks(true),
    'unsolvedTasks' => $user->getArchiveTasks(false),
    'user' => $user,
  ]);
  Smart::display('user/viewStats.tpl');
}

function resolve_user(string $username): User {
  $user = User::get_by_username($username);
  if (!$user) {
    FlashMessage::addError('Utilizator inexistent.');
    Util::redirectToHome();
  }
  if (Identity::isAdmin() && $user->banned) {
    FlashMessage::addWarning('Acest utilizator este blocat.');
  }
  return $user;
}
