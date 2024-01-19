{extends "layout.tpl"}

{block "title"}Rezultatele filtrării{/block}

{block "content"}
  <h1>Rezultatele filtrării</h1>

  <div id="task-filter-help">
    <div></div> Etichetă neselectată
    <div class="selected-filter"></div> Etichetă selectată
    <div class="sub-selected-filters"></div> Categorie selectată parțial
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
