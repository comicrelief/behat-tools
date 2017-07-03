<?php

namespace Comicrelief\Behat\Context;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

/**
 * Provides feature definitions to test RESTful APIs
 * @package Comicrelief\Behat\Context
 */
class RestContext implements Context
{
    protected $_requestUrl;
    private $_restObjectType;
    private $_restObjectMethod = 'get';
    /* @var \GuzzleHttp\Client */
    private $_client;
    /* @var ResponseInterface */
    private $_response;
    private $responseBody;
    private $_parameters;
    protected $requestPayload;

    public function __construct(string $baseUrl)
    {
        $parameters = ['base_url' => $baseUrl];

        $this->_client = new Client();
        $this->_parameters = $parameters;
    }

    /**
     * @Given /^that I want to make a new "([^"]*)"$/
     * @param string $objectType
     */
    public function thatIWantToMakeANew($objectType): void
    {
        $this->_restObjectType = ucwords(strtolower($objectType));
        $this->_restObjectMethod = 'post';
    }

    /**
     * @When I send get request to :arg1
     * @param string $pageUrl
     * @throws RequestException
     */
    public function iSendGetRequest($pageUrl): void
    {
        $baseUrl = $this->getParameter('base_url');

        $this->_requestUrl = $baseUrl . $pageUrl;

        try {
            $this->_response = $this->_client->get($this->_requestUrl);
        } catch (RequestException $e) {
            $this->_response = $e->getResponse();
        }
    }

    /**
     * @When I send get request to :arg1
     * @param string $pageUrl
     * @throws RequestException
     */
    public function iSendTheRequest($pageUrl): void
    {
        $baseUrl = $this->getParameter('base_url');

        $this->_requestUrl = $baseUrl . $pageUrl;

        try {
            $this->_response = $this->_client->request($this->_restObjectMethod, $this->_requestUrl);
        } catch (RequestException $e) {
            $this->_response = $e->getResponse();
        }
    }

    /**
     * @Then /^the response is JSON$/
     * @throws \RuntimeException
     */
    public function theResponseIsJson(): void
    {
        $this->responseBody = json_decode($this->_response->getBody());

        if (empty($this->responseBody)) {
            throw new \RuntimeException('Response was not JSON');
        }
    }

    /**
     * @Given /^I have the payload:$/
     * @param PyStringNode $requestPayload
     */
    public function iHaveThePayload(PyStringNode $requestPayload): void
    {
        $this->requestPayload = $requestPayload;
    }

    /**
     * @Then /^the http status code should be (\d+)$/
     * @param int $httpStatus
     * @throws \RuntimeException
     */
    public function theRestResponseStatusCodeShouldBe($httpStatus): void
    {
        if ((string)$this->_response->getStatusCode() !== $httpStatus) {
            throw new \RuntimeException('HTTP code does not match ' . $httpStatus .
                ' (actual: ' . $this->_response->getStatusCode() . ')' . $this->_response->getBody());
        }
    }

    /**
     * @Given /^the response has a "([^"]*)" property$/
     * @Given /^the response has an "([^"]*)" property$/
     * @param string $propertyName
     * @throws \RuntimeException
     */
    public function theResponseHasAProperty($propertyName): void
    {
        $data = json_decode($this->_response->getBody(), true);
        $count = count($data);
        $count = random_int(0, $count - 1);
        $data = $data[$count];

        $this->theResponseIsJson();

        if (static::path($data, $propertyName) === null) {
            throw new \RuntimeException("Property '" . $propertyName . "' is not set!\n");
        }
    }

    /**
     * Gets a value from an array using a dot separated path.
     *
     *     // Get the value of $array['foo']['bar']
     *     $value = Arr::path($array, 'foo.bar');
     *
     * @param   array   array to search
     * @param   string  $path, dot separated
     * @param   mixed   $default if the path is not set
     * @return  mixed
     */
    public static function path($array, $path, $default = null)
    {
        // Split the keys by slashes
        $keys = explode('.', $path);
        do {
            $key = array_shift($keys);

            if (isset($array[$key])) {
                if ($keys) {
                    if (is_array($array[$key])) {
                        // Dig down into the next part of the path
                        $array = $array[$key];
                    } else {
                        // Unable to dig deeper
                        break;
                    }
                } else {
                    // Found the path requested
                    return $array[$key];
                }
            } else {
                // Unable to dig deeper
                break;
            }
        } while ($keys);
        // Unable to find the value requested
        return $default;
    }

    /**
     * @Then the response should contain expected :arg1 as :arg2
     * @param $property
     * @param $expectedValue
     */
    public function responseShouldContainExpected($property, $expectedValue): void
    {
        $responseData = $this->_response->getBody();
        $responseData = \GuzzleHttp\json_decode($responseData, true);

        TestCase::assertEquals($expectedValue, $responseData[$property],
            'Failed: The response does not contain expected value ' . $property);
    }

    public function getParameter($name)
    {
        return $this->_parameters[$name] ?? null;
    }
}
