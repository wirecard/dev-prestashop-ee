{*
* Shop System Extensions:
* - Terms of Use can be found at:
* https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
* - License can be found under:
* https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 * @author Wirecard AG
 * @copyright Copyright (c) 2020 Wirecard AG, Einsteinring 35, 85609 Aschheim, Germany
 * @license MIT License
*}

<script>window.setInterval( function() {
		var wait = document.getElementById( "wait" );
		if ( wait.innerHTML.length > 3 )
			wait.innerHTML = "";
		else
			wait.innerHTML += ".";
	}, 200);
</script>
<link rel="stylesheet" type="text/css" href="{$base_url|escape:'htmlall':'UTF-8'}/modules/wirecardpaymentgateway/views/css/app.css" />
<div style="display: flex; justify-content: center; font-size: 20px;">{lFallback s='redirect_text' mod='wirecardpaymentgateway'}<span id="wait" style="font-size: 20px; width: 50px;">.</span></div>
<p id="card-spinner" class="wd-loader"></p>
<form id="credit_card_form" method="{$method|escape:'htmlall':'UTF-8'}" action="{$url|escape:'htmlall':'UTF-8'}">
    {foreach from=$form_fields key=key item=value}
        <input type="hidden" name="{$key|escape:'htmlall':'UTF-8'}" value="{$value|escape:'htmlall':'UTF-8'}">
    {/foreach}
</form>
<script>document.getElementsByTagName("form")[0].submit();</script>