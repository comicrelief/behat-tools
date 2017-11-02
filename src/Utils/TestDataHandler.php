<?php

namespace Comicrelief\Behat\Utils;

/**
 * Class Webconnector
 * @package utils
 */
class TestDataHandler
{
    /* @var array */
    protected static $testData = [];

    /**
     * Add test data into testData array
     * @param string $key
     * @param mixed $value
     */
    public function addTestData(string $key, $value): void
    {
        self::$testData[$key] = $value;
    }

    /**
     * Get test data from testData array
     * @param mixed $key
     * @return mixed
     */
    public function getTestData(string $key): string
    {
        return self::$testData[$key];
    }

    /**
     * Has testData array key
     * @param mixed $key
     * @return bool
     */
    public function hasTestData(string $key): bool
    {
        return array_key_exists($key, self::$testData);
    }

    /**
     * Set test data from testData array
     * Deletes existing test data and replaces with given array
     * @param array $value
     */
    public static function setTestData(array $value): void
    {
        self::$testData = $value;
    }
}
