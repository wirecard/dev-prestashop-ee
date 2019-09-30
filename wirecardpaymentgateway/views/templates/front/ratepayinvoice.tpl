{*
* Shop System Extensions:
* - Terms of Use can be found at:
* https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
* - License can be found under:
* https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
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