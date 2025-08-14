<?php

require_once __DIR__ . '/../common/rating.php';
require_once __DIR__ . '/../www/format/format.php';

class RatingBadge {

  private User $user;
  private float $rating;

  function __construct(User $user, float $rating) {
    $this->user = $user;
    $this->rating = rating_scale($rating);
  }

  function getUser(): User {
    return $this->user;
  }

  function getUsername(): string {
    return $this->user->username;
  }

  function getRating(): float {
    return $this->rating;
  }

  function getRatingClass(): int {
    $rec = rating_group($this->rating, $this->user->isAdmin());
    return $rec['group'];
  }
}
