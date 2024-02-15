<?php

class AlgorithmTagTree extends SearchTagTree {

  function __construct(array $tagIds, string $pag) {
    $tags = Tag::getWithCounts(['algorithm', 'method'], $tagIds);
    foreach ($tags as $tag) {
      if (!$tag->parent) {
        $sel = in_array($tag->id, $tagIds);
        $this->roots[$tag->id] = new SearchTagNode($tag, null, $sel);
      }
    }

    foreach ($tags as $tag) {
      if ($tag->parent) {
        $sel = in_array($tag->id, $tagIds);
        $this->roots[$tag->parent]->addChild($tag, $sel);
      }
    }

    parent::__construct($tagIds, $pag);
  }
}
