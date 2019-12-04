<?php


namespace WirecardEE\Prestashop\Helper;


trait NumericHelper
{

    private function equals($firstNumber, $secondNumber)
    {
        $precision = (int)_PS_PRICE_COMPUTE_PRECISION_;
        $delta = pow(10, -1 * $precision);
        $difference = abs($firstNumber - $secondNumber);
        error_log("precision: $precision, delta: $delta, diff: $difference, first: $firstNumber, second: $secondNumber");
        return $difference < $delta;
    }
}
