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