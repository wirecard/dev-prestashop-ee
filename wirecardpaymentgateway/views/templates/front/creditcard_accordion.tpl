{if 'true' == $ccvaultenabled and $userCards|@count gt 0}
    <!--Accordion wrapper-->
    <div class="accordion md-accordion" id="accordion-card" role="tablist" aria-multiselectable="false">
        <!-- Accordion card -->
        <div class="card">
            <!-- Card header -->
            <div class="card-header" role="tab" id="heading-existing-card">
                <a data-toggle="collapse" data-parent="#accordion-card" href="#collapse-existing-card"
                   aria-expanded="true"
                   aria-controls="collapse-existing-card">
                    <h5 class="mb-0">
                        {l s='vault_use_existing_text' mod='wirecardpaymentgateway'}<i
                                class="fas fa-angle-down rotate-icon"></i>
                    </h5>
                </a>
            </div>
            <!-- Card body -->
            <div id="collapse-existing-card" class="collapse show in" role="tabpanel"
                 aria-labelledby="heading-existing-card"
                 data-parent="#accordion-card">
                <div class="card-body">
                    <table id="vault-table" class="table">
                        <thead>
                        <tr>
                            <th></th>
                            <th>{l s='card_number' mod='wirecardpaymentgateway'}</th>
                            <th>{l s='delete_credit_card' mod='wirecardpaymentgateway'}</th>
                        </tr>
                        </thead>
                        <tbody>
                        {foreach from=$userCards  key=key item=value name=userCards}
                            <tr id="remove-card-row-{$value.cc_id|escape:'htmlall':'UTF-8'}">
                                <td class="wd-card-selector">
                                    <input type="radio"
                                           id="card-{$value.cc_id|escape:'htmlall':'UTF-8'}"
                                           name="card-selection"
                                           value="{$value.token|escape:'htmlall':'UTF-8'}"
                                            {if $smarty.foreach.userCards.first} checked="checked" {/if}
                                    />
                                </td>
                                <td class="wd-card-number">
                                    <label for="card-{$value.cc_id|escape:'htmlall':'UTF-8'}">{$value.masked_pan|escape:'htmlall':'UTF-8'}</label>
                                </td>
                                <td class="wd-card-delete">
                                    <button type="button" class='btn btn-danger remove-card'
                                            id="remove-card-{$value.cc_id}"
                                            onclick="removeCard({$value.cc_id})"> {l s='delete' mod='wirecardpaymentgateway'}</button>
                                    </button>
                                    <img id="delete-loader-{$value.cc_id}" style="display: none"
                                         src="{$smarty.const._PS_ADMIN_IMG_}ajax-loader.gif">
                                </td>
                            </tr>
                        {/foreach}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- Accordion card -->

        <!-- Accordion card -->
        <div class="card">
            <!-- Card header -->
            <div class="card-header" role="tab" id="heading-new-card">
                <a class="collapsed" data-toggle="collapse" data-parent="#accordion-card"
                   href="#collapse-new-card"
                   aria-expanded="false" aria-controls="collapse-new-card">
                    <h5 class="mb-0">
                        {l s='vault_use_new_text' mod='wirecardpaymentgateway'}<i
                                class="fas fa-angle-down rotate-icon"></i>
                    </h5>
                </a>
            </div>

            <!-- Card body -->
            <div id="collapse-new-card" class="collapse" role="tabpanel" aria-labelledby="heading-new-card"
                 data-parent="#accordion-card">
                <div class="card-body">
                    <div id="payment-processing-gateway-credit-card-form">
                    </div>
                    <img id="loader" style="display: none"
                         src="{$smarty.const._PS_ADMIN_IMG_}ajax-loader.gif">
                    <div class="save-card-container">
                        <div class="row">
                            <div class="col-xs-12">
                                <input id="saveCard" name="saveCard" type="checkbox">
                                <label for="saveCard">{l s='vault_save_text' mod='wirecardpaymentgateway'}</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Accordion card -->
    </div>
    <!-- Accordion wrapper -->
{else}
    <div id="payment-processing-gateway-credit-card-form">
    </div>
    <img id="loader" style="display: none" src="{$smarty.const._PS_ADMIN_IMG_}ajax-loader.gif">
    {if 'true' == $ccvaultenabled}
        <div class="save-card-container">
            <div class="row">
                <div class="col-xs-12">
                    <input id="saveCard" name="saveCard" type="checkbox">
                    <label for="saveCard">{l s='vault_save_text' mod='wirecardpaymentgateway'}</label>
                </div>
            </div>
        </div>
    {/if}
{/if}