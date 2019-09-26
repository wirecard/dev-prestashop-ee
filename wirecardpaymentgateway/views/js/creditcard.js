/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
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






