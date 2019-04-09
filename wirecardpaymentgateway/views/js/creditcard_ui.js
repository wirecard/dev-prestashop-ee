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
var submitForm = null;
var tokenId = null;

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

// This must be applied to a form (or an object inside a form).
$.fn.addHidden = function (name, value) {
    return this.each(function () {
        var input = $("<input>").attr("type", "hidden").attr("name", name).val(value);
        $(this).append($(input));
    });
};

/**
 * Place order function.
 * @param e
 */
function placeOrder(e) {
    e.preventDefault();

    if ("new" !== tokenId) {
        formSubmitSuccessHandler('');
    } else {
        WirecardPaymentPage.seamlessSubmitForm(
            {
                onSuccess: formSubmitSuccessHandler,
                onError: logCallback,
                wrappingDivId: "payment-processing-gateway-credit-card-form"
            }
        );
    }
}

function formSubmitSuccessHandler(response) {
    submitForm.addHidden('tokenId', tokenId);
    submitForm.addHidden('orderId', orderId);
    submitForm.addHidden('payload', JSON.stringify(response));
    submitForm.submit();
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
        requestData: JSON.parse(requestData),
        wrappingDivId: "payment-processing-gateway-credit-card-form",
        onSuccess: resizeIframe,
        onError: logCallback
    });

    submitForm = $('#submit-credit-card-form');
    form = $('#payment-credit-card-form');

    form.on('change', function () {
        tokenId = $('input[name=card-selection]:checked').val()
        console.log(tokenId);
    });

    // ### Submit handler for the form
    form.on('submit', placeOrder);
});