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
 *
 * @author Wirecard AG
 * @copyright Wirecard AG
 * @license GPLv3
 *}

{extends file="helpers/view/view.tpl"}

{block name="override_tpl"}
    <div class="col-lg-12">
        <div class="panel" style="width: 100%">
            <h3><i class="icon-group"></i> {l s='Transaction information' mod='wirecardpaymentgateway'}</h3>
            <h2>{l s='Transaction' mod='wirecardpaymentgateway'} {$transaction_id|escape:'htmlall':'UTF-8'}</h2>
            <br>
            <h3>
                {l s='Payment via' mod='wirecardpaymentgateway'} {$payment_method|escape:'htmlall':'UTF-8'}
            </h3>
            <div><b>{$transaction_type|escape:'htmlall':'UTF-8'}</b></div>
            <br>
            <div class="wc-order-data-row">
                {if $status != 'closed' and $canCancel }
                    <a href="{$cancelLink|escape:'htmlall':'UTF-8'}" class='button'>{l s='Cancel Transaction' mod='wirecardpaymentgateway'}</a>
                {/if}
                {if $status != 'closed' and $canCapture }
                    <a href="{$cancelLink|escape:'htmlall':'UTF-8'}" class='button'>{l s='Capture Transaction' mod='wirecardpaymentgateway'}</a>
                {/if}
                {if $status != 'closed' and $canRefund }
                    <a href="{$cancelLink|escape:'htmlall':'UTF-8'}" class='button'>{l s='Refund Transaction' mod='wirecardpaymentgateway'}</a>
                {/if}
                {if $status == 'closed' }
                    <p class='add-items'>{l s='No Back-end operations available for this transaction' mod='wirecardpaymentgateway'}</p>
                {/if}
            </div>
            <hr>
            <h3>{l s='Responsedata:' mod='wirecardpaymentgateway'}</h3>
            <div class="order_data_column_container">
                <table>
                    <tr>
                        <td>
                            <b>{l s='Total' mod='wirecardpaymentgateway'}</b>
                        </td>
                        <td>
                            <b>{$amount|escape:'htmlall':'UTF-8'} {$currency|escape:'htmlall':'UTF-8'}</b>
                        </td>
                    </tr>
                    {foreach from=$response_data key=k item=v}
                        <tr><td>{$k}</td><td>{$v}</td></tr>
                    {/foreach}
                </table>
            </div>
        </div>
    </div>
{/block}
