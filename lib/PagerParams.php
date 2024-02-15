<?php

/**
 * Parameters for a paginated and sortable table. Controlled via GET arguments.
 */
abstract class PagerParams {
  const SORT_PAG_REGEXP = '/^([_a-z0-9]+)-(asc|desc)-([0-9]+)-([0-9]+)$/i';
  const SORT_REGEXP = '/^([a-z0-9]+)-(asc|desc)$/i';

  const RANGE_FIRST = 3;
  const RANGE_MIDDLE = 5;

  public int $page;
  public int $pageSize;
  public string $sortField;
  public bool $sortAsc;
  public bool $showPagination;

  public int $numResults; // To be set by the caller once known.

  function __construct() {
    $this->sortField = $this->getDefaultSortField();
    $this->sortAsc = $this->getDefaultSortAsc();
    $this->pageSize = Config::PAGE_SIZE;
    $this->page = 1;

    $info = Request::get('pag');
    if (preg_match(self::SORT_PAG_REGEXP, $info, $matches)) {
      $this->sortField = $matches[1];
      $this->sortAsc = ($matches[2] == 'asc');
      $this->pageSize = $matches[3];
      $this->page = $matches[4];
    } else if (preg_match(self::SORT_REGEXP, $info, $matches)) {
      $this->sortField = $matches[1];
      $this->sortAsc = ($matches[2] == 'asc');
    }
  }

  function getNumPages(): int {
    if ($this->showPagination) {
      return ceil($this->numResults / $this->pageSize);
    } else {
      return 1;
    }
  }

  function getFirstResult(): int {
    return ($this->page - 1) * $this->pageSize + 1;
  }

  function getLastResult(): int {
    return min($this->page * $this->pageSize, $this->numResults);
  }

  abstract function getDefaultSortField(): string;
  abstract function getDefaultSortAsc(): bool;

  private function valuesAreDefault(
    int $page, int $pageSize, string $sortField, bool $sortAsc): bool {

    return
      ($page == 1) &&
      ($pageSize == Config::PAGE_SIZE) &&
      ($sortField == $this->getDefaultSortField()) &&
      ($sortAsc == $this->getDefaultSortAsc());
  }

  private function composeArg(
    int $page, int $pageSize, string $sortField, bool $sortAsc): string {

    if ($this->valuesAreDefault($page, $pageSize, $sortField, $sortAsc)) {
      return '';
    }

    $dir = $sortAsc ? 'asc' : 'desc';
    if ($this->showPagination) {
      return sprintf('%s-%s-%s-%s',
                     $sortField, $dir, $pageSize, $page);
    } else {
      return sprintf('%s-%s', $sortField, $dir);
    }
  }

  function toString(): string {
    return $this->composeArg(
      $this->page, $this->pageSize, $this->sortField, $this->sortAsc);
  }

  function getArgForPage(int $page): string {
    return $this->composeArg(
      $page, $this->pageSize, $this->sortField, $this->sortAsc);
  }

  function getArgForColumn(string $col): string {
    $dir = ($col == $this->sortField)
      ? !$this->sortAsc
      : $this->getDefaultSortAsc();
    return $this->composeArg(1, $this->pageSize, $col, $dir);
  }

  function getArgForPageSize(int $pageSize): string {
    return $this->composeArg(1, $pageSize, $this->sortField, $this->sortAsc);
  }

  private function trimPageRange(
    int $first, int $last, int $numPages): array {

    return [ max($first, 1), min($last, $numPages) ];
  }

  private function pushPageRange(array& $ranges, array $new): void {
    $last = &$ranges[count($ranges) - 1];
    if ($last[1] + 1 >= $new[0]) {
      $last[1] = $new[1];
    } else {
      $ranges[] = $new;
    }
  }

  /**
   * Determines the ranges of page links to display.
   *
   * @return pair[] An array of ranges, [first, last]
   *
   * Example: 100 pages, $page = 20 => returns [[1,3], [15,25], [98,100]]
   */
  function getRanges(): array {
    $n = $this->getNumPages();

    $beginning = $this->trimPageRange(
      1,
      self::RANGE_FIRST,
      $n);

    $middle = $this->trimPageRange(
      $this->page - self::RANGE_MIDDLE,
      $this->page + self::RANGE_MIDDLE,
      $n);

    $end = $this->trimPageRange(
      $n - self::RANGE_FIRST + 1,
      $n,
      $n);

    $result = [ $beginning ];
    $this->pushPageRange($result, $middle);
    $this->pushPageRange($result, $end);

    return $result;
  }

  function getUrlForPage(int $page): string {
    $val = $this->getArgForPage($page);
    return Util::addRequestParameter('pag', $val);
  }

  function getUrlForColumn(string $col): string {
    $val = $this->getArgForColumn($col);
    return Util::addRequestParameter('pag', $val);
  }
}
