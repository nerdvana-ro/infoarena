<?php

/**
 * A two-layered tag tree augmented with a couple more fields. Used in the
 * task tag search.
 */

abstract class SearchTagTree {
  public array $roots = [];

  function __construct(array $tagIds, string $pag) {
    foreach ($this->roots as $root) {
      $root->computeUrl($tagIds, $pag);
    }
  }
}
