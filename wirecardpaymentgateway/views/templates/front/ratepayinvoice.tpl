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
* @author Wirecard AG
* @copyright Wirecard AG
* @license GPLv3
*}

<form id="payment-form" action="{$action_link}" method="POST">
    <script language='JavaScript'>
        var di = {
            t:'{$device_identification|escape:'htmlall':'UTF-8'}',
            v:'WDWL',
            l:'Checkout'
        };
    </script>
    <script type='text/javascript' src='//d.ratepay.com/WDWL/di.js'></script>
    <noscript><link rel='stylesheet' type='text/css' href='//d.ratepay.com/di.css?t={$device_identification|escape:'htmlall':'UTF-8'}&v=WDWL&l=Checkout'></noscript>

    <object type='application/x-shockwave-flash' data='//d.ratepay.com/WDWL/c.swf' width='0' height='0'>
        <param name='movie' value='//d.ratepay.com/WDWL/c.swf' />
        <param name='flashvars' value='t={$device_identification|escape:'htmlall':'UTF-8'}&v=WDWL'/>
        <param name='AllowScriptAccess' value='always'/>
    </object>

    <div id="payment-processing-gateway-ratepay-form">
        <input type="hidden" class="form-control" name="invoiceDeviceIdent" id="invoiceDeviceIdent" value="{$device_identification|escape:'htmlall':'UTF-8'}"/>
        <ul>
            <li>
                <div class="float-xs-left">
                    <span class="custom-checkbox">
                        <input id="invoiceDataProtectionCheckbox" name="invoiceDataProtectionCheckbox" required="" type="checkbox" value="1" class="ps-shown-by-js">
                        <span><i class="material-icons rtl-no-flip checkbox-checked"></i></span>
                    </span>
                </div>
                <div class="condition-label">
                    <label id="invoiceDataProtectionLabel" for="invoiceDataProtectionCheckbox">
                        {lFallback s='text_terms_accept' mod='wirecardpaymentgateway'}
                    </label>
                </div>
            </li>
        </ul>
        <small id="invoiceDataProtectionHint" class="text-danger" style="display: none;">{lFallback s='text_terms_notice' mod='wirecardpaymentgateway'|unescape:"htmlall"}</small>
    </div>
</form>