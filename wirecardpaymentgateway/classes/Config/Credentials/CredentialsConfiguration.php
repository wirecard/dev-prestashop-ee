<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Classes\Config\Credentials;

use Psr\Log\LoggerInterface;
use Wirecard\Credentials\Credentials;
use Wirecard\Credentials\PaymentMethod;

class CredentialsConfiguration
{
    /** @var CredentialsConfiguration */
    private static $instance;

    /** @var string */
    private $filePath;

    /** @var LoggerInterface */
    private $logger;

    /**
     * Wrapper constructor.
     * @param string $xmlFilePath
     * @param LoggerInterface $logger
     * @since 2.9.0
     */
    private function __construct($xmlFilePath, LoggerInterface $logger)
    {
        $this->filePath = $xmlFilePath;
        $this->logger = $logger;
    }

    /**
     * @param string $xmlFilePath
     * @param LoggerInterface $logger
     * @return CredentialsConfiguration
     */
    public static function getInstance($xmlFilePath, $logger)
    {
        if (null === self::$instance) {
            self::$instance = new self($xmlFilePath, $logger);
        }

        return self::$instance;
    }

    /**
     * @return string
     * @since 2.9.0
     */
    public function getFilePath()
    {
        return $this->filePath;
    }

    /**
     * @param string $paymentMethodCode
     * @return \Wirecard\Credentials\Config\CredentialsConfigInterface|\Wirecard\Credentials\Config\CredentialsCreditCardConfig
     * @since 2.9.0
     */
    public function getConfigByPaymentMethod($paymentMethodCode)
    {
        $config = null;
        try {
            $credentials = new Credentials($this->getFilePath());
            $config = $credentials->getConfigByPaymentMethod(new PaymentMethod($paymentMethodCode));
        } catch (\Exception $ex) {
            $this->logger->error($ex->getMessage());
        }

        return $config;
    }
}
