{extends "layout.tpl"}

{block "title"}Statistici {$user->full_name} ({$user->username}){/block}

{block "content"}

  {include "bits/userCommonInfo.tpl" activeTab="stats"}

  <h2>Statistici pentru {$user->username}</h2>

  <h3>Probleme din arhivă rezolvate ({$solvedTasks|count})</h3>

  {include "bits/taskList.tpl" tasks=$solvedTasks}

  <h3>Probleme din arhivă încercate ({$unsolvedTasks|count})</h3>

  {include "bits/taskList.tpl" tasks=$unsolvedTasks}

  <h3>Participant la...</h3>

  {include "bits/roundList.tpl" rounds=$rounds}

{/block}
