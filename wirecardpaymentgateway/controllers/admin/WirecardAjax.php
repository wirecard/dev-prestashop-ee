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
    const TRANSLATION_FILE = "wirecardajax";

    /**
     * Handle ajax actions
     *
     * @since 1.0.0
     */
    public function postProcess()
    {
        if ('TestConfig' === Tools::getValue('action')) {
            $this->testCredentials();
        }
    }

    /**
     * Test filled in credentials.
     * @since 2.1.0
     */
    protected function testCredentials()
    {
        $method = $this->getPaymentMethodCode();
        $baseUrl = Tools::getValue($this->module->buildParamName($method, 'base_url'));
        $wppUrl = Tools::getValue($this->module->buildParamName($method, 'wpp_url'));
        $httpUser = Tools::getValue($this->module->buildParamName($method, 'http_user'));
        $httpPass = Tools::getValue($this->module->buildParamName($method, 'http_pass'));

        $config = new Config($baseUrl, $httpUser, $httpPass);
        $transactionService = new TransactionService($config, new Logger());
        try {
            $this->validateBaseUrl($baseUrl);
            $this->validateBaseUrl($wppUrl);
            $this->validateUrlConfiguration($method, $baseUrl, $wppUrl);
            // Validate Credentials should be the last check.
            $this->validateCredentials($transactionService);
            $message = $this->l('success_credentials');
            $this->sendResponse('ok', $message);
        } catch (InvalidArgumentException $exception) {
            $this->sendResponse('error', $exception->getMessage());
        }
    }

    /**
     * Get payment method code.
     * Needed for sofort payment
     * @return string
     * @since 2.1.0
     */
    protected function getPaymentMethodCode()
    {
        $method = Tools::getValue('method');
        if ($method === 'sofortbanking') {
            $method = 'sofort';
        }
        return $method;
    }

    /**
     *
     * Validate base Url.
     * It shouldn't have any path on the end of Url
     * @param string $baseUrl
     * @throws InvalidArgumentException
     * @since 2.1.0
     */
    protected function validateBaseUrl($baseUrl)
    {
        $parsedUrl = parse_url($baseUrl);
        if ('https' !== $parsedUrl['scheme'] || isset($parsedUrl['path'])) {
            $message = $this->l('error_credentials');
            throw new InvalidArgumentException($message);
        }
    }

    /**
     * Check if transaction service can connect to ee and the credentials are valid
     * @param TransactionService $transactionService
     * @throws InvalidArgumentException
     * @since 2.1.0
     */
    protected function validateCredentials($transactionService)
    {
        try {
            $success = $transactionService->checkCredentials();
        } catch (\Http\Client\Exception $exception) {
            $success = false;
        }
        if (!$success) {
            $message = $this->l('error_credentials');
            throw new InvalidArgumentException($message);
        }
    }

    /**
     * Check if base url and wpp url are on the same level (test or production)
     * @param string $method
     * @param string $baseUrl
     * @param string $wppUrl
     * @throws InvalidArgumentException
     * @since 2.1.0
     */
    protected function validateUrlConfiguration($method, $baseUrl, $wppUrl)
    {
        if (('creditcard' === $method) && !UrlConfigurationChecker::isUrlConfigurationValid($baseUrl, $wppUrl)) {
            $message = $this->l('warning_credit_card_url_mismatch');
            throw new InvalidArgumentException($message);
        }
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
