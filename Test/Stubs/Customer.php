<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

class Customer
{
    /** @var int $id Customer ID */
    public $id;

    /** @var int $id_shop Shop ID */
    public $id_shop;

    /** @var int $id_shop_group ShopGroup ID */
    public $id_shop_group;

    /** @var string Secure key */
    public $secure_key;

    /** @var string protected note */
    public $note;

    /** @var int Gender ID */
    public $id_gender = 0;

    /** @var int Default group ID */
    public $id_default_group;

    /** @var int Current language used by the customer */
    public $id_lang;

    /** @var string Lastname */
    public $lastname;

    /** @var string Firstname */
    public $firstname;

    /** @var string Birthday (yyyy-mm-dd) */
    public $birthday = null;

    /** @var string e-mail */
    public $email;

    /** @var bool Newsletter subscription */
    public $newsletter;

    /** @var string Newsletter ip registration */
    public $ip_registration_newsletter;

    /** @var string Newsletter registration date */
    public $newsletter_date_add;

    /** @var bool Opt-in subscription */
    public $optin;

    /** @var string WebSite * */
    public $website;

    /** @var string Company */
    public $company;

    /** @var string SIRET */
    public $siret;

    /** @var string APE */
    public $ape;

    /** @var float Outstanding allow amount (B2B opt) */
    public $outstanding_allow_amount = 0;

    /** @var int Show public prices (B2B opt) */
    public $show_public_prices = 0;

    /** @var int Risk ID (B2B opt) */
    public $id_risk;

    /** @var int Max payment day */
    public $max_payment_days = 0;

    /** @var int Password */
    public $passwd;

    /** @var string Datetime Password */
    public $last_passwd_gen;

    /** @var bool Status */
    public $active = true;

    /** @var bool Status */
    public $is_guest = 0;

    /** @var bool True if carrier has been deleted (staying in database as deleted) */
    public $deleted = 0;

    /** @var string Object creation date */
    public $date_add;

    /** @var string Object last modification date */
    public $date_upd;

    public $years;
    public $days;
    public $months;

    /** @var int customer id_country as determined by geolocation */
    public $geoloc_id_country;
    /** @var int customer id_state as determined by geolocation */
    public $geoloc_id_state;
    /** @var string customer postcode as determined by geolocation */
    public $geoloc_postcode;

    /** @var bool is the customer logged in */
    public $logged = 0;

    /** @var int id_guest meaning the guest table, not the guest customer */
    public $id_guest;

    public $groupBox;

    /** @var string Unique token for forgot passsword feature */
    public $reset_password_token;

    /** @var string token validity date for forgot password feature */
    public $reset_password_validity;

    public function __construct($id = null)
    {
        if (!is_null($id)) {
            $this->birthday = '01-01-1980';
            $this->email = 'max.mustermann@email.com';
            $this->firstname = 'Max';
            $this->lastname = 'Mustermann';
            $this->date_add = '2019-06-03 09:49:57';
            $this->date_upd = '2019-06-09 19:09:27';
            $this->last_passwd_gen = '2019-08-09 10:59:01';
        }
    }

    public static function isGuest(){
     return false;
    }

    public static function getStats(){
        return array('last_visit' => '2019-08-04 02:37:40');
    }

}
