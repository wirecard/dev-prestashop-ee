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

var form = null;


function processAjaxUrl(url, params) {
    let querySign = '?';
    if (url.includes("?")) {
        querySign = '&';
    }
    params.forEach(function (param) {
        url += querySign + param.index + '=' + param.data;
        querySign = '&';
    });
    return url;
}

function placeOrder(e) {
    e.preventDefault();
    WirecardPaymentPage.seamlessSubmitForm(
        {
            onSuccess: formSubmitSuccessHandler,
            onError: logCallback,
            wrappingDivId: "payment-processing-gateway-credit-card-form"
        }
    );
}

function formSubmitSuccessHandler(response) {
    console.log('Sending to prestashop:', response);
    $.ajax({
        type: 'POST',
        url: form.attr('action'),
        dataType: 'json',
        data: {
            orderId : orderId,
            payload: response,
            ajax: true
        },
        success: function (sucess) {
            console.log('juppii', sucess);
            window.location.href = sucess.url;
        },
        error: function (error) {
            console.log('error', error);
            window.location.href = error.url;
        }
    });

}

function logCallback(response) {
    console.log('Error:', response);
}

function resizeIframe() {
    $("#payment-processing-gateway-credit-card-form > iframe").height(350);
}

$(document).ready(function () {
    // This function will render the credit card UI in the specified div.
    WirecardPaymentPage.seamlessRenderForm({
        requestData: requestData,
        wrappingDivId: "payment-processing-gateway-credit-card-form",
        onSuccess: resizeIframe,
        onError: logCallback
    });

    // ### Submit handler for the form
    $(document).on('submit', '#payment-creditcard-form', function (e) {
        form = $(this);
        console.log('submit:', form);
        placeOrder(e);
    });
});