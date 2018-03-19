<?php
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
 *
 * @author Wirecard AG
 * @copyright Wirecard AG
 * @license GPLv3
 */

namespace WirecardEE\Prestashop\Helper;

/**
 * Class OrderManager
 *
 * @since 1.0.0
 */
class OrderManager
{
    const WIRECARD_OS_AWAITING = 'WIRECARD_OS_AWAITING';
    const WIRECARD_OS_AUTHORIZATION = 'WIRECARD_OS_AUTHORIZATION';

    private $module;

    /**
     * OrderManager constructor.
     *
     * @param \PaymentModule $module
     * @since 1.0.0
     */
    public function __construct($module)
    {
        $this->module = $module;
    }

    /**
     * Validate and create order with specific order state
     *
     * @param \Cart $cart
     * @param string $state
     * @param string $paymentType
     * @return \Order
     * @since 1.0.0
     */
    public function createOrder($cart, $state, $paymentType)
    {
        $this->module->validateOrder(
            $cart->id,
            \Configuration::get($state),
            $cart->getOrderTotal(true),
            $this->module->getConfigValue($paymentType, 'title'),
            null,
            array(),
            null,
            false,
            $cart->secure_key
        );

        return $this->module->currentOrder;
    }

    /**
     * Create a new order state with specific order state name
     *
     * @param string $stateName
     * @since 1.0.0
     */
    public function createOrderState($stateName)
    {
        if (!\Configuration::get($stateName)) {
            $orderStateInfo = $this->getOrderStateInfo($stateName);
            $orderState = new \OrderState();
            $orderState->name = array();
            foreach (\Language::getLanguages() as $language) {
                if (\Tools::strtolower($language['iso_code']) == 'de') {
                    $orderState->name[$language['id_lang']] = $orderStateInfo['de'];
                } else {
                    $orderState->name[$language['id_lang']] = $orderStateInfo['en'];
                }
            }
            $orderState->send_email = false;
            $orderState->color = 'lightblue';
            $orderState->hidden = false;
            $orderState->delivery = false;
            $orderState->logable = true;
            $orderState->module_name = 'wirecardpaymentgateway';
            $orderState->invoice = false;
            $orderState->add();

            \Configuration::updateValue(
                $stateName,
                (int)($orderState->id)
            );
        }
    }

    /**
     * Getter for language texts to specific order state
     *
     * @param $stateName
     * @return array
     * @since 1.0.0
     */
    private function getOrderStateInfo($stateName)
    {
        switch ($stateName) {
            case self::WIRECARD_OS_AUTHORIZATION:
                return array(
                    'de' => 'Wirecard Bezahlung authorisiert',
                    'en' => 'Wirecard payment authorized',
                );
            case self::WIRECARD_OS_AWAITING:
            default:
                return array(
                    'de' => 'Wirecard Bezahlung ausständig',
                    'en' => 'Wirecard payment awaiting'
                );
        }
    }
}
