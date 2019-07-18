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
 * @author    WirecardCEE
 * @copyright WirecardCEE
 * @license   GPLv3
 */
var form = null;
var sepaCheck = false;

$(document).ready(
    function () {
        $('form').submit(function (event) {
            form = $(this);
            let paymentMethod = $('input[name="payment-option"]:checked').data('module-name');
            if (paymentMethod === 'wd-sepadirectdebit') {
                placeOrder(event);
            }
        });

        function placeOrder(e)
        {
            if (sepaCheck) {
                return;
            }
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();

            let params = [{
                index: 'action',
                data: 'sepamandate'
            }];
            $.ajax({
                url: processAjaxUrl(ajaxsepaurl, params),
                type: "GET",
                dataType: 'json',
                success: function (response) {
                    displayPopup(response.html);
                },
                error: function (response) {
                    console.log(response);
                }
            });
        }

        function displayPopup(html)
        {
            if (document.getElementById('sepaMandateModal')) {
                console.log("delete");
                document.getElementById('sepaMandateModal').remove();
            }

            $('body').append(html);
            let sepaModal = $('#sepaMandateModal');
            sepaModal.find('.first_last_name').text($('#sepaFirstName').val() + ' ' + $('#sepaLastName').val());
            sepaModal.find('.bank_iban').text($('#sepaIban').val());
            sepaModal.find('.bank_bic').text($('#sepaBic').val());
            sepaModal.modal('show');

            let cancelButton = document.getElementById('sepaCancelButton');
            cancelButton.addEventListener('click', close, false);

            let confirmButton = document.getElementById('sepaConfirmButton');
            confirmButton.addEventListener('click', process_order, false);

            let check_box = document.getElementById('sepaCheck');
            check_box.addEventListener('change', check_change, false);
        }

        function process_order()
        {
            sepaCheck = true;
            $('#sepaFirstName').attr('type', 'hidden').appendTo(form);
            $('#sepaLastName').attr('type', 'hidden').appendTo(form);
            $('#sepaIban').attr('type', 'hidden').appendTo(form);
            $('#sepaBic').attr('type', 'hidden').appendTo(form);
            form.submit();
        }

        function check_change()
        {
            $('#sepaConfirmButton').toggleClass('disabled');
        }

        function close()
        {
            $("#sepaMandateModal").modal('hide');
        }
    }
);









