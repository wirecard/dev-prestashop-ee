{extends file='page.tpl'}

{include file='module:wirecardpaymentgateway/views/templates/front/creditcard_header.tpl'}

{block name='page_content'}
    <div class="container">
        {include file="module:wirecardpaymentgateway/views/templates/front/creditcard_form.tpl"}
        <form id="submit-credit-card-form" method="post" action="{$actionUrl}">
        </form>
    </div>
{/block}