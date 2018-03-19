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
var sepaCheck = false;
var popup = $('#sepaDialog');

$(document).ready(
    function () {
        /**
         * Create popup window
         */
        popup.dialog({
            autoOpen :false,
            modal: true,
            show: "blind",
            hide: "blind"
        });

        $(document).on('submit','#payment-form', function (e) {
            form = $(this);
            if (form.attr('action').search('sepa') >= 0) {
                placeOrder(e);
            }
        });
        function placeOrder(e)
        {
            if (sepaCheck) {
                return;
            } else {
                e.preventDefault();

                $.ajax({
                    url: ajaxsepaurl + '?action=sepamandate',
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
        }
        
        function displayPopup(html)
        {
            popup.html(html);
            popup.find('.first_last_name').text($('#sepaFirstName').val() + ' ' + $('#sepaLastName').val());
            popup.find('.bank_iban').text($('#sepaIban').val());
            popup.find('.bank_bic').text($('#sepaBic').val());
            popup.dialog({
                height: '800',
                width: 'auto'
            });
            popup.dialog('open');
            $('body').css('overflow', 'hidden');

            var button = document.getElementById('sepaButton');
            button.addEventListener('click', process_order, false);

            var check_box = document.getElementById('sepaCheck');
            check_box.addEventListener('change', check_change, false);
        }

        function process_order()
        {
            if ( document.getElementById('sepaCheck').checked ) {
                sepaCheck = true;
                $('#sepaFirstName').appendTo(form);
                $('#sepaLastName').appendTo(form);
                $('#sepaIban').appendTo(form);
                $('#sepaBic').appendTo(form);
                form.submit();
            } else {
                popup.dialog('close');
                $('body').css('overflow', 'auto');
            }
        }

        function check_change()
        {
            if ( document.getElementById('sepaCheck').checked ) {
                $('#sepaButton').text('Process');
            } else {
                $('#sepaButton').text('Cancel');
            }
        }
    }
);