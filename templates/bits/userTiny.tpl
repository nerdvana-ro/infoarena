{* Mandatory arguments: $user *}
{* Optional argument: bool $showRating *}
{$showRating=$showRating|default:false}

<span class="tiny-user">
  <a href="{$user->getProfileUrl()}">
    <img
      class="avatar-tiny"
      alt="avatar {$user->username}"
      src="{$user->getAvatarUrl('tiny')}">
    {$user->full_name|escape}
  </a>

  {if $showRating}
    <span>
      {include "bits/ratingBadge.tpl" rb=$user->getRatingBadge()}
    </span>
  {/if}

  <span class="username">
    <a href="{$user->getProfileUrl()}">
      {$user->username}
    </a>
  </span>
</span>
