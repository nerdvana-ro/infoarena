{$text=$text|default:null}
{if $job}
  <a href="{Config::URL_PREFIX}job_detail/{$job->id}">
    {if $text}
      {$text}
    {else}
      #{$job->id}
    {/if}
  </a>
{else}
  [ID invalid]
{/if}
