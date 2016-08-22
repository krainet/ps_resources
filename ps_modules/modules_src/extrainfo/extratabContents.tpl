<link rel="stylesheet" href="{$base_dir}modules/extrainfo/extrainfo.css" />
<div id="msTabHolder">
<div id="msTabHolder_tabs"></div>
<div id="msTabHolder_contents"></div>

<table width="100%" border="0">
  <tr>
    <td>{l s='Name' mod='extrainfo'}</td>
    <td>:</td>
    <td>{$product->name|escape:'htmlall':'UTF-8'}</td>
  </tr>
  <tr>
    <td>{l s='Author' mod='extrainfo'}</td>
    <td>:</td>
    <td>{$product->reference|escape:'htmlall':'UTF-8'}</td>
  </tr>
  <tr>
    <td>{l s='Supplier Reference' mod='extrainfo'}</td>
    <td>:</td>
    <td>{$product->supplier_reference|escape:'htmlall':'UTF-8'}</td>
  </tr>
  <tr>
    <td>{l s='ISBN' mod='extrainfo'}</td>
    <td>:</td>
    <td>{$product->ean13|escape:'htmlall':'UTF-8'}</td>
  </tr>
  <tr>
    <td>{l s='Item Code' mod='extrainfo'}</td>
    <td>:</td>
    <td>{$product->upc|escape:'htmlall':'UTF-8'}</td>
  </tr>
</table>

</div>


