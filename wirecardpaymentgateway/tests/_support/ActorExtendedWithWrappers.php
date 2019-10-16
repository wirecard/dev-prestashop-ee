<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/magento2-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/magento2-ee/blob/master/LICENSE
 */

namespace Wirecard\ElasticEngine\tests\_support;

class ActorExtendedWithWrappers extends \Codeception\Actor
{
//    use _generated\ExtendedActorActions;

    /**
     * Method preparedFillField
     * @param string $field
     * @param string $value
     * @since 2.3.0
     */
    public function preparedFillField($field, $value)
    {
        $this->waitForElementVisible($field);
        $this->fillField($field, $value);
    }
    /**
     * Method preparedClick
     * @param string $link
     * @param string $context
     * @since 2.3.0
     */
    public function preparedClick($link, $context = null)
    {
        $this->waitForElementVisible($link);
        $this->waitForElementClickable($link);
        $this->click($link, $context);
    }

    /**
     * Method preparedClick
     * @param string $select
     * @param string $option
     * @since 2.3.0
     */
    public function preparedSelectOption($select, $option)
    {
        $this->waitForElementVisible($select);
        $this->waitForElementClickable($select);
        $this->selectOption($select, $option);
    }
}
