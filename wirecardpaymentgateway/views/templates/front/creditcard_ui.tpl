{extends file='page.tpl'}

{include file='module:wirecardpaymentgateway/views/templates/front/creditcard_header.tpl'}

{block name='page_content'}
    <div class="container">
        <h2>{l s='heading_creditcard_form' mod='wirecardpaymentgateway'}</h2>
        <form id="payment-credit-card-form" method="post">
            {include file="module:wirecardpaymentgateway/views/templates/front/creditcard_accordion.tpl"}
            <button type="button" class="btn btn-secondary" onclick="cancel()">
                {l s='cancel' mod='wirecardpaymentgateway'}</button>
            <button type="submit"
                    class="btn btn-primary float-xs-right">{l s='pay' mod='wirecardpaymentgateway'}</button>
        </form>

        <form id="submit-credit-card-form" method="post" action="{$actionUrl}">
        </form>
    </div>
{/block}