<?php

class AuthorTagTree extends SearchTagTree {

  const AUTHOR_BRACKETS = [
    ['A', 'C'],
    ['D', 'I'],
    ['J', 'N'],
    ['O', 'Ș'],
    ['T', 'Z'],
  ];

  function __construct(array $tagIds, string $pag) {
    $tags = Tag::getWithCounts(['author'], $tagIds);
    foreach ($tags as $tag) {
      $sel = in_array($tag->id, $tagIds);
      $bracket = $this->getBracket($tag);
      $this->ensureBracket($bracket);
      $this->roots[$bracket]->addChild($tag, $sel);
    }

    parent::__construct($tagIds, $pag);
  }

  function ensureBracket(int $index): void {
    if (!isset($this->roots[$index])) {
      $bracket = self::AUTHOR_BRACKETS[$index];
      $tagName = sprintf('Autori (%s-%s)', $bracket[0], $bracket[1]);
      $tag = Model::factory('Tag')->create();
      $tag->name = $tagName;
      $this->roots[$index] = new SearchTagNode($tag, null, false);
    }
  }

  function getBracket(Tag $tag): int {
    $i = 0;
    $letter = mb_strtoupper(mb_substr($tag->name, 0, 1));
    while (($i < count(self::AUTHOR_BRACKETS) - 1) &&
           (strcoll($letter, self::AUTHOR_BRACKETS[$i][1]) > 0)) {
      $i++;
    }
    return $i;
  }

}
