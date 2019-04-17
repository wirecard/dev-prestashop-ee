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
var wrapDivPayment = 'payment-processing-gateway-credit-card-form';
var saveCard = null;

/**
 * Set data to URL
 * @param url
 * @param params
 * @returns {*}
 */
function processAjaxUrl(url, params)
{
    var querySign = '?';
    if (url.includes("?")) {
        querySign = '&';
    }
    params.forEach(function (param) {
        url += querySign + param.index + '=' + param.data;
        querySign = '&';
    });
    url += querySign + 'ajax=true';
    return url;
}

/**
 * Add hidden fields to form.
 * This must be applied to a form (or an object inside a form).
 * @param name
 * @param value
 * @returns {*}
 */
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
function placeOrder(e)
{
    e.preventDefault();
    if (tokenId === undefined) {
        return;
    }
    if ("new" !== tokenId) {
        formSubmitSuccessHandler(JSON.parse(requestData));
    } else {
        WirecardPaymentPage.seamlessSubmitForm(
            {
                onSuccess: formSubmitSuccessHandler,
                onError: logCallback,
                wrappingDivId: wrapDivPayment
            }
        );
    }
}

/**
 * Submit to prestashop function
 * @param response
 */
function formSubmitSuccessHandler(response)
{
    submitForm.addHidden('saveCard', saveCard.prop("checked"));
    submitForm.addHidden('tokenId', tokenId);
    submitForm.addHidden('orderId', orderId);
    submitForm.addHidden('payload', JSON.stringify(response));
    submitForm.submit();
}

/**
 * Show Error in console
 * @param response
 */
function logCallback(response)
{
    alert(response.status_description_1);
    console.log('Error:', response);
}

/**
 * Resize Iframe
 */
function resizeIframe()
{
    let iframe = $("#" + wrapDivPayment + " > iframe");
    if ($(window).width() > 600) {
        iframe.height(300);
    } else {
        iframe.height(450);
    }
    $('#loader').hide();
}

/**
 * Render  WirecardPaymentPage seamlessRenderForm
 */
function seamlessRenderForm()
{
    $('#loader').show();
    WirecardPaymentPage.seamlessRenderForm({
        requestData: JSON.parse(requestData),
        wrappingDivId: wrapDivPayment,
        onSuccess: resizeIframe,
        onError: logCallback
    });
}

function removeCard(cardId)
{
    console.log('Remove card:', cardId);
    let params = [{
        index: 'action',
        data: 'deletecard'
    }, {
        index: 'ccid',
        data: cardId
    }];

    $.ajax({
        url: processAjaxUrl(submitForm.attr('action'), params),
        type: "GET",
        dataType: "json",
        success: function (response) {
            tokenId = undefined;
            $('#remove-card-row-' + cardId).remove();
        }
    });

}


/**
 * Cancel payment
 */
function cancel()
{
    submitForm.addHidden('orderId', orderId);
    submitForm.addHidden('cancel', true);
    submitForm.submit();
}

/**
 * Handle if vault is enabled and user has saved cards
 */
function handleVault()
{
    if ($('#accordion-card').length) {
        tokenId = $('input[name=card-selection]:checked').val();
        $('input[name=card-selection]').change(function () {
            tokenId = $('input[name=card-selection]:checked').val();
        });

        $('#collapse-existing-card').on('show.bs.collapse', function () {
            tokenId = $('input[name=card-selection]:checked').val();
            saveCard.prop('checked', false);
        });

        $('#collapse-new-card').on('show.bs.collapse', function () {
            tokenId = 'new';
        })
    } else {
        tokenId = 'new';
    }
}

/**
 * Set listeners.
 */
$(document).ready(function () {
    // This function will render the credit card UI in the specified div.
    saveCard = $('#saveCard');
    submitForm = $('#submit-credit-card-form');
    form = $('#payment-credit-card-form');
    // ### Submit handler for the form
    seamlessRenderForm();
    handleVault();
    form.on('submit', placeOrder);
});