var token = null;

$(document).ready(
    function() {
        $('#payment-processing-gateway-credit-card-form').parent().bind('display:block', getRequestData());
        $(document).on('submit','#payment-form', function (e) {
            placeOrder(e);
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

        function getRequestData() {
            $.ajax({
                url: url,
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
                }
            );
        }

        function resizeIframe() {
            $( "#payment-processing-gateway-credit-card-form > iframe" ).height( 550 );
        }

        function logCallback( response ) {
            console.error( response );
        }

        function formSubmitSuccessHandler(response) {
            console.log("token: " + response.token_id);
            token = response.token_id;
            $( '<input>' ).attr(
                {
                    type: 'hidden',
                    name: 'tokenId',
                    id: 'tokenId',
                    value: token
                }
            ).appendTo( '#payment-form' );
        }
    }
);
