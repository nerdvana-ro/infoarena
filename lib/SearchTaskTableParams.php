<?php

class SearchTaskTableParams extends TaskTableParams {

  public array $tagIds;

  function __construct() {
    parent::__construct();
    $this->tagIds = Request::getCsv('tag_ids');
    $this->cssClass = '';
    $this->showRatings = true;
    $this->showScores = true;
    $this->showSolvedBy = false;
    $this->showPagination = true;
  }

  function getDefaultSortField(): string {
    return 'id';
  }

  function getDefaultSortAsc(): bool {
    return true;
  }
}
