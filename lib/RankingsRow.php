<?php

class RankingsRow {
  public int $rank;
  public User $user;
  public array $scores; // floats
  public float $total;

  function __construct(int $rank, User $user, array $scores, float $total) {
    $this->rank = $rank;
    $this->user = $user;
    $this->scores = $scores;
    $this->total = $total;
  }
}
