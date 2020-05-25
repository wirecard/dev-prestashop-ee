<?php


namespace WirecardEE\Prestashop\Helper;

trait NumericHelper
{

    /**
     * Decides whether two float numbers are equal, given a precision.
     * @param $firstNumber
     * @param $secondNumber
     * @param null|int $precision
     * @return bool
     *
     * No validation is done here, because it's a private method. The class using it has more context to decide what
     * kind of validation is necessary.
     */
    private function equals($firstNumber, $secondNumber, $precision = null)
    {
        if ($precision === null) {
            $precision = (int)\Configuration::get('PS_PRICE_DISPLAY_PRECISION');
        }
        $integerCoefficient = pow(10, $precision);
        $fractionalCoefficient = pow(10, -1 * $precision);
        $threshold = $integerCoefficient * $fractionalCoefficient;
        $firstNumber *= $integerCoefficient;
        $secondNumber *= $integerCoefficient;
        $difference = abs($firstNumber - $secondNumber);
        return $difference < $threshold;
    }

    /**
     * @param float $firstNumber
     * @param float $secondNumber
     * @param null $precision If null, use prestashop's default
     * @return float|int
     *
     * Work with integers instead of floats, which makes rounding a safe operation, and thus the final division.
     */
    private function difference($firstNumber, $secondNumber, $precision = null)
    {
        if ($precision === null) {
            $precision = (int)\Configuration::get('PS_PRICE_DISPLAY_PRECISION');
        }
        $integerCoefficient = pow(10, $precision);
        $firstNumber *= $integerCoefficient;
        $secondNumber *= $integerCoefficient;
        $diff = round($firstNumber - $secondNumber);
        return $diff / $integerCoefficient;
    }
}
