{*
* Shop System Extensions:
* - Terms of Use can be found at:
* https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
* - License can be found under:
* https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
*}

<form id="payment-form" action="{$action_link}" method="POST">
<div id="payment-processing-gateway-ideal-form">
    <div class="form-group row">
        <label class="form-control-label required">{lFallback s='bank_label' mod='wirecardpaymentgateway'}</label>
        <select class="form-control" name="idealBankBic" id="idealBankBic" style="width:auto">
            {foreach $banks as $bank}
                <option value="{$bank.key|escape:'htmlall':'UTF-8'}">{$bank.label|escape:'htmlall':'UTF-8'}</option>
            {/foreach}
        </select>
    </div>
</div>
</form>
