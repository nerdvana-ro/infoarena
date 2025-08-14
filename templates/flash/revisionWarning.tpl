{** Mandatory arguments: $textblock, $revision, $numRevisions **}

<p>
  Atenție! Aceasta este o versiune veche a paginii (revizia {$revision} din
  {$numRevisions}), scrisă la {$textblock.timestamp}.
</p>

<p>
  {if $revision > 1}
    <a href="?revision={$revision-1}">revizia anterioară</a>
  {else}
    <span class="muted">revizia anterioară</span>
  {/if}

  <a class="hspace-3" href="?revision={$revision+1}">revizia următoare</a>
</p>
