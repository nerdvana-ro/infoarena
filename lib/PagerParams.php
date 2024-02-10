<?php

/**
 * Parameters for a paginated and sortable table. Controlled via GET arguments.
 */
abstract class PagerParams {
  const SORT_PAG_REGEXP = '/^([_a-z0-9]+)-(asc|desc)-([0-9]+)-([0-9]+)$/i';
  const SORT_REGEXP = '/^([a-z0-9]+)-(asc|desc)$/i';

  public int $pageNo;
  public int $pageSize;
  public string $sortField;
  public bool $sortAsc;
  public bool $showPagination;

  function __construct() {
    $this->sortField = $this->getDefaultSortField();
    $this->sortAsc = $this->getDefaultSortAsc();
    $this->pageSize = Config::PAGE_SIZE;
    $this->pageNo = 1;

    $info = Request::get('pag');
    if (preg_match(self::SORT_PAG_REGEXP, $info, $matches)) {
      $this->sortField = $matches[1];
      $this->sortAsc = ($matches[2] == 'asc');
      $this->pageSize = $matches[3];
      $this->pageNo = $matches[4];
    } else if (preg_match(self::SORT_REGEXP, $info, $matches)) {
      $this->sortField = $matches[1];
      $this->sortAsc = ($matches[2] == 'asc');
    }
  }

  abstract function getDefaultSortField(): string;
  abstract function getDefaultSortAsc(): bool;

  function toString(): string {
    $dir = $this->sortAsc ? 'asc' : 'desc';
    if ($this->showPagination) {
      return sprintf('%s-%s-%s-%s',
                     $this->sortField, $dir, $this->pageSize, $this->pageNo);
    } else {
      return sprintf('%s-%s', $this->sortField, $dir);
    }
  }
}
