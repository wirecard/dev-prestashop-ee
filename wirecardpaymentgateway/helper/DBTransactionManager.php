<?php

namespace WirecardEE\Prestashop\Helper;

/**
 * Class DBTransactionManager
 * @since 2.4.0
 */
class DBTransactionManager
{
    /**
     * @var string
     * @since 2.4.0
     */
    const TRANSACTION_TABLE = 'wirecard_payment_gateway_tx';

    /**
     * @var \Db
     * @since 2.4.0
     */
    protected $database;

    public function __construct()
    {
        $this->database = \Db::getInstance();
    }

    /**
     * @param string $transaction_id
     * @since 2.4.0
     */
    public function markTransactionClosed($transaction_id)
    {
        $whereClause = sprintf(
            'tx_id = "%s"',
            pSQL($transaction_id)
        );

        $this->database->update(
            self::TRANSACTION_TABLE,
            [ 'transaction_state' => 'closed' ],
            $whereClause
        );
    }


    /**
     * Attempts to get a named lock for a period of $timeout seconds and retry $maxAttempts times during this time,
     * according to a back-off strategy
     * @param $name
     * @param $timeout
     * @param int $maxAttempts
     * @return bool
     * @throws \PrestaShopDatabaseException
     */
    public function acquireLock($name, $timeout, $maxAttempts = 1)
    {
        if(!trim($name)) {
            throw new \RuntimeException("Invalid name for lock: $name");
        }
        $backoffFactors = $this->getReasonableBackoffFactorsForTimeout($timeout);
        $attempts = 0;
        $startTime = microtime(true);
        $sqlTimeout = $timeout;
        if($maxAttempts == 1) {
            $sqlTimeout = $timeout;
        }
        while ($attempts < $maxAttempts) {
            $result = $this->database->query("SELECT GET_LOCK('" . pSQL($name) . "', $sqlTimeout) AS acquired");
            $acquired = (bool)$result->fetch(\PDO::FETCH_COLUMN);
            if ($acquired) {
                return true;
            }
            if (microtime(true) - $startTime > $timeout) {
                break;
            }
            $backoffFactorIndex = $attempts;
            if (!isset($backoffFactors[$backoffFactorIndex])) {
                $backoffFactorIndex = count($backoffFactors) - 1;
            }
            $backoffFactor = $backoffFactors[$backoffFactorIndex];
            usleep($backoffFactor * 1000);

            $attempts++;
        }
        return false;
    }

    public function releaseLock($name)
    {
        $result = $this->database->query("SELECT RELEASE_LOCK('" . pSQL($name) . "') AS released");
        $released = (bool)$result->fetch(\PDO::FETCH_COLUMN);
        return $released;
    }

    /**
     * Splits the timeout duration in reasonable backoff intervals, in milliseconds
     * @param $timeout timeout in seconds
     *
     * The way it works:
     * During the first second, attempt: 10 times every 10 milliseconds, 9 times every 100 milliseconds; total: 1s
     * During the first minute, attempt: 9 times every second. This amounts to 50 seconds left from the first minute,
     * because it includes the previous step of 1 second. The remaining 50 seconds are divided in 10 attempts of 5
     * seconds each.
     * The previous steps have covered the first minute. If more time is needed, proceed following the same pattern as
     * for minutes: 9 times every minute, and the remaining 50 minutes are attempted 10 times every 5 minutes.
     * The remaining time (if longer than 1h) is split in intervals of 10 minutes each.
     *
     * Examples (notation: s - second, m - minute, h - hour, ms - millisecond):
     * 1s => 10 x 10ms + 9 x 100ms
     * 2s => 10 x 10ms + 9 x 100ms + 1s
     * 3s => 10 x 10ms + 9 x 100ms + 1s + 1s
     * 10s => 10 x 10ms + 9 x 100ms + 9 x 1s
     * 11s => 10 x 10ms + 9 x 100ms + 10 x 1s
     * 16s => 10 x 10ms + 9 x 100ms + 9 x 1s + 1 x 5s
     * 59s => 10 x 10ms + 9 x 100ms + 8 x 1s + 10 x 5s
     * 60s => 10 x 10ms + 9 x 100ms + 9 x 1s + 10 x 5s
     *
     * @return array
     */
    private function getReasonableBackoffFactorsForTimeout($timeout)
    {
        $timeoutInMilliseconds = $timeout * 1000;
        $coveredTime = 0;
        $intervals = [];
        $lastIntervalCount = 0;
        while ($coveredTime < $timeoutInMilliseconds) {//at most 58 loops
            if ($coveredTime < 100) {//first 100 ms are divided into 10 x 10ms
                $intervals[] = 10;//10 ms
                $coveredTime += 10;
                continue;
            }
            if ($coveredTime < 1000) {//interval between 100ms and 1s is divided into 9 x 100ms
                $intervals[] = 100;//100 ms
                $coveredTime += 100;
                continue;
            }
            if ($coveredTime < 10 * 1000) {//interval between 1s and 10s
                $intervals[] = 1000;//1s
                $coveredTime += 1000;
                continue;
            }
            if ($coveredTime < 60 * 1000) {//interval between 10s and 60s
                $intervals[] = 5 * 1000;//5s
                $coveredTime += 5 * 1000;
                continue;
            }
            if ($coveredTime < 10 * 60 * 1000) {//interval between 60s and 10m
                $intervals[] = 60 * 1000;//1m
                $coveredTime += 60 * 1000;
                continue;
            }
            if ($coveredTime < 60 * 60 * 1000) {//interval between 10m and 60m
                $intervals[] = 5 * 60 * 1000;//5m
                $coveredTime += 5 * 60 * 1000;
                $lastIntervalCount++;
                if ($lastIntervalCount < 10) {
                    continue;
                }
            }
            if ($coveredTime < $timeoutInMilliseconds) {//add the final (default) interval
                $intervals[] = 10 * 60 * 1000;//10m
                $coveredTime = $timeoutInMilliseconds;//break the loop
            }
        }

        return $intervals;
    }
}
