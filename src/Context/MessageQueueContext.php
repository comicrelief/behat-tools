<?php

namespace Comicrelief\Behat\Context;

use Comicrelief\Behat\Utils\TestDataHandler;
use PHPUnit\Framework\TestCase;

class MessageQueueContext extends RawContext
{

    private $messageQueueUrl;

    private $jsonMQString;

    private $messageQueuePresent;

  /**
   * Webconnector constructor.
   * Create instance for TestDataHandler class
   *
   * @param string $parameters
   */
    public function __construct($parameters)
    {
        parent::__construct();
        $this->messageQueueUrl = $parameters;
    }

  /**
   * @When I navigate to :arg message queue interface
   *
   * @param string $queueType
   */
    public function iNavigateToMQInterface($queueType)
    {
        $queue = null;
        $id = null;
        $base_url = $this->getMinkParameter('base_url');
        if (strpos($base_url, 'staging') !== false) {
            $id = 7;
        } else {
            $id = 11;
        }

        if ($queueType === 'esu') {
            $queue = 'queueName=esu&id=' . $id;
        }
        $this->visitPath($this->messageQueueUrl . $queue);
    }

  /**
   * @Then I read the :arg1 message queue data from :arg2
   *
   * @param string $queueName
   * @param string $field
   */
    public function iReadMQDataFrom($queueName, $field)
    {
        $messagePresent = null;

        try {
            $this->assertSession()->elementExists('css', $field);
            $messagePresent = true;
        } catch (\Behat\Mink\Exception\Exception $e) {
            $messagePresent = false;
        }

        if ($messagePresent) {
            $json = $this->getSession()->getPage()->find('css', $field)->getText();
            $this->jsonMQString = json_decode($json, true);
            $this->messageQueuePresent = true;
        } else {
            echo 'The ' . $queueName . 'message queue has been consumed';
            $this->messageQueuePresent = false;
        }
    }

  /**
   * @Then the :arg1 in message queue json should contain :arg2
   *
   * @param string $key
   * @param string $value
   */
    public function theMQJsonShouldContain($key, $value)
    {
        if ($this->messageQueuePresent) {
            TestCase::assertEquals(
                $value,
                $this->jsonMQString[$key],
                'The ' . $key . ' in message queue json do not contain ' . $value
            );
        } else {
            echo 'The message queue has been consumed';
        }
    }

  /**
   * @Then the :arg1 in message queue json should contain :arg2 test data
   *
   * @param string $mqJsonKey
   * @param string $testDataKey
   */
    public function theMessageQueueJsonShouldContainTestData($mqJsonKey, $testDataKey)
    {
        if ($this->messageQueuePresent) {
            $value = $this->testDataHandler->getTestData($testDataKey);
            if (ctype_upper($this->jsonMQString[$mqJsonKey])) { // returns true if is fully uppercase
                $value = strtoupper($value);
            }
            TestCase::assertEquals(
                $value,
                $this->jsonMQString[$mqJsonKey],
                'The ' . $mqJsonKey . ' in message queue json do not contain expected ' . $testDataKey
            );
        } else {
            echo 'The message queue has been consumed';
        }
    }

  /**
   * @Then I should see the expected transSourceURL in message queue json
   */
    public function theTransSourceURLInMQJsonShouldContain()
    {
        $base_url = $this->getMinkParameter('base_url');
        if ($this->messageQueuePresent) {
            TestCase::assertEquals(
                $base_url,
                $this->jsonMQString['transSourceURL'],
                'The transSourceURL in message queue json do not contain ' . $base_url
            );
        } else {
            echo 'The message queue has been consumed';
        }
    }
}
