<form id="page-info">
</form>
<div
  data-round-description="{$params->roundDescription|escape}"
  data-detail-round="{$params->detailRound}"
  data-detail-task="{$params->detailTask}"
  id="page-table">

  {$penaltyLinks=Identity::isAdmin() && $rankings->getRoundId() && !$params->showPagination}
  {if $params->showPagination}
    <div class="controls">
      {include "bits/pagination.tpl" n=$rankings->getNumPages() k=$params->pageNo}

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
        rezultatele {$rankings->getFirstResult()}-{$rankings->getLastResult()}
        din {$rankings->getNumResults()}
      </div>
    </div>
  {/if}

  <table
    class="alternating-colors"
    data-show-pagination="{$params->showPagination}"
    data-page="{$params->pageNo}"
    data-sort-field="{$params->sortField}"
    data-sort-asc="{$params->sortAsc}"
    data-form-id="page-info">

    <thead>
      <tr>
        <th class="center" data-field="rank">loc</th>
        <th data-field="username">utilizator</th>
        {foreach $rankings->getColumns() as $i => $col}
          <th class="center" data-field="col{$i}">{$col.displayValue}</th>
        {/foreach}
        <th class="center" data-field="total">total</th>
        {if $penaltyLinks}
          <th class="center" data-disabled-sort>acțiuni</th>
        {/if}
      </tr>
    </thead>

    <tbody>
      {foreach $rankings->getRows() as $row}
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
              <a href="{Config::URL_PREFIX}penalty_edit?userId={$row->user->id}&roundId={$rankings->getRoundId()}">
                penalizează
              </a>
            </td>
          {/if}
        </tr>
      {/foreach}
    </tbody>
  </table>
</div>
