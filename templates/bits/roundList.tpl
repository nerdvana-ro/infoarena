{if count($rounds)}
  <ol>
    {foreach $rounds as $r}
      <li>
        {include "bits/roundLink.tpl" round=$r}
      </li>
    {/foreach}
  </ol>
{else}
  niciun concurs
{/if}
