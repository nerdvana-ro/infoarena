{$tasks=$taskTable->getTasks()}
{if !count($tasks)}
  Nicio problemă găsită.
{else}
  {if $params->showPagination}
    <div class="controls">
      {include "bits/pagination.tpl" n=$taskTable->getNumPages() k=$params->pageNo}

      <div class="page-size-selector">
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

      <div class="range">
        rezultatele {$taskTable->getFirstResult()}-{$taskTable->getLastResult()}
        din {$taskTable->getNumResults()}
      </div>
    </div>
  {/if}

  <table
    class="alternating-colors tasks fill-screen {$params->cssClass}"
    data-show-pagination="{$params->showPagination}"
    data-page="{$params->pageNo}"
    data-sort-field="{$params->sortField}"
    data-sort-asc="{$params->sortAsc}"
    data-form-id="{$formId}">

    <thead>
      <tr>
        {if isset($params->showNumbers) && $params->showNumbers}
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
          {if isset($params->showNumbers) && $params->showNumbers}
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
          <td>
            {$authors=Preload::getTaskAuthors($task->id)}
            {include "bits/taskAuthors.tpl"}
          </td>
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
{/if}
