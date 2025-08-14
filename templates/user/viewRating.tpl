{extends "layout.tpl"}

{block "title"}Rating {$user->full_name} ({$user->username}){/block}

{block "content"}

  {include "bits/userCommonInfo.tpl" activeTab="rating"}

  <h2>Rating {$user->username}</h2>

  <h3>Concursuri cu rating la care a participat</h3>

  {include "bits/roundList.tpl" rounds=$rounds}

{/block}
