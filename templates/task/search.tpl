{extends "layout.tpl"}

{block "title"}Rezultatele filtrării{/block}

{block "content"}
  <h1>Rezultatele filtrării</h1>

  <div id="task-filter-help">
    <div> </div> Tag neselectat
    <div class="selected-filter"> </div> Tag selectat
    <div class="sub-selected-filters"> </div> Categorie din care e selectat cel puțin un tag
  </div>

  <br>

  <div id="task-filter-menu">
    {include "bits/searchTagTree.tpl" tree=$tagTree}
    {include "bits/searchTagTree.tpl" tree=$authorTree}
  </div>

  <div id="task-filter-table">
    <div
      class="ajax-table"
      data-tag-ids="{'_'|implode:$tagIds}">
      {$taskTableHtml}
    </div>
  </div>
{/block}
