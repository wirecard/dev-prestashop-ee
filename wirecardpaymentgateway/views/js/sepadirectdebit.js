/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

var form = null;
var sepaCheck = false;

$(document).ready(function () {
    function processOrder()
    {
        if ($("#sepaCheck").is(":checked")) {
            form.submit();
        }
    }

    function checkChange()
    {
        $("#sepaConfirmButton").toggleClass("disabled");
    }

    function close()
    {
        $("#sepaMandateModal").modal("hide");
        $("#payment-confirmation button").removeAttr("disabled");
        $("#sepaCheck").prop("checked", false);
        $("#sepaConfirmButton").addClass("disabled");
    }

    function displayPopup()
    {
        let sepaModal = $("#sepaMandateModal");

        sepaModal.find(".first_last_name").text($("#sepaFirstName").val() + " " + $("#sepaLastName").val());
        sepaModal.find(".bank_iban").text($("#sepaIban").val());
        sepaModal.find(".bank_bic").text($("#sepaBic").val());
        sepaModal.modal("show");

        let cancelButton = document.getElementById("sepaCancelButton");
        cancelButton.addEventListener("click", close, false);

        let confirmButton = document.getElementById("sepaConfirmButton");
        confirmButton.addEventListener("click", processOrder, false);

        let checkBox = document.getElementById("sepaCheck");
        checkBox.addEventListener("change", checkChange, false);
    }

    function placeOrder(e)
    {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();

        displayPopup();
    }

    $("form").submit(function (event) {
        form = this;
        let paymentMethod = $("input[name='payment-option']:checked").data("module-name");
        if (paymentMethod === "wd-sepadirectdebit" && !sepaCheck) {
            placeOrder(event);
        }
    });
});









