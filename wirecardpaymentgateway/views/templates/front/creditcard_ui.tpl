{extends file='page.tpl'}

{block name='page_content'}
    <script src="{$paymentPageLoader}"></script>
    <script>
        let requestData = {$requestData};
        let orderId = {$orderId};
    </script>

    <div class="container">
        <form id="payment-creditcard-form" method="post" action="{$actionUrl}">
            <div id="payment-processing-gateway-credit-card-form">
            </div>
            <button type="submit" class="btn btn-primary">{l s='Save' d='Shop.Theme.Actions'}</button>
        </form>

        <form id="credit_card_submit_form" method="post" action="{$actionUrl}">
        </form>

    </div>
{/block}