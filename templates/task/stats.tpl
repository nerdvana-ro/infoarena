{extends "layout.tpl"}

{block "title"}Statisticile problemei {$task->title}{/block}

{block "content"}

  <h1>Statisticile problemei {include "bits/taskLink.tpl"}</h1>

  {if count($stats)}
    {foreach $stats as $stat}
      <h2 class="vspace-1">{$stat->getDescription()}</h2>

      <table>
        <thead>
          <tr>
            <th>loc</th>
            <th>utilizator</th>
            <th class="text-right">{$stat->getName()}</th>
          </tr>
        </thead>
        <tbody>
          {foreach $stat->jobs as $row => $job}
            <tr>
              <td class="number">{$row+1}</td>
              <td>
                {include "bits/userTiny.tpl"
                  showRating=true
                  user=$job->getUser()}
              </td>
              <td class="text-right">
                {capture "text"}{$stat->getMetric($row)} {$stat->getUnit()}{/capture}
                {include "bits/jobLink.tpl" text=$smarty.capture.text}
              </td>
            </tr>
          {/foreach}
        </tbody>
      </table>

    {/foreach}
  {else}
    Nicio sursă corectă trimisă la această problemă. 😢
  {/if}

  <h2>Alte statistici</h2>

  <ul>
    <li>Numărul mediu de submisii greșite: {$averageWrongSubmissions}</li>
    {if Identity::isLoggedIn()}
      <li>Numărul tău de submisii greșite: {$userWrongSubmissions}</li>
    {/if}
    <li>Procentajul de reușită: {$solvedPercentage}%</li>
  </ul>
{/block}
