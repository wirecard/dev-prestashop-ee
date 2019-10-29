<?php
/**
 * Shop System Extensions:
 *  - Terms of Use can be found at:
 *  https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 *  - License can be found under:
 *  https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Classes\Transaction\Entity\Cart;

use WirecardEE\Prestashop\Helper\Logger;
use WirecardEE\Prestashop\Helper\StringHelper;

/**
 * Class CartItemCollection
 * @package WirecardEE\Prestashop\Classes\Transaction\Entity\Cart
 * @since 2.4.0
 */
class CartItemCollection implements \Countable, \Iterator, \ArrayAccess
{
    const WIRECARD_ORDER_ITEM_PREFIX = "order-items.0.order-item.";
    /**
     * @var array
     * @since 2.4.0
     */
    private $products = [];

    /**
     * @var int
     * @since 2.4.0
     */
    private $position = 0;

    /**
     * @return CartItemInterface
     * @since 2.4.0
     */
    public function current()
    {
        return $this->products[$this->position];
    }

    /**
     * @return int
     * @since 2.4.0
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * @since 2.4.0
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * @return bool
     * @since 2.4.0
     */
    public function valid()
    {
        return isset($this->products[$this->position]);
    }

    /**
     * @return int
     * @since 2.4.0
     */
    public function next()
    {
        return $this->position++;
    }

    /**
     * @return int|void
     * @since 2.4.0
     */
    public function count()
    {
        return count($this->products);
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     * @since 2.4.0
     */
    public function offsetSet($offset, $value)
    {
        if (!$value instanceof CartItemInterface) {
            throw new \InvalidArgumentException('Value must implement CartItemInterface');
        }

        if (empty($offset)) {
            $this->products[] = $value;
        }

        $this->products[$offset] = $value;
    }

    /**
     * @param mixed $offset
     * @return bool
     * @since 2.4.0
     */
    public function offsetExists($offset)
    {
        return isset($this->products[$offset]);
    }

    /**
     * @param mixed $offset
     * @since 2.4.0
     */
    public function offsetUnset($offset)
    {
        unset($this->products[$offset]);
    }

    /**
     * @param mixed $offset
     * @return mixed
     * @since 2.4.0
     */
    public function offsetGet($offset)
    {
        return $this->products[$offset];
    }

    /**
     * @param string $transactionRawData
     * @since 2.4.0
     */
    public function createProductCollectionFromTransactionData($transactionRawData)
    {
        $basket = $this->parseTransactionRawData($transactionRawData);

        foreach ($basket as $product) {
            $productData = new CartItem();
            $productData->createProductFromArray($product);
            $this->products[] = $productData;
        }
    }

    /**
     * @param string $rawData
     * @return array
     * @since 2.4.0
     */
    private function parseTransactionRawData($rawData)
    {
        $basket = [];
        $prefix = self::WIRECARD_ORDER_ITEM_PREFIX;
        // Decode raw data
        $transactionRawDataArr = json_decode($rawData, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $transactionRawDataArr = [];
            (new Logger())->error(json_last_error_msg());
        }

        // Filter data with specified prefix
        $transactionRawDataArr = array_filter($transactionRawDataArr, function($key) use ($prefix) {
            return strpos($key, $prefix) !== false;
        }, ARRAY_FILTER_USE_KEY);

        foreach ($transactionRawDataArr as $responseFieldName => $responseValue) {
            // Get field name after prefix
            $fieldNameWithoutPrefix = StringHelper::startFrom($responseFieldName, $prefix);
            // Unpack fieldName to index / fieldName
            list($index, $fieldName) = explode('.', $fieldNameWithoutPrefix);
            // Normalize field name
            $normalizedFieldName = StringHelper::replaceWith($fieldName, "-", "_");
            // Add element with specified index and fieldName to basket
            $basket[$index][$normalizedFieldName] = $responseValue;
        }

        return $basket;
    }
}
