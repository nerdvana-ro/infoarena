<?php

class SearchTaskTableParams extends TaskTableParams {

  public array $tagIds;

  function __construct() {
    parent::__construct();
    $this->tagIds = Request::getCsv('tagIds');
  }

  function getDefaultSortField(): string {
    return 'id';
  }

  function getDefaultSortAsc(): bool {
    return true;
  }
}
