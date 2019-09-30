<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

class Product
{

    const STATE_TEMP = 0;
    const STATE_SAVED = 1;

    /** @var string Tax name */
    public $tax_name;

    /** @var string Tax rate */
    public $tax_rate;

    /** @var int Manufacturer id */
    public $id_manufacturer;

    /** @var int Supplier id */
    public $id_supplier;

    /** @var int default Category id */
    public $id_category_default;

    /** @var int default Shop id */
    public $id_shop_default;

    /** @var string Manufacturer name */
    public $manufacturer_name;

    /** @var string Supplier name */
    public $supplier_name;

    /** @var string Name */
    public $name;

    /** @var string Long description */
    public $description;

    /** @var string Short description */
    public $description_short;

    /** @var int Quantity available */
    public $quantity = 0;

    /** @var int Minimal quantity for add to cart */
    public $minimal_quantity = 1;

    /** @var int|null Low stock for mail alert */
    public $low_stock_threshold = null;

    /** @var bool Low stock mail alert activated */
    public $low_stock_alert = false;

    /** @var string available_now */
    public $available_now;

    /** @var string available_later */
    public $available_later;

    /** @var float Price in euros */
    public $price = 0;

    public $specificPrice = 0;

    /** @var float Additional shipping cost */
    public $additional_shipping_cost = 0;

    /** @var float Wholesale Price in euros */
    public $wholesale_price = 0;

    /** @var bool on_sale */
    public $on_sale = false;

    /** @var bool online_only */
    public $online_only = false;

    /** @var string unity */
    public $unity = null;

    /** @var float price for product's unity */
    public $unit_price;

    /** @var float price for product's unity ratio */
    public $unit_price_ratio = 0;

    /** @var float Ecotax */
    public $ecotax = 0;

    /** @var string Reference */
    public $reference;

    /** @var string Supplier Reference */
    public $supplier_reference;

    /** @var string Location */
    public $location;

    /** @var string Width in default width unit */
    public $width = 0;

    /** @var string Height in default height unit */
    public $height = 0;

    /** @var string Depth in default depth unit */
    public $depth = 0;

    /** @var string Weight in default weight unit */
    public $weight = 0;

    /** @var string Ean-13 barcode */
    public $ean13;

    /** @var string ISBN */
    public $isbn;

    /** @var string Upc barcode */
    public $upc;

    /** @var string Friendly URL */
    public $link_rewrite;

    /** @var string Meta tag description */
    public $meta_description;

    /** @var string Meta tag keywords */
    public $meta_keywords;

    /** @var string Meta tag title */
    public $meta_title;

    /** @var bool Product statuts */
    public $quantity_discount = 0;

    /** @var bool Product customization */
    public $customizable;

    /** @var bool Product is new */
    public $new = null;

    /** @var int Number of uploadable files (concerning customizable products) */
    public $uploadable_files;

    /** @var int Number of text fields */
    public $text_fields;

    /** @var bool Product statuts */
    public $active = true;

    /** @var bool Product statuts */
    public $redirect_type = '';

    /** @var bool Product statuts */
    public $id_type_redirected = 0;

    /** @var bool Product available for order */
    public $available_for_order = true;

    /** @var string Object available order date */
    public $available_date = '0000-00-00';

    /** @var bool Will the condition select should be visible for this product ? */
    public $show_condition = false;

    /** @var string Enumerated (enum) product condition (new, used, refurbished) */
    public $condition;

    /** @var bool Show price of Product */
    public $show_price = true;

    /** @var bool is the product indexed in the search index? */
    public $indexed = 0;

    /** @var string ENUM('both', 'catalog', 'search', 'none') front office visibility */
    public $visibility;

    /** @var string Object creation date */
    public $date_add;

    /** @var string Object last modification date */
    public $date_upd;

    /*** @var array Tags */
    public $tags;

    /** @var int temporary or saved object */
    public $state = self::STATE_SAVED;

    /**
     * @var float Base price of the product
     *
     * @deprecated 1.6.0.13
     */
    public $base_price;

    public $id_tax_rules_group = 1;

    /**
     * We keep this variable for retrocompatibility for themes.
     *
     * @deprecated 1.5.0
     */
    public $id_color_default = 0;

    /**
     * @since 1.5.0
     *
     * @var bool Tells if the product uses the advanced stock management
     */
    public $advanced_stock_management = 0;
    public $out_of_stock;
    public $depends_on_stock;

    public $isFullyLoaded = false;

    public $cache_is_pack;
    public $cache_has_attachments;
    public $is_virtual;
    public $id_pack_product_attribute;
    public $cache_default_attribute;

    /**
     * @var string If product is populated, this property contain the rewrite link of the default category
     */
    public $category;

    /**
     * Type of delivery time.
     *
     * Choose which parameters use for give information delivery.
     * 0 - none
     * 1 - use default information
     * 2 - use product information
     *
     * @var int
     */
    public $additional_delivery_times = 1;

    /**
     * Delivery in-stock information.
     *
     * Long description for delivery in-stock product information.
     *
     * @var string
     */
    public $delivery_in_stock;

    /**
     * Delivery out-stock information.
     *
     * Long description for delivery out-stock product information.
     *
     * @var string
     */
    public $delivery_out_stock;

    public function __construct($id_product = null, $full = false, $id_lang = null, $id_shop = null, Context $context = null)
    {
        $this->id = $id_product;
    }

    /**
     * Check product availability.
     *
     * @param int $qty Quantity desired
     *
     * @return bool True if product is available with this quantity, false otherwise
     */
    public function checkQty($qty)
    {
        if ($qty == 2) {
            return false;
        }
        return true;
    }

}
