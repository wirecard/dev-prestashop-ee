{*
* Shop System Extensions:
* - Terms of Use can be found at:
* https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
* - License can be found under:
* https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
*}

<form id="payment-form" action="{$action_link}" method="POST">
{if $ccvaultenabled == 'true'}
<div class="modal fade" id="wirecard-ccvault-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="{lFallback s='text_close' mod='wirecardpaymentgateway'}">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h2>{lFallback s='text_creditcard_selection' mod='wirecardpaymentgateway'}</h2>
            </div>
            <div class="modal-body">
                <table class="table table-hover">

                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{lFallback s='cancel' mod='wirecardpaymentgateway'}</button>
            </div>
        </div>
    </div>
</div>
    <p>
        <button disabled type="button" id="stored-card" class="btn btn-primary" data-toggle="modal"
                data-target="#wirecard-ccvault-modal">{lFallback s='vault_use_existing_text' mod='wirecardpaymentgateway'}</button>
    </p>
    <p id="new-card-text"
       style="display: none">{lFallback s='selected_creditcard_info' mod='wirecardpaymentgateway'}
    </p>
    <p>
        <button type="button" id="new-card" style="display: none"
                class="btn btn-primary">{lFallback s='vault_use_new_text' mod='wirecardpaymentgateway'}</button>
    </p>
{/if}
<p id="card-spinner" class="wd-loader"></p>
<div id="payment-processing-gateway-credit-card-form">
</div>
{if $ccvaultenabled == 'true'}
    <div id="wirecard-vault"><p><label for="wirecard-store-card"><input type="checkbox" id="wirecard-store-card" /> {lFallback s='vault_save_text' mod='wirecardpaymentgateway'}</label></p></div>
{/if}
</form>
