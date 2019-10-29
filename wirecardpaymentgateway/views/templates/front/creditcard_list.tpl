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
                    <b>{$strings.use}</b>
                </button>
                <button type="button" class="btn btn-danger" data-cardid="{$card.cc_id}">
                    <b>{$strings.delete}</b>
                </button>
            </td>
        </tr>
    {/foreach}

    <tr class="wd-card-row">
        <td>
            <label for="ccVaultId">Test token</label>
        </td>

        <td align="right">
            <button type="button" class="btn btn-success" data-tokenid="5168216323601006">
                <b>{$strings.use}</b>
            </button>
            <button type="button" disabled class="btn btn-danger" data-cardid="{$card.cc_id}">
                <b>{$strings.delete}</b>
            </button>
        </td>
    </tr>
</table>

