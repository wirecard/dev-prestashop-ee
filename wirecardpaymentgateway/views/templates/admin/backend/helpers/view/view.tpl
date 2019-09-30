{*
* Shop System Extensions:
* - Terms of Use can be found at:
* https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
* - License can be found under:
* https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
*}

{extends file="helpers/view/view.tpl"}

{block name="override_tpl"}
    <div class="col-lg-12">
        <div class="panel" style="width: 100%">
            <h3><i class="icon-group"></i> {lFallback s='heading_transaction_details' mod='wirecardpaymentgateway'}</h3>
            <h2>{lFallback s='text_transaction' mod='wirecardpaymentgateway'} {$transaction.id|escape:'htmlall':'UTF-8'}</h2>
            <br>
            <h3>
                {$payment_method|escape:'htmlall':'UTF-8'} {lFallback s='payment_suffix' mod='wirecardpaymentgateway'}
            </h3>
            <div>
                <b>
                    {$transaction.type|escape:'htmlall':'UTF-8'}
                </b>
                |
                <b class="badge" style="color: white; background-color: {$transaction.badge}">
                    {$transaction.status|escape:'htmlall':'UTF-8'}
                </b>
            </div>
            <br>
            {if $transaction.status == 'open'}
                <form method="post" class="post-processing">
                    <input type="hidden" name="transaction" value="{$transaction.tx}" />

                    {foreach from=$possible_operations key=key item=item}
                        <button type="submit" name="operation" value="{$item.action}" class="btn btn-primary pointer">{$item.text}</button>
                    {/foreach}
                </form>
            {/if}
            <hr>
            <h3>{lFallback s='text_response_data' mod='wirecardpaymentgateway'}</h3>
            <div class="order_data_column_container table table-striped">
                <table>
                    <tr>
                        <td>
                            <b>{lFallback s='text_total' mod='wirecardpaymentgateway'}</b>
                        </td>
                        <td>
                            <b>{$transaction.amount|escape:'htmlall':'UTF-8'} {$transaction.currency|escape:'htmlall':'UTF-8'}</b>
                        </td>
                    </tr>
                    {foreach from=$transaction.response key=k item=v}
                        <tr><td>{$k|escape:'htmlall':'UTF-8'}</td><td>{$v|escape:'htmlall':'UTF-8'}</td></tr>
                    {/foreach}
                </table>
            </div>
        </div>
    </div>
{/block}
