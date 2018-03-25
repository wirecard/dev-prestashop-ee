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
                                        <i>{l s='Creditor' mod='wirecardpaymentgateway'}</i><br />
                                        {$creditorName}
                                        {if strlen($creditorName)}
                                            ,
                                        {/if}
                                        {$creditorStoreCity}
                                        {if strlen($creditorName) || strlen($creditorStoreCity)}<br />{/if}
                                        {l s='Creditor ID:' mod='wirecardpaymentgateway'}{$creditorId}<br />
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
                                        <i>{l s='Debtor' mod='wirecardpaymentgateway'}</i><br />
                                        {l s='Account owner:' mod='wirecardpaymentgateway'}<span class="first_last_name"></span><br />
                                        {l s='IBAN:' mod='wirecardpaymentgateway'}<span class="bank_iban"></span><br />
                                        {if $enableBic == true }
                                            {l s='BIC:' mod='wirecardpaymentgateway'}<span class="bank_bic"></span><br />
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
                                        {l s='I authorize the creditor ' mod='wirecardpaymentgateway'}
                                        {$creditorName}
                                        {l s=' to send instructions to my bank to collect one single direct debit from my account. At the same time I instruct my bank to debit my account in accordance with the instructions from the creditor ' mod='wirecardpaymentgateway'}
                                        {$creditorName} {$additionalText}
                                    </td>
                                    <td width="10%">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td class="text11justify">
                                        {l s='Note: As part of my rights, I am entitled to a refund under the terms and conditions of my agreement with my bank. A refund must be claimed within 8 weeks starting from the date on which my account was debited.' mod='wirecardpaymentgateway'}
                                    </td>
                                    <td width="10%">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td class="text11justify">
                                        {l s='I irrevocably agree that, in the event that the direct debit is not honored, or objection against the direct debit exists, my bank will disclose to the creditor ' mod='wirecardpaymentgateway'}
                                        {$creditorName}
                                        {l s=' my full name, address and date of birth.' mod='wirecardpaymentgateway'}
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
                                            {$creditorStoreCity},
                                        {/if}
                                        {$date}<span class="first_last_name"></span>
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
                    <input type="checkbox" id="sepaCheck">&nbsp;<label for="sepaCheck">{l s='I have read and accepted the SEPA Direct Debit Mandate information.' mod='wirecardpaymentgateway'}</label>
                </div>
                <button class="btn btn-primary" id="sepaCancelButton">{l s='Cancel' mod='wirecardpaymentgateway'}</button>
                <button class="btn btn-primary disabled" id="sepaConfirmButton">{l s='Confirm' mod='wirecardpaymentgateway'}</button>
            </div>
        </div>
    </div>
</div>