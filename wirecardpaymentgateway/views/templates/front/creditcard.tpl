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

<script type="text/javascript" src="{$paymentPageScript|escape:'htmlall':'UTF-8'}"></script>
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

<button disabled id="stored-card" class="btn btn-primary" data-toggle="modal" data-target="#wirecard-ccvault-modal">{lFallback s='vault_use_existing_text' mod='wirecardpaymentgateway'}</button>
<div id="new-card-text" class="invisible">{lFallback s='selected_creditcard_info' mod='wirecardpaymentgateway'}</div>
<button id="new-card" class="invisible btn btn-primary">{lFallback s='vault_use_new_text' mod='wirecardpaymentgateway'}</button>
{/if}
<div id="payment-processing-gateway-credit-card-form">
</div>
{if $ccvaultenabled == 'true'}
    <label for="wirecard-store-card"><input type="checkbox" id="wirecard-store-card" /> {lFallback s='vault_save_text' mod='wirecardpaymentgateway'}</label>
{/if}