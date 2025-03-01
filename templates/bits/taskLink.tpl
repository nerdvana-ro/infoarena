{$taskId=$taskId|default:''}
{if !$task}
  {if $taskId}
    {$taskId}
  {else}
    Problema nu există!
  {/if}
{elseif !$task->isViewable()}
  ...
{else}
  <a href="{Config::URL_PREFIX}problema/{$task->id}">
    {$task->title}
  </a>
{/if}
