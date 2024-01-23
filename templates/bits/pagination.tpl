{* @param $n number of pages *}
{* @param $k current page *}
{$ranges=Util::getPaginationRange($n, $k)}

<ul class="pagination">

  {foreach $ranges as $i => $range}
    {if $i}
      <li class="separator">···</li>
    {/if}
    {for $p = $range[0] to $range[1]}
      <li
        {if $k == $p}class="active"{/if}
        title="pagina {$p} din {$n}">
        <a href="#" data-dest="{$p}">{$p}</a>
      </li>
    {/for}
  {/foreach}

</ul>
