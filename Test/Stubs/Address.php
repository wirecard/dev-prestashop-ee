<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

class Address
{
    /** @var int Customer ID which address belongs to */
    public $id_customer = null;

    /** @var int Manufacturer ID which address belongs to */
    public $id_manufacturer = null;

    /** @var int Supplier ID which address belongs to */
    public $id_supplier = null;

    /**
     * @since 1.5.0
     *
     * @var int Warehouse ID which address belongs to
     */
    public $id_warehouse = null;

    /** @var int Country ID */
    public $id_country;

    /** @var int State ID */
    public $id_state;

    /** @var string Country name */
    public $country;

    /** @var string Alias (eg. Home, Work...) */
    public $alias;

    /** @var string Company (optional) */
    public $company;

    /** @var string Lastname */
    public $lastname;

    /** @var string Firstname */
    public $firstname;

    /** @var string Address first line */
    public $address1;

    /** @var string Address second line (optional) */
    public $address2;

    /** @var string Postal code */
    public $postcode;

    /** @var string City */
    public $city;

    /** @var string Any other useful information */
    public $other;

    /** @var string Phone number */
    public $phone;

    /** @var string Mobile phone number */
    public $phone_mobile;

    /** @var string VAT number */
    public $vat_number;

    /** @var string DNI number */
    public $dni;

    /** @var string Object creation date */
    public $date_add;

    /** @var string Object last modification date */
    public $date_upd;

    /** @var bool True if address has been deleted (staying in database as deleted) */
    public $deleted = 0;

    public function __construct($addressId)
    {
        $this->date_add = '2019-08-09 10:59:01';
    }

}
