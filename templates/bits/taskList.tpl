{if count($tasks)}
  <ul class="inline-list">
    {foreach $tasks as $t}
      <li>
        {include "bits/taskLink.tpl" task=$t}
      </li>
    {/foreach}
  </ul>
{else}
  nicio problemă
{/if}
