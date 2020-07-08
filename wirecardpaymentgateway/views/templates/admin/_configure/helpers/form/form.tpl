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

{extends file="helpers/form/form.tpl"}
{block name="input"}
    {if $input.type == 'linkbutton'}
		<a class="btn btn-default" id="{$input.id|escape:'htmlall':'UTF-8'}" href="#">
			<i class="icon-check"></i>
            {lFallback s=$input.buttonText mod='wirecardpaymentgateway'}
		</a>
		<script type="text/javascript">
            $(function () {
                $(document).ready(function(){
                    var translate = {
                        paypal:'{lFallback s='paypal' mod='wirecardpaymentgateway'}',
                        creditcard:'{lFallback s='creditcard' mod='wirecardpaymentgateway'}',
                        sepadirectdebit:'{lFallback s='sepadd' mod='wirecardpaymentgateway'}',
                        sepacredittransfer:'{lFallback s='sepact' mod='wirecardpaymentgateway'}',
                        ideal:'{lFallback s='ideal' mod='wirecardpaymentgateway'}',
                        sofort:'{lFallback s='sofortbanking' mod='wirecardpaymentgateway'}',
                        poipia:'{lFallback s='poi_pia' mod='wirecardpaymentgateway'}',
                        invoice:'{lFallback s='ratepayinvoice' mod='wirecardpaymentgateway'}',
                        'alipay-xborder':'{lFallback s='alipay_crossborder' mod='wirecardpaymentgateway'}',
                        p24:'{lFallback s='ptwentyfour' mod='wirecardpaymentgateway'}',
                    };
                    $("a[data-toggle=tab]").each(function() {
                        $(this).html(translate[$(this).html().toLowerCase()]);
                    });
                });
                $('#{$input.id}').on('click', function() {
                    $.ajax({
                        type: 'POST',
                        url: '{$ajax_configtest_url|escape:'quotes':'UTF-8'}',
                        dataType: 'json',
                        data: {
                            action: 'TestConfig',
                    {foreach $input.send as $datasend}
                    '{$datasend|escape:'htmlall':'UTF-8'}': $('input[name={$datasend|escape:'htmlall':'UTF-8'}]').val(),
                    {/foreach}
                        method: '{$input.method|escape:'htmlall':'UTF-8'}',
                        ajax: true
                },
                    success: function (jsonData) {
                        if (jsonData) {
                            $.fancybox({
                                fitToView: true,
                                content: '<div><fieldset><legend>{lFallback s='text_test_result' mod='wirecardpaymentgateway'}</legend>' +
                                '<label>{lFallback s='config_status' mod='wirecardpaymentgateway'}:</label>' +
                                '<div class="margin-form" style="text-align:left;">' + jsonData.status + '</div><br />' +
                                '<label>{lFallback s='text_message' mod='wirecardpaymentgateway'}:</label>' +
                                '<div class="margin-form" style="text-align:left;">' + jsonData.message + '</div></fieldset></div>'
                            });
                        }
                    }
                });
                });
            });
		</script>
    {else}
        {$smarty.block.parent}
    {/if}
{/block}
