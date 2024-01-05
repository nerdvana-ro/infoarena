{foreach $task->getAuthors() as $i => $author}
  {if $i}|{/if}
  {include "bits/tagLink.tpl" tag=$author}
{/foreach}
