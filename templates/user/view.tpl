{extends "layout.tpl"}

{block "title"}Profil {$user->full_name} ({$user->username}){/block}

{block "content"}
  {include "bits/userCommonInfo.tpl" activeTab="view"}

  {include "bits/textblockHeader.tpl" textblock=$textblock}

  <div class="wiki_text_block">
    {$wikiHtml}
  </div>
{/block}
