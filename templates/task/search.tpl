{extends "layout.tpl"}

{block "title"}Rezultatele filtrării{/block}

{block "content"}
  <h1>Rezultatele filtrării</h1>

  <form id="tag-filters">
    <input type="hidden" name="tag_ids" value="{'_'|implode:$tagIds}">
    <input type="hidden" name="pag" value="{$params->toString()}">
  </form>

  <div id="task-filter-columns">
    <div id="task-filter-menu">
      {include "bits/searchTagTree.tpl" tree=$tagTree}
      {include "bits/searchTagTree.tpl" tree=$authorTree}

      {if count($tagIds)}
        <div id="task-filter-help">
          <p>
            <div class="selected-filter"></div> Etichetă selectată
          </p>
          <p>
            <div class="sub-selected-filters"></div> Selecție parțială
          </p>
          <p>
            <div></div> Etichetă neselectată
          </p>
        </div>
      {/if}
    </div>

    <div id="task-filter-table">
      <div id="page-table">
        {include "bits/taskTable.tpl" formId="tag-filters"}
      </div>
    </div>
  </div>
{/block}
