<form id="page-info">
  <input type="hidden" name="pag" value="">
</form>

<div id="page-table">

  {include "bits/pagination.tpl" formId="page-info"}

  {$penaltyLinks=Identity::isAdmin() && $rankings->getRoundId() && !$params->showPagination}

  <table class="alternating-colors">
    <thead>
      <tr>
        <th class="center">
          <a href="{$params->getUrlForColumn('rank')}">
            loc
          </a>
        </th>
        <th>
          <a href="{$params->getUrlForColumn('username')}">
            utilizator
          </a>
        </th>
        {foreach $rankings->getColumns() as $i => $col}
          <th class="center">
            <a href="{$params->getUrlForColumn($i)}">
              {$col.displayValue}
            </a>
          </th>
        {/foreach}
        <th class="center">
          <a href="{$params->getUrlForColumn('total')}">
            total
          </a>
        </th>
        {if $penaltyLinks}
          <th class="center">acțiuni</th>
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
