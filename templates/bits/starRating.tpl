{* Mandatory argument: $task *}
{if !$task->rating || !$task->isPublic()}
  N/A
{else}
  <span class="star-rating">
    {strip}
    {foreach StarRating::getStarTypes($task) as $type}
      <img
        alt="stea de rating de tip {$type}"
        src="{Config::URL_PREFIX}static/images/stars/{$type}.png">
    {/foreach}
    {/strip}
    <span class="hidden">{$task->rating}</span>
  </span>
{/if}
