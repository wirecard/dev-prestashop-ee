{*
* Shop System Extensions:
* - Terms of Use can be found at:
* https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
* - License can be found under:
* https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
*}

<table class="table table-hover">
    {foreach $cards as $card}
        <tr class="wd-card-row">
            <td>
                <label for="ccVaultId">{$card.masked_pan}</label>
            </td>

            <td align="right">
                <button type="button" class="btn btn-success" data-tokenid="{$card.token}">
                    <b>{lFallback s='vault_use_card_text' mod='wirecardpaymentgateway'}</b>
                </button>
                <button type="button" class="btn btn-danger" data-cardid="{$card.cc_id}">
                    <b>{lFallback s='vault_delete_card_text' mod='wirecardpaymentgateway'}</b>
                </button>
            </td>
        </tr>
    {/foreach}
</table>

