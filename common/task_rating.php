<?php

require_once(Config::ROOT."common/db/task_rating.php");
require_once(Config::ROOT."common/rating.php");

// Computes the rating out of the array $ratings
// $ratings contains arrays of ratings
// TODO: Pass a single triplet of ratings.
function task_rating_compute($ratings): float {
  if (empty($ratings)) {
    return 0;
  }

  $sum = ($ratings[0]['idea'] ?? 0) +
    ($ratings[0]['theory'] ?? 0) +
    ($ratings[0]['coding'] ?? 0);

  if (!$sum) {
    return 0.0;
  }

  // Scores of 1/1/1 correspond to ½ star. Scores of MAX/MAX/MAX correspond to
  // SCALE stars. Do the math.
  $rating = 0.5 + (StarRating::SCALE - 0.5) * ($sum - 3) /
    (3 * TaskRatings::MAX_VALUE - 3);
  return $rating;
}

// Checks to see if a value is an int between 1 and 10.
function task_is_rating_value($rating_value) {
  if (!is_whole_number($rating_value)) {
    return false;
  }

  $int_rating_value = intval($rating_value);
  if ($int_rating_value < 1 || $int_rating_value > TaskRatings::MAX_VALUE) {
    return false;
  }

  return true;
}

?>
