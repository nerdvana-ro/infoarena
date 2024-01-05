{if $score === null}
  <span class="deemph">N/A</span>
{else}
  {$score|string_format:'%d'}
{/if}
