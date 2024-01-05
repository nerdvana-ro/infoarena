{$penaltyLinks=Identity::isAdmin() && $roundId && !$params->showPagination}
<table
  class="alternating-colors"
  data-show-pagination="{$params->showPagination}"
  data-page-no="{$params->pageNo}"
  data-sort-field="{$params->sortField}"
  data-sort-asc="{$params->sortAsc}"
  data-url="{Config::URL_PREFIX}ajax/rankings.php">

  <thead>
    <tr>
      <th class="center" data-field="rank">loc</th>
      <th data-field="username">utilizator</th>
      {foreach $columns as $i => $col}
        <th class="center" data-field="col-{$i}">{$col.displayValue}</th>
      {/foreach}
      <th class="center" data-field="total">total</th>
      {if $penaltyLinks}
        <th class="center">acțiuni</th>
      {/if}
    </tr>
  </thead>

  <tbody>
    {foreach $rows as $row}
      <tr>
        <td class="center">
          {$row->rank}
        </td>
        <td class="nowrap">
          {include "bits/userNormal.tpl" user=$row->user}
        </td>
        {foreach $row->scores as $score}
          <td class="center">
            {$score|default:'&ndash;'}
          </td>
        {/foreach}
        <td class="center">
          {include "bits/score.tpl" score=$row->total}
        </td>
        {if $penaltyLinks}
          <td class="center">
            <a href="{Config::URL_PREFIX}penalty_edit?userId={$row->user->id}&roundId={$roundId}">
              penalizează
            </a>
          </td>
        {/if}
      </tr>
    {/foreach}
  </tbody>
</table>

{if $params->showPagination}
  <div class="controls">
    <div class="range">
      rezultatele {$firstResult}-{$lastResult} din {$numResults}
    </div>

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

    {include "bits/pagination.tpl" n=$numPages k=$params->pageNo}
  </div>
{/if}
