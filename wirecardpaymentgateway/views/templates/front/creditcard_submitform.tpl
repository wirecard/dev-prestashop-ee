{*
* Shop System Plugins - Terms of Use
*
* The plugins offered are provided free of charge by Wirecard AG and are explicitly not part
* of the Wirecard AG range of products and services.
*
* They have been tested and approved for full functionality in the standard configuration
* (status on delivery) of the corresponding shop system. They are under General Public
* License version 3 (GPLv3) and can be used, developed and passed on to third parties under
* the same terms.
*
* However, Wirecard AG does not provide any guarantee or accept any liability for any errors
* occurring when used in an enhanced, customized shop system configuration.
*
* Operation in an enhanced, customized configuration is at your own risk and requires a
* comprehensive test phase by the user of the plugin.
*
* Customers use the plugins at their own risk. Wirecard AG does not guarantee their full
* functionality neither does Wirecard AG assume liability for any disadvantages related to
* the use of the plugins. Additionally, Wirecard AG does not guarantee the full functionality
* for customized shop systems or installed plugins of other vendors of plugins within the same
* shop system.
*
* Customers are responsible for testing the plugin's functionality before starting productive
* operation.
*
* By installing the plugin into the shop system the customer agrees to these terms of use.
* Please do not use the plugin if you do not agree to these terms of use!
*}

<script>window.setInterval( function() {
		var wait = document.getElementById( "wait" );
		if ( wait.innerHTML.length > 3 )
			wait.innerHTML = "";
		else
			wait.innerHTML += ".";
	}, 200);
</script>
<div style="display: flex; justify-content: center; font-size: 20px;">{l s='redirect_text' mod='wirecardpaymentgateway'}<span id="wait" style="font-size: 20px; width: 50px;">.</span></div>

<form id="credit_card_form" method="{$method|escape:'htmlall':'UTF-8'}" action="{$url}">
    {foreach from=$form_fields key=key item=value}
        <input type="hidden" name="{$key|escape:'htmlall':'UTF-8'}" value="{$value|escape:'htmlall':'UTF-8'}">
    {/foreach}
</form>
<script>document.getElementsByTagName("form")[0].submit();</script>