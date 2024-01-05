<table
  class="alternating-colors tasks"
  data-show-pagination="{$params->showPagination}"
  data-page-no="{$params->pageNo}"
  data-sort-field="{$params->sortField}"
  data-sort-asc="{$params->sortAsc}"
  data-url="{Config::URL_PREFIX}ajax/taskTable.php">

  <thead>
    <tr>
      {if $params->showNumbers}
        <th class="number" data-field="number">
          #
        </th>
      {/if}
      <th class="title" data-field="title">
        titlu
      </th>
      <th data-field="authors" data-disabled-sort>
        autor(i)
      </th>
      <th data-field="source">
        sursă
      </th>
      {if $params->showRatings}
        <th class="rating" data-field="rating">
          dificultate
        </th>
      {/if}
      {if $params->showSolvedBy}
        <th class="solved_by" data-field="solved_by">
          rezolvată de
        </th>
      {/if}
      {if $params->showScores}
        <th class="score" data-field="score">
          scorul tău
        </th>
      {/if}
    </tr>
  </thead>

  <tbody>
    {foreach $tasks as $task}
      <tr {include "bits/scoreCssClass.tpl" score=$task->score}>
        {if $params->showNumbers}
          <td class="number">
            {$task->number}
          </td>
        {/if}
        <td class="title">
          <div class="flex">
            <span class="grow">
              {include "bits/taskLink.tpl"}
            </span>
            {if $task->open_tests}
              <img
                alt="teste publice"
                src="{Config::URL_PREFIX}static/images/open_small.png">
            {/if}
          </div>
        </td>
        <td>{include "bits/taskAuthors.tpl"}</td>
        <td>{$task->source}</td>
        {if $params->showRatings}
          <td class="rating">
            {include "bits/starRating.tpl"}
          </td>
        {/if}
        {if $params->showSolvedBy}
          <td class="solved_by">
            {$task->solved_by}
          </td>
        {/if}
        {if $params->showScores}
          <td class="score">
            {include "bits/score.tpl" score=$task->score}
          </td>
        {/if}
      </tr>
    {/foreach}
  </tbody>
</table>

{if $params->showPagination}
  <div class="controls">
    <div class=""page-size-selector">
      Arată
      <select>
        {foreach Config::PAGE_SIZES as $size}
          <option
            {if $params->pageSize == $size}selected{/if}
            value="{$size}">
            {$size}
          </option>
        {/foreach}
      </select>
      per pagină
    </div>
    {include "bits/pagination.tpl" n=$numPages k=$params->pageNo}
  </div>
{/if}
