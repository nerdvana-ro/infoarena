{** Mandatory argument: array $textblock **}
<div id="wikiOps">
  <ul>
    {if Identity::mayEditTextblockReversibly($textblock)}
      <li>
        <a href="?action=edit">Editează</a>
      </li>
    {/if}
    {if Identity::mayViewTextblock($textblock)}
      <li>
        <a href="?action=history">Istoria</a>
      </li>
    {/if}
    {if Identity::mayMoveTextblock($textblock)}
      <li>
        <a href="?action=move">Mută</a>
      </li>
    {/if}
    {if Identity::mayEditTextblockReversibly($textblock)}
      <li>
        <a href="?action=copy">Copiază</a>
      </li>
    {/if}
    {if Identity::mayDeleteTextblock($textblock)}
      <li>
        <a
          onclick="return confirm('Această acțiune este ireversibilă! Dorești să continui?')"
          href="javascript:PostData('?action=delete', [])">
          Șterge
        </a>
      </li>
    {/if}
    {if Identity::mayEditTextblockReversibly($textblock)}
      <li>
        <a href="?action=attach">Atașează</a>
      </li>
    {/if}
    {if Identity::mayViewTextblock($textblock)}
      <li>
        <a href="?action=attach-list">Listează atașamente</a>
      </li>
    {/if}
  </ul>
</div>
