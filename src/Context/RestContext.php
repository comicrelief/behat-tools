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
    protected $requestUrl;
    private $requestMethod = 'get';
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
     * @Given /^that I want to make a new "([^"]*)" request$/
     * @param string $requestMethod
     */
    public function thatIWantToMakeANew(string $requestMethod): void
    {
        $this->requestMethod = $requestMethod;
    }

    /**
     * @When I send :arg1 request to :arg2
     * @param string $requestMethod
     * @param string $pageUrl
     */
    public function iSendRequestTo(string $requestMethod, string $pageUrl): void
    {
        $baseUrl = $this->getParameter('base_url');

        $this->requestUrl = $baseUrl . $pageUrl;

        try {
            $this->_response = $this->_client->request($requestMethod, $this->requestUrl);
        } catch (RequestException $e) {
            $this->_response = $e->getResponse();
        }
    }

    /**
     * @Then /^the response should be in JSON format$/
     * @throws \RuntimeException
     */
    public function theResponseIsJson(): void
    {
        $this->responseBody = \GuzzleHttp\json_decode($this->_response->getBody());

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
     * @Given /^the response has a "([^"]*)" property$/
     * @Given /^the response has an "([^"]*)" property$/
     * @param string $propertyName
     * @throws \RuntimeException
     */
    public function theResponseHasAProperty(string $propertyName): void
    {
        $this->_response->getBody()->rewind();
        $data = json_decode($this->_response->getBody()->getContents(), true);

        if (static::path($data, $propertyName) === null) {
            throw new \RuntimeException("Property '" . $propertyName . "' is not set!\n");
        }
    }

    /**
     * @Given /^the response item has a "([^"]*)" property$/
     * @Given /^the response item has an "([^"]*)" property$/
     * @param string $propertyName
     * @throws \RuntimeException
     */
    public function theResponseItemHasAProperty(string $propertyName): void
    {
        $this->_response->getBody()->rewind();
        $data = json_decode($this->_response->getBody()->getContents(), true);

        $randomIndex = random_int(0, count($data) - 1);
        $item = $data[$randomIndex];

        if (static::path($item, $propertyName) === null) {
            throw new \RuntimeException("Property '" . $propertyName . "' is not set!\n");
        }
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

    /**
     * @Then /^the response should have "([^"]*)" header$/
     * @param string $header
     * @throws \UnexpectedValueException
     */
    public function theRestHeaderShouldExist($header)
    {
        if (!$this->_response->hasHeader($header)) {
            throw new \UnexpectedValueException('HTTP header does not exist '.$header );
        }
    }

    /**
     * @Then /^"([^"]*)" header should be "([^"]*)"$/
     * @param string $header
     * @param string $contents
     * @throws \UnexpectedValueException
     */
    public function theRestHeaderShouldExistBe($header, $contents)
    {
        $header1 = $this->_response->getHeader($header);
        if ($header1[0] !== $contents) {
            throw new \UnexpectedValueException('HTTP header ' . $header . ' does not match '.$contents.
                ' (actual: '.$header1[0].')');
        }
    }
}
