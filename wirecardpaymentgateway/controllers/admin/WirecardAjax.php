<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 * @author Wirecard AG
 * @copyright Copyright (c) 2020 Wirecard AG, Einsteinring 35, 85609 Aschheim, Germany
 * @license MIT License
 */

require dirname(__FILE__) . '/../../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Response;
use Wirecard\PaymentSdk\Config\Config;
use Wirecard\PaymentSdk\TransactionService;
use WirecardEE\Prestashop\Helper\Logger;
use WirecardEE\Prestashop\Helper\Service\ShopConfigurationService;
use WirecardEE\Prestashop\Helper\TranslationHelper;
use WirecardEE\Prestashop\Helper\UrlConfigurationChecker;
use WirecardEE\Prestashop\Models\PaymentCreditCard;

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
        $message = $this->getTranslatedString('error_credentials');

        try {
            if ($this->validatePaymentMethod($base_url, $http_user, $http_pass, $wpp_url, $method)) {
                $status = 'ok';
                $message = $this->getTranslatedString('success_credentials');
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
        if ($method == PaymentCreditCard::TYPE && $status) {
            $status = $this->validateBaseUrl($wpp_url);
            if (!UrlConfigurationChecker::isUrlConfigurationValid($base_url, $wpp_url)) {
                throw new \Exception($this->getTranslatedString('warning_credit_card_url_mismatch'));
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
        $transaction_service = new TransactionService($config, new Logger());
        return $transaction_service->checkCredentials();
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
