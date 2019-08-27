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

require dirname(__FILE__) . '/../../vendor/autoload.php';

use Wirecard\PaymentSdk\Config\Config;
use Wirecard\PaymentSdk\TransactionService;
use WirecardEE\Prestashop\Helper\Logger;
use WirecardEE\Prestashop\Helper\UrlConfigurationChecker;
use WirecardEE\Prestashop\Helper\Service\ShopConfigurationService;

/**
 * Class WirecardAjaxController
 *
 * @since 1.0.0
 */
class WirecardAjaxController extends ModuleAdminController
{
    use \WirecardEE\Prestashop\Helper\TranslationHelper;

    /** @var string */
    const TRANSLATION_FILE = "wirecardajax";

    /**
     * Handle ajax actions
     *
     * @since 1.0.0
     */
    public function postProcess()
    {
        switch (Tools::getValue('action')) {
            case 'TestConfig':
                $method = Tools::getValue('method');
                $shopConfigService = new ShopConfigurationService($method);

                $baseUrl = Tools::getValue($shopConfigService->getFieldName('base_url'));
                $wppUrl = Tools::getValue($shopConfigService->getFieldName('wpp_url'));
                $httpUser = Tools::getValue($shopConfigService->getFieldName('http_user'));
                $httpPass = Tools::getValue($shopConfigService->getFieldName('http_pass'));
                
                $config = new Config($baseUrl, $httpUser, $httpPass);
                $transactionService = new TransactionService($config, new Logger());

                $status = 'error';
                $message = $this->l('error_credentials');

                if (('creditcard' === $method) && UrlConfigurationChecker::isUrlConfigurationValid($baseUrl, $wppUrl)) {
                    $message = $this->l('warning_credit_card_url_mismatch');
                }

                if ($transactionService->checkCredentials()) {
                    $status = 'ok';
                    $message = $this->l('success_credentials');
                }

                die(\Tools::jsonEncode(
                    [
                        'status' => htmlspecialchars($status),
                        'message' => htmlspecialchars($message)
                    ]
                ));
        }
    }
}
