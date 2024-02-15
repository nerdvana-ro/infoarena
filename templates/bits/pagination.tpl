{* @param $params an instance of PagerParams *}
{if $params->showPagination}
  {$ranges=$params->getRanges()}

  <div class="controls">

    <ul class="pagination">
      {foreach $ranges as $i => $range}
        {if $i}
          <li class="separator">···</li>
        {/if}
        {for $p = $range[0] to $range[1]}
          <li
            {if $p == $params->page}class="active"{/if}
            title="pagina {$p} din {$params->getNumPages()}">
            <a href="{$params->getUrlForPage({$p})}">{$p}</a>
          </li>
        {/for}
      {/foreach}
    </ul>

    <div class="page-size-select">
      Arată
      <select data-form-id="{$formId}">
        {foreach Config::PAGE_SIZES as $size}
          <option
            {if $params->pageSize == $size}selected{/if}
            value="{$params->getArgForPageSize($size)}">
            {$size}
          </option>
        {/foreach}
      </select>
      per pagină
    </div>

    <div class="range">
      rezultatele {$params->getFirstResult()}-{$params->getLastResult()}
      din {$params->numResults}
    </div>
  </div>
{/if}
