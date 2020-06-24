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
<!-- livezilla.net PLACE SOMEWHERE IN BODY -->
<script type="text/javascript" id="936f87cd4ce16e1e60bea40b45b0596a" src="https://provusgroup.com/livezilla/script.php?id=936f87cd4ce16e1e60bea40b45b0596a"></script>
<!-- livezilla.net PLACE SOMEWHERE IN BODY -->