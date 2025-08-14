{** $activeTab: name of active tab (view/rating/stats) **}

{if Identity::isAdmin()}
  <a
    class="user-control {if $user->banned}unban{else}ban{/if}"
    href="{$user->getControlUrl()}"
  >{if $user->banned}deblochează{else}blochează{/if}</a>
{/if}

<h1>{$user->full_name} ({$user->username})</h1>

<table class="user-table">
  <tbody>
    <tr>
      <td rowspan="5" class="text-center">
        {include "bits/userImage.tpl" size="big"}

        <br/>

        <a href="{$user->getMonitorUrl()}">
          Vezi soluțiile trimise
        </a>

        <br/>
      </td>

      <th>Nume</th>
      <td>{$user->full_name}</td>
    </tr>
    <tr>
      <th>Cont</th>
      <td>{$user->username}</td>
    </tr>
    <tr>
      <th>Clasă</th>
      <td>n/a</td>
    </tr>
    <tr>
      <th>Rating</th>
      <td>{$user->getScaledRating()}</td>
    </tr>
    <tr>
      <th>Statut</th>
      <td>{$user->getSecurityLevelName()}</td>
    </tr>
  </tbody>
</table>

<ul class="htabs">
  <li {if $activeTab == "view"}class="active"{/if}>
    <a href="{$user->getProfileUrl()}">Pagina personală</a>
  </li>
  <li {if $activeTab == "rating"}class="active"{/if}>
    <a href="{$user->getRatingUrl()}">Rating</a>
  </li>
  <li {if $activeTab == "stats"}class="active"{/if}>
    <a href="{$user->getStatsUrl()}">Statistici</a>
  </li>
</ul>
