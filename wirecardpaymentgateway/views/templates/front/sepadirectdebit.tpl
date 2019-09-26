{*
* Shop System Extensions:
* - Terms of Use can be found at:
* https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
* - License can be found under:
* https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
*}

<form id="payment-form" action="{$action_link}" method="POST">
<div id="payment-processing-gateway-sepa-form">
    <div class="form-group row">
        <label class="col-md-3 form-control-label">{lFallback s='first_name_input' mod='wirecardpaymentgateway'}</label>
        <div class="col-md-6">
            <input type="text" class="form-control" name="sepaFirstName" id="sepaFirstName" />
        </div>
        <div class="col-md-3 form-control-comment">
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-3 form-control-label">{lFallback s='last_name_input' mod='wirecardpaymentgateway'}</label>
        <div class="col-md-6">
            <input type="text" class="form-control" name="sepaLastName" id="sepaLastName" />
        </div>
        <div class="col-md-3 form-control-comment">
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-3 form-control-label required"> {lFallback s='iban_input' mod='wirecardpaymentgateway'}</label>
        <div class="col-md-6">
            <input type="tel" class="form-control" name="sepaIban" id="sepaIban" />
        </div>
        <div class="col-md-3 form-control-comment">
        </div>
    </div>

    {if $bicEnabled == 'true' }
        <div class="form-group row">
            <label class="col-md-3 form-control-label required"> {lFallback s='bic_input' mod='wirecardpaymentgateway'}</label>
            <div class="col-md-6">
                <input type="tel" class="form-control" name="sepaBic" id="sepaBic" />
            </div>
            <div class="col-md-3 form-control-comment">
            </div>
        </div>
    {/if}
</div>
<div id="sepaDialog">
    <div class="modal fade" id="sepaMandateModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header"></div>
                <div class="modal-body">
                    <table border="0" cellpadding="0" cellspacing="0" class="stretch">
                        <tr>
                            <td class="text11justify">
                                <table border="0" width="100%">
                                    <tr>
                                        <td class="text11justify">
                                            <i>{lFallback s='creditor' mod='wirecardpaymentgateway'}</i><br />
                                            {$creditorName|escape:'htmlall':'UTF-8'}
                                            {if strlen($creditorName)}
                                                ,
                                            {/if}
                                            {$creditorStoreCity|escape:'htmlall':'UTF-8'}
                                            {if strlen($creditorName) || strlen($creditorStoreCity)}<br />{/if}
                                            {lFallback s='creditor_id_input' mod='wirecardpaymentgateway'}: {$creditorId|escape:'htmlall':'UTF-8'}<br />
                                        </td>
                                        <td width="10%">&nbsp;</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <table border="0" width="100%">
                                    <tr>
                                        <td class="text11">
                                            <i>{lFallback s='debtor' mod='wirecardpaymentgateway'}</i><br />
                                            {lFallback s='debtor_acc_owner' mod='wirecardpaymentgateway'}: <span class="first_last_name"></span><br />
                                            {lFallback s='iban' mod='wirecardpaymentgateway'}: <span class="bank_iban"></span><br />
                                            {if $bicEnabled == true }
                                                {lFallback s='bic' mod='wirecardpaymentgateway'}: <span class="bank_bic"></span><br />
                                            {/if}
                                        </td>
                                        <td width="10%">&nbsp;</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td class="text11justify">
                                <table border="0" width="100%">
                                    <tr>
                                        <td class="text11justify">
                                            {lFallback s='sepa_text_1' mod='wirecardpaymentgateway'}
                                            {$creditorName|escape:'htmlall':'UTF-8'}
                                            {lFallback s='sepa_text_2' mod='wirecardpaymentgateway'}
                                            {$creditorName|escape:'htmlall':'UTF-8'} {$additionalText|escape:'htmlall':'UTF-8'}
                                            {lFallback s='sepa_text_2b' mod='wirecardpaymentgateway'}
                                        </td>
                                        <td width="10%">&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td class="text11justify">
                                            {lFallback s='sepa_text_3' mod='wirecardpaymentgateway'}
                                        </td>
                                        <td width="10%">&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td class="text11justify">
                                            {lFallback s='sepa_text_4' mod='wirecardpaymentgateway'}
                                            {$creditorName|escape:'htmlall':'UTF-8'}
                                            {lFallback s='sepa_text_5' mod='wirecardpaymentgateway'}
                                        </td>
                                        <td width="10%">&nbsp;</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td class="text11justify">
                                <table border="0" width="100%">
                                    <tr>
                                        <td class="text11justify">
                                            {if strlen($creditorStoreCity)}
                                                {$creditorStoreCity|escape:'htmlall':'UTF-8'},
                                            {/if}
                                            {$date|escape:'htmlall':'UTF-8'} <span class="first_last_name"></span>
                                        </td>
                                        <td width="10%">&nbsp;</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="modal-footer">
                    <div class="w-100" style="text-align: left;">
                        <input type="checkbox" id="sepaCheck">&nbsp;<label for="sepaCheck">{lFallback s='sepa_text_6' mod='wirecardpaymentgateway'}</label>
                    </div>
                    <button class="btn btn-primary" id="sepaCancelButton">{lFallback s='cancel' mod='wirecardpaymentgateway'}</button>
                    <button class="btn btn-primary disabled" id="sepaConfirmButton">{lFallback s='text_confirm' mod='wirecardpaymentgateway'}</button>
                </div>
            </div>
        </div>
    </div>
</div>
</form>
