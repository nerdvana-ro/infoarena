{if $showFilters}
  <form class="task-filters">
    Arată

    <select name="attempted">
      {for $a = 0 to TaskTableParams::NUM_ATTEMPTED-1}
        <option
          {if $a == $params->attempted}selected{/if}
          value="{$a}">
          {TaskTableParams::getAttemptedText($a)}
        </option>
      {/for}
    </select>

    din perspectiva utilizatorului

    <input
      name="as_username"
      placeholder="username"
      type="text"
      value="{$asUsername}">

    <input
      class="button"
      type="submit"
      value="Vezi">

    <div class="task-filter-messages">
      {if $asUsername && !$asUser}
        <span class="error">
          Utilizatorul „{$asUsername}” nu există.
        </span>
      {/if}

      {if $asUser}
        Momentan vezi această listă de probleme din perspectiva utilizatorului
        {include "bits/userTiny.tpl" user=$asUser showRating=true}
      {/if}
    </div>
  </form>
{/if}

<div
  class="ajax-table"
  data-round-id="{$params->roundId}"
  data-user-id="{$params->userId}"
  data-attempted="{$params->attempted}">
  {$taskTableHtml}
</div>
