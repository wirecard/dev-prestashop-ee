{*
* Shop System Extensions:
* - Terms of Use can be found at:
* https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
* - License can be found under:
* https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 * @author Wirecard AG
 * @copyright Copyright (c) 2020 Wirecard AG, Einsteinring 35, 85609 Aschheim, Germany
 * @license MIT License
*}

<table class="table table-hover">
    {foreach $cards as $card}
        <tr class="wd-card-row">
            <td id="cc-reuse-td">
                <span class="custom-radio">
                    <input type="radio" name="cc-reuse" id="{$card.cc_id|escape:'htmlall':'UTF-8'}" value="{$card.token|escape:'htmlall':'UTF-8'}"/>
                    <span></span>
                </span>
                    <label for="{$card.cc_id|escape:'htmlall':'UTF-8'}" id="cc-reuse-mask">{$card.masked_pan|escape:'htmlall':'UTF-8'}</label>
                </fieldset>
            </td>

            <td align="right">
                <button type="button" class="btn btn-danger" data-cardid="{$card.cc_id|escape:'htmlall':'UTF-8'}">
                    <i class="material-icons delete">&#xE872;</i>
                </button>
            </td>
        </tr>
    {/foreach}
</table>

