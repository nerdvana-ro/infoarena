<div id="sidebar">
  <ul id="nav" class="clear">
    <li>
      <a href="{Config::URL_PREFIX}">Acasă</a>
    </li>
    <li>
      <a href="{Config::URL_PREFIX}concursuri">Concursuri</a>
    </li>
    <li>
      <a href="{Config::URL_PREFIX}concursuri-virtuale">Concursuri virtuale</a>
    </li>
    <li>
      <a href="{Config::URL_PREFIX}clasament-rating">Clasament</a>
    </li>

    <li>
      <a
        accesskey="m"
        href="{User::getCurrentUserMonitorUrl()}">
        <span class="access-key">M</span>onitorul de evaluare
      </a>
    </li>

    <li class="separator"><hr></li>

    <li>
      <a href="{Config::URL_PREFIX}cauta-probleme">Categorii probleme</a>
    </li>

    {if Config::GOOGLE_CSE_TOKEN && !Config::DEVELOPMENT_MODE}
      <li>
        <a href="{Config::URL_PREFIX}search">Căutare probleme</a>
      </li>
      {include "layout/googleSearch.tpl"}
    {/if}

    {if $identity}
      <li>
        <a href="{Config::URL_PREFIX}submit"><strong>Trimite soluții</strong></a>
      </li>
      <li>
        <a
          accesskey="m"
          href="{$identity->getAccountUrl()}">
          <span class="access-key">C</span>ontul meu
        </a>
      </li>
      <li>
        <a
          accesskey="p"
          href="{$identity->getProfileUrl()}">
          <span class="access-key">P</span>rofilul meu
        </a>
      </li>
    {/if}

    {if Identity::isAdmin()}
		  <li class="separator"><hr></li>
		  <li>
        <a
          accesskey="a"
          href="{Config::URL_PREFIX}admin">
          <span class="access-key">A</span>dministrativ
        </a>
      </li>
    {/if}

    {if $numReports && Identity::mayViewReports()}
      <li>
        <a href="{Config::URL_PREFIX}report/list">Rapoarte</a>
        ({$numReports})
      </li>
    {/if}
  </ul>

  {include "layout/sidebarLogin.tpl"}

  <p class="user-count">
    {User::countAll()} membri înregistrați
  </p>

  <div class="user-count" id="srv_time"></div>

</div>
