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

$(document).ready(
    function () {
        if ($('#payment-processing-gateway-credit-card-form').length > 0) {
            getRequestData();
        }
        $(document).on('submit', '#payment-form', function (e) {
            form = $(this);
            if (form.attr('action').search('creditcard') >= 0) {
                placeOrder(e);
            }
        });

        $("#wirecard-ccvault-modal").on('show.bs.modal', function () {
            form = $("#payment-form", $(this).closest(".additional-information").next(".js-payment-option-form"));
            getStoredCards();
        });

        function placeOrder(e) {
            if (token !== null) {
                return;
            } else {
                e.preventDefault();
                WirecardPaymentPage.seamlessSubmitForm(
                    {
                        onSuccess: formSubmitSuccessHandler,
                        onError: logCallback,
                        wrappingDivId: "payment-processing-gateway-credit-card-form"
                    }
                );
            }
        }

        function getStoredCards() {
            $.ajax({
                url: ccVaultURL + '?action=liststoredcards',
                type: "GET",
                dataType: "json",
                success: function (response) {
                    buildWcdStoredCardView(response);
                }
            });
        }

        function getRequestData() {
            $.ajax({
                url: configProviderURL + '?action=getcreditcardconfig',
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

        function renderForm(config) {
            WirecardPaymentPage.seamlessRenderForm({
                requestData: config,
                wrappingDivId: "payment-processing-gateway-credit-card-form",
                onSuccess: resizeIframe,
                onError: logCallback
            });
        }

        function resizeIframe() {
            $("#payment-processing-gateway-credit-card-form > iframe").height(550);
        }

        function logCallback(response) {
            console.error(response);
        }

        function formSubmitSuccessHandler(response) {
            token = response.token_id;
            var successHandler = function(token, form){
                $('<input>').attr(
                    {
                        type: 'hidden',
                        name: 'tokenId',
                        id: 'tokenId',
                        value: token
                    }
                ).appendTo(form);
                form.submit();
            };
            if(response.masked_account_number !== undefined) {
                $.ajax({
                    url: ccVaultURL + '?action=addcard&tokenid=' + token + '&maskedpan=' + response.masked_account_number,
                    type: "GET",
                    success: successHandler(token, form)
                });
            } else {
                successHandler(token, form);
            }
        }

        function buildWcdStoredCardView(response) {
            var table = $("#wirecard-ccvault-modal .modal-body table");
            table.find(".btn-danger").unbind('click');
            table.find(".btn-success").unbind('click');
            table.empty();

            for (var row in response) {
                var card = response[row];
                var tr = "<tr>";
                tr += "<td><label for='ccVaultId'>" + card.masked_pan + "</label></td>";
                tr += "<td><button class='btn btn-success' data-tokenid='" + card.token + "'><b>+</b></button>";
                tr += " <button class='btn btn-danger' data-cardid='" + card.cc_id + "'><b>-</b></button></td>";
                tr += "</tr>";
                table.append(tr);
            }

            table.find(".btn-danger").bind('click', function(){
                $.ajax({
                    url: ccVaultURL + '?action=deletecard&ccid=' + $(this).data('cardid'),
                    type: "GET",
                    dataType: "json",
                    success: function (response) {
                        buildWcdStoredCardView(response);
                    }
                });
            });

            table.find(".btn-success").bind('click', function(){
                formSubmitSuccessHandler({token_id:$(this).data('tokenid')});
            });
        }
    }
);




