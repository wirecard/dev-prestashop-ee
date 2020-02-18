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
            <td id="cc-reuse-td">
                <span class="custom-radio">
                    <input type="radio" name="cc-reuse" id="{$card.cc_id}" value="{$card.token}"/>
                    <span></span>
                </span>
                    <label for="{$card.cc_id}" id="cc-reuse-mask">{$card.masked_pan}</label>
                </fieldset>
            </td>

            <td align="right">
                <button type="button" class="btn btn-danger" data-cardid="{$card.cc_id}">
                    <i class="material-icons delete">&#xE872;</i>
                </button>
            </td>
        </tr>
    {/foreach}
</table>

