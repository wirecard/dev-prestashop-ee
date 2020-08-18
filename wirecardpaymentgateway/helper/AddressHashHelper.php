<?php
/**
 * Shop System Extensions:
 *  - Terms of Use can be found at:
 *  https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 *  - License can be found under:
 *  https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Helper;

class AddressHashHelper
{
    /**
     * @param int $addressId
     * @return string
     * @since 2.12.0
     */
    public function getHashFromAddressId($addressId)
    {
        /** @var \Address $address */
        $address = new \Address($addressId);

        return md5($this->formStringForHashing($address));
    }

    /**
     * @param \Address $address
     * @return string mixed
     * @since 2.12.0
     */
    private function formStringForHashing($address)
    {
        return $address->lastname .
            $address->firstname .
            $address->address1 .
            $address->address2 .
            $address->postcode .
            $address->city;
    }
}
