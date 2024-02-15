<?php

/**
 * A node in a SearchTagTree.
 **/

class SearchTagNode {
  public Tag $tag;
  public bool $isSelected;
  public bool $hasSelectedChildren = false;
  public ?SearchTagNode $parent;
  public array $children = [];
  public string $url;

  function __construct(Tag $tag, ?SearchTagNode $parent, bool $isSelected) {
    $this->tag = $tag;
    $this->parent = $parent;
    $this->isSelected = $isSelected;
  }

  function addChild(Tag $tag, bool $isSelected): void {
    $this->children[] = new SearchTagNode($tag, $this, $isSelected);
    $this->hasSelectedChildren |= $isSelected;
  }

  // Given the tag IDs that this tree was generated with, compute the target
  // URL should the user click on this node.
  function computeUrl(array $tagIds, string $pag): void {
    foreach ($this->children as $child) {
      $child->computeUrl($tagIds, $pag);
    }

    if (!$this->tag->id) {
      $this->url = '';
    } else {
      $this->computeUrlProper($tagIds, $pag);
    }
  }

  function computeUrlProper(array $tagIds, string $pag): void {
    if ($this->isSelected) {
      $tagIds = array_diff($tagIds, [ $this->tag->id ]);
    } else {
      // When selecting a parent, remove all the children. When selecting a
      // child, remove its parent.
      $tagIds[] = $this->tag->id;
      $tagIds = $this->removeParentAndChildTags($tagIds);
    }

    $url = url_task_search($tagIds);
    $this->url = Util::addUrlParameter($url, 'pag', $pag);
  }

  private function removeParentAndChildTags(array $tagIds): array {
    $toRemove = [];
    if ($this->parent) {
      $toRemove[] = $this->parent->tag->id ?? 0;
    }
    foreach ($this->children as $child) {
      $toRemove[] = $child->tag->id;
    }
    return array_diff($tagIds, $toRemove);
  }

  function getCssClass(): string {
    if ($this->isSelected) {
      return 'selected-filter';
    } else if ($this->hasSelectedChildren) {
      return 'sub-selected-filters';
    } else {
      return '';
    }
  }

  function isFake(): bool {
    return $this->tag->id === null;
  }

  function showCounts(): bool {
    return
      !$this->isFake() &&
      !$this->isSelected &&
      !$this->hasSelectedChildren;
  }
}
