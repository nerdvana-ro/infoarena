<ul class="mainmenu">
  {foreach $tree->roots as $node}
    <li class="{$node->getCssClass()}">
      <a {if !$node->isFake()}href="{$node->url}"{/if}>
        {$node->tag->name}
        {if $node->showCounts()}
          ({$node->tag->num_tasks})
        {/if}
      </a>

      <ul class="submenu">
        {foreach $node->children as $child}
          <li class="{$child->getCssClass()}">
            <a href="{$child->url}">
              {$child->tag->name}
              {if $child->showCounts()}
                ({$child->tag->num_tasks})
              {/if}
            </a>
          </li>
        {/foreach}
      </ul>
    </li>
  {/foreach}
</ul>
