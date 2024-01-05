{* @param $n number of pages *}
{* @param $k current page *}
{$ends=Util::getPaginationRange($n, $k)}

<ul class="pagination">

  {* first page *}
  <li title="prima pagină">
    {if $k == 1}
      <span>«</span>
    {else}
      <a href="#" data-dest="1">«</a>
    {/if}
  </li>

  {* previous page *}
  <li class="prev" title="pagina precedentă">
    {if $k == 1}
      <span>‹</span>
    {else}
      <a href="#" data-dest="{$k-1}">‹</a>
    {/if}
  </li>

  {* range around $k *}
  {for $p = $ends[0] to $ends[1]}
    <li
      {if $k == $p}class="active"{/if}
      title="pagina {$p} din {$n}">
      <a href="#" data-dest="{$p}">{$p}</a>
    </li>
  {/for}

  {* next page *}
  <li class="next" title="pagina următoare">
    {if $k == $n}
      <span>›</span>
    {else}
      <a href="#" data-dest="{$k+1}">›</a>
    {/if}
  </li>

  {* last page *}
  <li title="ultima pagină">
    {if $k == $n}
      <span>»</span>
    {else}
      <a href="#" data-dest="{$n}">»</a>
    {/if}
  </li>

</ul>
