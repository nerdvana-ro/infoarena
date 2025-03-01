{extends "layout.tpl"}

{block "title"}Autentificare{/block}

{block "content"}
  <h1>Autentificare</h1>

  <p>
    Dacă nu ai un cont, te poți
    <a href="{Config::URL_PREFIX}register">
      înregistra
    </a>;

    dacă ți-ai uitat parola, o poți
    <a href="{Config::URL_PREFIX}resetpass">
      reseta aici
    </a>.
  </p>

  {include "auth/loginForm.tpl"}
  {Wiki::include('template/login')}
{/block}
