<form id="task-filters">
  {if $showFilters}
    Arată

    <select name="attempted">
      {for $a = 0 to RoundTaskTableParams::NUM_ATTEMPTED-1}
        <option
          {if $a == $params->attempted}selected{/if}
          value="{($a)?$a:''}">
          {RoundTaskTableParams::getAttemptedText($a)}
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
  {/if}
</form>

<div id="page-table">
  {include "bits/taskTable.tpl" formId="task-filters"}
</div>
