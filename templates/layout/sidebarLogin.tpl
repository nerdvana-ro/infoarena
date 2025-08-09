{$showSidebarLogin=$showSidebarLogin|default:true}

{if !Config::MAINTENANCE_MODE && Identity::isAnonymous() && $showSidebarLogin}
  <div id="login">
    {include "auth/loginForm.tpl"}
    <p>
      <a href="{Config::URL_PREFIX}register">Mă înregistrez</a>
      <br>
      <a href="{Config::URL_PREFIX}resetpass">Mi-am uitat parola</a>
    </p>
  </div>
{/if}
