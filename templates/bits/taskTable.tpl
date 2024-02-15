{$tasks=$taskTable->getTasks()}

<div id="page-table">
  {if !count($tasks)}
    Nicio problemă găsită.
  {else}
    {include "bits/pagination.tpl"}

    <table class="alternating-colors tasks fill-screen {$params->cssClass}">
      <thead>
        <tr>
          {if isset($params->showNumbers) && $params->showNumbers}
            <th class="number">
              <a href="{$params->getUrlForColumn('number')}">
                #
              </a>
            </th>
          {/if}
          <th class="title">
            <a href="{$params->getUrlForColumn('title')}">
              titlu
            </a>
          </th>
          <th>
            autor(i)
          </th>
          <th>
            <a href="{$params->getUrlForColumn('source')}">
              sursă
            </a>
          </th>
          {if $params->showRatings}
            <th class="rating">
              <a href="{$params->getUrlForColumn('rating')}">
                dificultate
              </a>
            </th>
          {/if}
          {if $params->showSolvedBy}
            <th class="solved_by">
              <a href="{$params->getUrlForColumn('solved_by')}">
                rezolvată de
              </a>
            </th>
          {/if}
          {if $params->showScores}
            <th class="score">
              <a href="{$params->getUrlForColumn('score')}">
                scorul tău
              </a>
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
</div>
