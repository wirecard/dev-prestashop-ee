<?php


namespace WirecardEE\Prestashop\Helper;

trait NumericHelper
{
    /**
     * Returns Prestashop precision defined in settings
     * @return int
     */
    public function getPrecision()
    {
        $psPrecision = (int)\Configuration::get('PS_PRICE_DISPLAY_PRECISION');
        $precision = 2;
        if ($psPrecision < 2) {
            $precision = $psPrecision;
        }

        return $precision;
    }

    /**
     * Return step for postprocessing amount input field
     * @return string
     */
    public function calculateNumericInputStep()
    {
        $precision = $this->getPrecision();
        $step = '1';
        if ($precision > 0) {
            $step = '';
            for ($i = 1; $i < $precision; $i++) {
                $step .= '0';
            }
            $step = '0.' . $step . '1';
        }

        return $step;
    }

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
            $precision = $this->getPrecision();
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
            $precision = $this->getPrecision();
        }
        $integerCoefficient = pow(10, $precision);
        $firstNumber *= $integerCoefficient;
        $secondNumber *= $integerCoefficient;
        $diff = round($firstNumber - $secondNumber);
        return $diff / $integerCoefficient;
    }
}
