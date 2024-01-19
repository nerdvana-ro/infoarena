<?php

class SearchTaskTableParams extends TaskTableParams {

  public array $tagIds;

  function populateFromRequest(): void {
    parent::populateFromRequest();
    $this->tagIds = Request::getCsv('tagIds');
  }
}
