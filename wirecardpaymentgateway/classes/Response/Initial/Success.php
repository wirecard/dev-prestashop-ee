<?php
/**
 * Shop System Extensions:
 *  - Terms of Use can be found at:
 *  https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 *  - License can be found under:
 *  https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Classes\Response\Initial;

use WirecardEE\Prestashop\Classes\Response\Success as SuccessAbstract;

class Success extends SuccessAbstract
{
    public function process()
    {
        parent::process();

        if ($this->response->getPaymentMethod() === 'wiretransfer' &&
            $this->configuration_service->getField('payment_type') === 'pia') {
            $this->context_service->setPiaCookie($this->response);
        }

        \Tools::redirect(
            'index.php?controller=order-confirmation&id_cart='
            .$this->cart->id.'&id_module='
            .$this->module->id.'&id_order='
            .$this->order->id.'&key='
            .$this->customer->secure_key
        );
    }
}