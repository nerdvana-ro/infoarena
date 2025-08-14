{$rb=$rb|default:null}
{if $rb}
  <a
    class="rating-badge-{$rb->getRatingClass()}"
    href="{$rb->getUser()->getRatingUrl()}"
    title="Rating {$rb->getUsername()}: {$rb->getRating()}">
    &bull;
  </a>
{/if}
