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
</div>
<!-- livezilla.net PLACE SOMEWHERE IN BODY -->
<script type="text/javascript" id="936f87cd4ce16e1e60bea40b45b0596a" src="https://provusgroup.com/livezilla/script.php?id=936f87cd4ce16e1e60bea40b45b0596a"></script>
<!-- livezilla.net PLACE SOMEWHERE IN BODY -->