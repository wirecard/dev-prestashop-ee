<?php
/**
 * Shop System Extensions:
 *  - Terms of Use can be found at:
 *  https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 *  - License can be found under:
 *  https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Classes\Transaction\Adapter\Product;

/**
 * Class ProductDataCollection
 * @package WirecardEE\Prestashop\Classes\Transaction\Adapter\Product
 * @since 2.4.0
 */
class CartItemCollection implements \Countable, \Iterator, \ArrayAccess
{
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
        } else {
            $this->products[$offset] = $value;
        }
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
}
