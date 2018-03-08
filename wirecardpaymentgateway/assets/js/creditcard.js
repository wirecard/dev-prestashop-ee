$(document).ready(
    function () {
        //var url = "{$link->getModuleLink('wirecardpaymentgatewaycreditcard', 'ajaxgetcreditcardconfig', array())}";
        $.ajax({
            url : url,
            type: "GET",
            success : function(response){
                console.log(response);
                renderForm();
            }
        });

        function renderForm($config) {

        }

    }
);
