/**
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
 */

var token = null;
var form = null;

function processAjaxUrl(url, params) {
    var querySign = '?';
    if (url.includes("?")) {
        querySign = '&';
    }
    params.forEach(function (param) {
        url += querySign + param.index + '=' + param.data;
        querySign = '&';
    });
    return url;
}

$(document).ready(
    function () {
        if ($('#payment-processing-gateway-upi-form').length > 0) {
            getRequestData();
        }
        $(document).on('submit','#payment-form', function (e) {
            form = $(this);
            if (form.attr('action').search('unionpayinternational') >= 0) {
                placeOrder(e);
            }
        });

        function placeOrder(e)
        {
            if (token !== null) {
                return;
            } else {
                e.preventDefault();
                WirecardPaymentPage.seamlessSubmitForm(
                    {
                        onSuccess: formSubmitSuccessHandler,
                        onError: logCallback,
                        wrappingDivId: "payment-processing-gateway-upi-form"
                    }
                );
            }
        }

        function getRequestData()
        {
            var params = [{
                index: 'action',
                data: 'getupiconfig'
            }];
            $.ajax({
                url: processAjaxUrl(configProviderURL, params),
                type: "GET",
                dataType: 'json',
                success: function (response) {
                    renderForm(JSON.parse(response));
                },
                error: function (response) {
                    console.log(response);
                }
            });
        }

        function renderForm(config)
        {
            WirecardPaymentPage.seamlessRenderForm({
                requestData: config,
                wrappingDivId: "payment-processing-gateway-upi-form",
                onSuccess: resizeIframe,
                onError: logCallback
            });
        }

        function resizeIframe()
        {
            $("#payment-processing-gateway-upi-form > iframe").height(550);
        }

        function logCallback(response)
        {
            console.error(response);
        }

        function formSubmitSuccessHandler(response)
        {
            token = response.token_id;
            $('<input>').attr(
                {
                    type: 'hidden',
                    name: 'tokenId',
                    id: 'tokenId',
                    value: token
                }
            ).appendTo(form);
            form.submit();
        }
    }
);




