{*
* Shop System Extensions:
* - Terms of Use can be found at:
* https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
* - License can be found under:
* https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
*}

<br>
<table class="table table-striped">
    <thead>
        <tr>
            <td scope="col" colspan="2"><b>{lFallback s='transfer_notice' mod='wirecardpaymentgateway'}</b></td>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>{lFallback s='amount' mod='wirecardpaymentgateway'}</td>
            <td>{$amount|string_format:"%.2f"} {$currency|escape:'htmlall':'UTF-8'}</td>
        </tr>
        <tr>
            <td>IBAN</td>
            <td>{$iban|escape:'htmlall':'UTF-8'}</td>
        </tr>
        <tr>
            <td>BIC</td>
            <td>{$bic|escape:'htmlall':'UTF-8'}</td>
        </tr>
        <tr>
            <td>{lFallback s='ptrid' mod='wirecardpaymentgateway'}</td>
            <td>{$refId|escape:'htmlall':'UTF-8'}</td>
        </tr>
    </tbody>
</table>
<br>