{extends file='page.tpl'}

{block name='header'}
    {block name='header_nav'}
        <nav class="header-nav">
            <div class="container">
                <div class="row">
                    <div class="col-md-6 hidden-sm-down" id="_desktop_logo">
                        <a href="{$urls.base_url}">
                            <img class="logo img-responsive" src="{$shop.logo}"
                                 alt="{$shop.name} {l s='logo' d='Shop.Theme.Global'}">
                        </a>
                    </div>
                    <div class="col-md-6 text-xs-right hidden-sm-down">
                        {hook h='displayNav1'}
                    </div>
                    <div class="hidden-md-up text-sm-center mobile">
                        {hook h='displayNav2'}
                        <div class="float-xs-left" id="menu-icon">
                            <i class="material-icons">&#xE5D2;</i>
                        </div>
                        <div class="float-xs-right" id="_mobile_cart"></div>
                        <div class="float-xs-right" id="_mobile_user_info"></div>
                        <div class="top-logo" id="_mobile_logo"></div>
                        <div class="clearfix"></div>
                    </div>
                </div>
            </div>
        </nav>
    {/block}
{/block}

{block name='page_content'}
    <div class="container">
        <form id="payment-credit-card-form" method="post">
            {if 'true' == $ccvaultenabled}
                {l s='Credit card selection' mod='Modules.WirecardPaymentGateway'}
                <div class="card-selection">

                    {foreach from=$userCards  key=key item=value name=userCards}
                        <div>
                            <label for="card-{$value.cc_id|escape:'htmlall':'UTF-8'}">
                                <input type="radio"
                                       id="card-{$value.cc_id|escape:'htmlall':'UTF-8'}"
                                       name="card-selection"
                                       value="{$value.token|escape:'htmlall':'UTF-8'}"
                                        {if $smarty.foreach.userCards.first} checked="checked" {/if}
                                />
                                {$value.masked_pan|escape:'htmlall':'UTF-8'}
                                <button type="button" class='btn btn-danger remove-card'
                                        id="remove-card-{$value.cc_id}"
                                        onclick="removeCard({$value.cc_id})"><b>-</b></button>
                                </br>
                            </label>
                            <br/>
                        </div>
                    {/foreach}
                    <div>
                        <label for="card-new">
                            <input type="radio"
                                   id="card-new"
                                   name="card-selection"
                                   value="new">
                            {l s='Use new credit card' mod='Modules.WirecardPaymentGateway'}</label>

                        <div id="payment-processing-gateway-credit-card-form">
                        </div>
                        <img id="loader" style="display: none" src="{$smarty.const._PS_ADMIN_IMG_}ajax-loader.gif">
                    </div>
                </div>
            {else}
                <div id="payment-processing-gateway-credit-card-form">
                </div>
                <img id="loader" style="display: none" src="{$smarty.const._PS_ADMIN_IMG_}ajax-loader.gif">
            {/if}
            <button type="submit" class="btn btn-primary">{l s='Pay' mod='Modules.WirecardPaymentGateway'}</button>
        </form>

        <form id="submit-credit-card-form" method="post" action="{$actionUrl}">
            <span class="checkbox"><input id="saveCard" name="saveCard" type="checkbox"></span>
            <label for="saveCard">{l s='Save for later use.' mod='Modules.WirecardPaymentGateway'}</label>
        </form>
    </div>
{/block}