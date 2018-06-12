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

{extends file="helpers/form/form.tpl"}
{block name="input"}
    {if $input.type == 'linkbutton'}
		<a class="btn btn-default" id="{$input.id|escape:'htmlall':'UTF-8'}" href="#">
			<i class="icon-check"></i>
            {l s=$input.buttonText mod='wirecardpaymentgateway'}
		</a>
		<script type="text/javascript">
            $(function () {
                $(document).ready(function(){
                    var translate = {
                        paypal:'paypal',
                        creditcard:'credit Card',
                        sepa:'sepa',
                        ideal:'ideal',
                        sofort:'sofort.',
                        poipia:'Payment on Invoice / Payment in Advance (POI/PIA)',
                        invoice:'invoice',
                        'alipay-xborder':'alipay crossborder',
                        p24:'przelewy24',
                        masterpass:'masterpass',
                        unionpayinternational:'unionpay international'
                    };
                    $("a[data-toggle=tab]").each(function() {
                        $(this).html(translate[$(this).html().toLowerCase()]);
                    });
                });
                $('#{$input.id}').on('click', function() {
                    $.ajax({
                        type: 'POST',
                        {** this url doesn't work when escaped *}
                        url: '{$ajax_configtest_url}',
                        dataType: 'json',
                        data: {
                            action: 'TestConfig',
                    {foreach $input.send as $datasend}
                    {$datasend}:$('input[name={$datasend|escape:'htmlall':'UTF-8'}]').val(),
                    {/foreach}
                        method: '{$input.method|escape:'htmlall':'UTF-8'}',
                        ajax: true
                },
                    success: function (jsonData) {
                        if (jsonData) {
                            $.fancybox({
                                fitToView: true,
                                content: '<div><fieldset><legend>{l s='Test result' mod='wirecardpaymentgateway'}</legend>' +
                                '<label>{l s='Status' mod='wirecardpaymentgateway'}:</label>' +
                                '<div class="margin-form" style="text-align:left;">' + jsonData.status + '</div><br />' +
                                '<label>{l s='Message' mod='wirecardpaymentgateway'}:</label>' +
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
