$(document).ready(
    function() {
        //var url = "{$link->getModuleLink('wirecardpaymentgatewaycreditcard', 'ajaxgetcreditcardconfig', array())}";
        //function getRequestData() {
            $.ajax({
                url: url,
                type: "GET",
                success: function (response) {
                    console.log(response);
                    renderForm(response);
                }
            });
        //}

        function renderForm(config) {
            WirecardPaymentPage.seamlessRenderForm(
                {
                    requestData: config,
                    wrappingDivId: "payment-processing-gateway-credit-card-input",
                    onSuccess: resizeIframe,
                    onError: logCallback
                }
            );
        }

        function resizeIframe() {
            console.log("it works");
            $( "#payment-processing-gateway-credit-card-input > iframe" ).height( 550 );
        }

        function logCallback( response ) {
            console.error( response );
        }
    }
);
