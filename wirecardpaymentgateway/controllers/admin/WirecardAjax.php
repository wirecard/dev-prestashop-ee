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
use WirecardEE\Prestashop\Helper\TranslationHelper;
use WirecardEE\Prestashop\Helper\UrlConfigurationChecker;
use WirecardEE\Prestashop\Helper\Service\ShopConfigurationService;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class WirecardAjaxController
 *
 * @since 1.0.0
 */
class WirecardAjaxController extends ModuleAdminController
{
    use TranslationHelper;

    /** @var string */
    const TRANSLATION_FILE = 'wirecardajax';

    /** @var string  */
    const CONFIG_ACTION = 'TestConfig';

    /**
     * Handle ajax actions
     *
     * @since 1.0.0
     */
    public function postProcess()
    {
        if (self::CONFIG_ACTION === Tools::getValue('action')) {
            $this->testCredentials();
        }
    }

    /**
     * Test filled in credentials.
     * @since 2.1.0
     */
    protected function testCredentials()
    {
        $method = Tools::getValue('method');
        $shop_config = new ShopConfigurationService($method);

        $base_url = Tools::getValue($shop_config->getFieldName('base_url'));
        $wpp_url = Tools::getValue($shop_config->getFieldName('wpp_url'));
        $http_user = Tools::getValue($shop_config->getFieldName('http_user'));
        $http_pass = Tools::getValue($shop_config->getFieldName('http_pass'));

        $status = 'error';
        $message = $this->l('error_credentials');

        try {
            if ($this->validatePaymentMethod($base_url, $http_user, $http_pass, $wpp_url, $method)) {
                $status = 'ok';
                $message = $this->l('success_credentials');
            }
        } catch (\Exception $exception) {
            $message = $exception->getMessage();
        }

        $this->sendResponse($status, $message);
    }

    /**
     * @param string $base_url
     * @param string $http_user
     * @param string $http_pass
     * @param string $wpp_url
     * @param string $method
     *
     * @return bool
     * @throws \Http\Client\Exception
     * @since 2.1.0
     */
    private function validatePaymentMethod($base_url, $http_user, $http_pass, $wpp_url, $method)
    {
        $status = $this->validateBaseUrl($base_url) && $this->validateCredentials($base_url, $http_user, $http_pass);
        if ($method == 'creditcard' && $status) {
            $status = $this->validateBaseUrl($wpp_url);
            if (!UrlConfigurationChecker::isUrlConfigurationValid($base_url, $wpp_url)) {
                throw new \Exception($this->l('warning_credit_card_url_mismatch'));
            }
        }

        return $status;
    }

    /**
     * @param string $base_url
     * @param string $http_user
     * @param string $http_pass
     *
     * @return boolean
     * @throws \Http\Client\Exception
     * @since 2.1.0
     */
    private function validateCredentials($base_url, $http_user, $http_pass)
    {
        $config = new Config($base_url, $http_user, $http_pass);
        $transactionService = new TransactionService($config, new Logger());
        return $transactionService->checkCredentials();
    }

    /**
     * Check if the base url has a path if so return false
     * @param string $base_url
     * @return boolean
     * @since 2.1.0
     */
    private function validateBaseUrl($base_url)
    {
        $parsed_url = parse_url($base_url);
        if ('https' !== $parsed_url['scheme'] || isset($parsed_url['path'])) {
            return false;
        }

        return true;
    }

    /**
     * Send response
     * @param string $status
     * @param string $message
     * @since 2.1.0
     */
    protected function sendResponse($status, $message)
    {
        $content = json_encode(['status' => htmlspecialchars($status), 'message' => htmlspecialchars($message)]);
        $response = new Response($content);
        $response->sendContent();
    }
}
