{*
* Shop System Extensions:
* - Terms of Use can be found at:
* https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
* - License can be found under:
* https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
*}

<img src="../modules/wirecardpaymentgateway/logo.png">
<br>
<p><strong>{lFallback s='pay_with_gateway' mod='wirecardpaymentgateway'}</strong></p>
<div class="btn-group">
    <a class="btn btn-default" id="wirecardTransactions" href="{$link->getAdminLink('WirecardTransactions')|escape:'html':'UTF-8'}">
        <i class="icon-money"></i>
        {lFallback s='text_list' mod='wirecardpaymentgateway'}
    </a>
    <a class="btn btn-default" id="WirecardSupport" href="{$link->getAdminLink('WirecardSupport')|escape:'html':'UTF-8'}">
        {lFallback s='text_support' mod='wirecardpaymentgateway'}
    </a>
    <a class="btn btn-default" id="WirecardShopPluginInformation" target=_blank href="https://github.com/wirecard/prestashop-ee/wiki/Terms-of-Use">
        {lFallback s='terms_of_use' mod='wirecardpaymentgateway'}
    </a>
    <a class="btn btn-default" id="WirecardGeneralSettings" href="{$link->getAdminLink('WirecardGeneralSettings')|escape:'html':'UTF-8'}">
        {lFallback s='general_settings' mod='wirecardpaymentgateway'}
    </a>
</div>