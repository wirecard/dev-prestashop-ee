<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use Codeception\Lib\Generator\PageObject;

class Acceptance extends \Codeception\Module
{


    /**
     * Method getDataFromDataFile
     * @param string $fileName
     * @return string
     *
     * @since 1.3.4
     */
    public static function getDataFromDataFile($fileName)
    {
        // decode the JSON feed
        $json_data = json_decode(file_get_contents($fileName));
        if (! $json_data) {
            $error = error_get_last();
            echo 'Failed to get customer data from '. $fileName .'. Error was: ' . $error['message'];
        } else {
            return $json_data;
        }
    }

    /**
     * Method fillFieldsWithData
     *
     * @param string $dataType
     * @param PageObject $page
     *
     * @since 2.0.1
     */
    public static function fillFieldsWithData($dataType, $page)
    {
        switch(true) {
            case stripos($dataType, 'customer data with password') !== false:
                $page->fillCustomerDetails(true);
                break;
            case stripos($dataType, 'customer data') !== false:
                $page->fillCustomerDetails();
                break;
            case stripos($dataType, 'credit card') !== false:
                $page->fillCreditCardDetails();
                break;
            case stripos($dataType, 'sign in') !== false:
                $page->fillSignInDetails();
                break;
            default:
                $page->fillBillingDetails();
        }
    }
}
