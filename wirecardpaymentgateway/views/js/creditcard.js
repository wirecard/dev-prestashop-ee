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
 * Customers are responsible for testing the plugin"s functionality before starting productive
 * operation.
 *
 * By installing the plugin into the shop system the customer agrees to these terms of use.
 * Please do not use the plugin if you do not agree to these terms of use!
 */

// Declaring global variables for ESLint and allowing console.error statements
/* global cartId, WPP, configProviderURL, ccVaultURL */
/* eslint no-console: ["error", {allow: ["error"]}] */

var cardToken = null;
var form = null;
var orderNumber = null;
var paymentMethod = null;
var wrappingDiv = "payment-processing-gateway-credit-card-form";

$(document).ready(
    function () {
        function formHandler(response, form)
        {
            for (var field in response) {
                if (response.hasOwnProperty(field)) {
                    var value = response[field.toString()];

                    jQuery("<input>")
                        .attr({
                            type: "hidden",
                            value: value,
                            name: field
                        })
                        .appendTo(form);
                }
            }

            if (response.hasOwnProperty("masked_account_number")) {
                jQuery("<input>")
                    .attr({
                        type: "hidden",
                        value: true,
                        name: "jsresponse"
                    })
                    .appendTo(form);
            }

            if (form !== null) {
                form.submit();
            }
        }

        function logCallback(response)
        {
            jQuery(document).off("submit", "#payment-form");

            formHandler(response, form);
        }

        function processAjaxUrl(url, params)
        {
            var querySign = "?";
            if (url.includes("?")) {
                querySign = "&";
            }
            params.forEach(function (param) {
                url += querySign + param.index + "=" + param.data;
                querySign = "&";
            });
            return url;
        }


        function enableCheckoutButtonIfPermitted()
        {
            var checkmarkWasChecked = $("#conditions-to-approve input[type=checkbox]").prop("checked");

            if (checkmarkWasChecked) {
                $("#payment-confirmation button").removeAttr("disabled");
            }
        }

        function resizeIframe()
        {
            $("#card-spinner").hide();
            $("#stored-card").removeAttr("disabled");

            enableCheckoutButtonIfPermitted();

            $("#" + wrappingDiv + " > iframe").height($(window).width() < 992 ? 410 : 390);
        }


        function renderForm(config)
        {
            // This is always the order id
            orderNumber = config.field_value_1;

            //Show card spinner if is hidden
            $("#card-spinner").show();

            // Since we already generated an order, add the new order id to every payment method.
            $(".js-payment-option-form form").append(
                jQuery("<input>")
                    .attr({
                        type: "hidden",
                        value: orderNumber,
                        name: "order_number"
                    })
            );

            WPP.seamlessRender({
                requestData: config,
                wrappingDivId: wrappingDiv,
                onSuccess: resizeIframe,
                onError: logCallback
            });
        }

        function getRequestData()
        {
            $.ajax({
                url: configProviderURL,
                data: {
                    action: "getSeamlessConfig",
                    "cartId": cartId
                },
                type: "GET",
                dataType: "json",
                success: function (response) {
                    renderForm(response);
                },
                error: function (response) {
                    console.error(response);
                }
            });
        }

        function formSubmitSuccessHandler(response)
        {
            cardToken = response.token_id;

            if (response.hasOwnProperty("masked_account_number")&& $("#wirecard-store-card").is(":checked")) {
                var params = [{
                    index: "action",
                    data: "addcard"
                }, {
                    index: "tokenid",
                    data: cardToken
                }, {
                    index: "maskedpan",
                    data: response.masked_account_number
                }];
                $.ajax({
                    url: processAjaxUrl(ccVaultURL, params),
                    type: "GET",
                    success: formHandler(response, form)
                });
            } else {
                formHandler(response, form);
            }
        }

        function formSubmitErrorHandler()
        {
            getRequestData();
        }

        function placeOrder(e)
        {
            if (cardToken === null && paymentMethod === "creditcard") {
                e.preventDefault();
                WPP.seamlessSubmit(
                    {
                        onSuccess: formSubmitSuccessHandler,
                        onError: formSubmitErrorHandler,
                        wrappingDivId: wrappingDiv
                    }
                );
            }
        }

        function buildWcdStoredCardView(response)
        {
            var table = $("#wirecard-ccvault-modal .modal-body table");
            table.find(".btn-danger").unbind("click");
            table.find(".btn-success").unbind("click");
            table.empty();

            for (var row in response.cards) {
                if (response.cards.hasOwnProperty(row)) {
                    var card = response.cards[row.toString()];
                    var tr = "<tr class='wd-card-row'>";
                    tr += "<td><label for='ccVaultId'>" + card.masked_pan + "</label></td>";
                    tr += "<td align='right'>";
                    tr += "<button type='button' class='btn btn-success' data-tokenid='" + card.token + "'>";
                    tr += "<b>" + response.strings.use + "</b>";
                    tr += "</button>";
                    tr += "<button type='button' class='btn btn-danger' data-cardid='" + card.cc_id + "'>";
                    tr += "<b>" + response.strings.delete + "</b>";
                    tr += "</button>";
                    tr += "</td></tr>";
                    table.append(tr);
                }
            }

            table.find(".btn-danger").bind("click", function () {
                var params = [{
                    index: "action",
                    data: "deletecard"
                }, {
                    index: "ccid",
                    data: $(this).data("cardid")
                }];

                $.ajax({
                    url: processAjaxUrl(ccVaultURL, params),
                    type: "GET",
                    dataType: "json",
                    success: function (response) {
                        buildWcdStoredCardView(response);
                    }
                });
            });

            table.find(".btn-success").bind("click", function () {
                cardToken = $(this).data("tokenid");

                $(".js-payment-option-form form").append(
                    jQuery("<input>")
                        .attr({
                            type: "hidden",
                            value: cardToken,
                            name: "token_id"
                        })
                );

                $("#payment-processing-gateway-credit-card-form").empty();
                $("#wirecard-vault").hide();
                $("#wirecard-ccvault-modal").modal('hide');
                $("#stored-card").hide();
                $("#new-card-text").show();
                $("#new-card").show();
            });
        }

        function getStoredCards()
        {
            var params = [{
                index: "action",
                data: "liststoredcards"
            }];
            $.ajax({
                url: processAjaxUrl(ccVaultURL, params),
                type: "GET",
                dataType: "json",
                success: function (response) {
                    buildWcdStoredCardView(response);
                }
            });
        }

        $(document).on("click", "input[name='payment-option']", function () {
            paymentMethod = $(this).data("module-name").replace("wd-", "");

            if ($("#" + wrappingDiv).children().length > 0) {
                return;
            }

            getRequestData();
        });

        $(document).on("submit", "#payment-form", function (e) {
            form = $(this);
            placeOrder(e);
        });

        $("#new-card").on("click", function () {
            getRequestData();
            $("#new-card").hide();
            $("#new-card-text").hide();
            $("#stored-card").show();
            $("#wirecard-vault").show();
        });

        $("#wirecard-ccvault-modal").on("show.bs.modal", function () {
            getStoredCards();
        });
    }
);






