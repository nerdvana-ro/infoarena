<table>
  <thead>
    <tr>
      <th>problemă</th>
      <th>rating dat de</th>
    </tr>
  </thead>

  <tbody>
    {foreach $report->getTasks() as $task}
      <tr>
        <td>
          {include "bits/taskLink.tpl"}
        </td>

        <td>
          {$task->full_name}
        </td>
      </tr>
    {/foreach}
  </tbody>
</table>
