<?php

namespace Comicrelief\Behat\Context;


use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\MinkExtension\Context\RawMinkContext;
use Comicrelief\Behat\Utils\TestDataHandler;
use Exception;
use Faker;
use PHPUnit\Framework\TestCase;
use RuntimeException;


class CommonContext extends RawMinkContext
{
    /* @var TestDataHandler */
    protected $testDataHandler;

    /* @var RawContext */
    protected $rawContext;

    /**
     * CommonContext constructor.
     */
    public function __construct() {
        $this->testDataHandler = new TestDataHandler();
    }

    /** @BeforeScenario */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $environment = $scope->getEnvironment();

        $this->rawContext = $environment->getContext(RawContext::class);
    }

    /**
     * Waits for the given amount of time in milliseconds
     * Example: When I wait for 1000ms
     * Example: And I wait for 2000ms
     *
     * @When /^I wait for ([\d]+)(?:ms)?$/
     * @param int $time
     */
    public function iWaitFor(int $time): void
    {
        $this->getSession()->wait($time);
    }

    /**
     * @Given I maximise the browser window
     * @throws \Behat\Mink\Exception\DriverException
     */
    public function iMaximiseTheBrowserWindow()
    {
        $this->getSession()->getDriver()->maximizeWindow();
        $this->getSession()->wait(5000);
    }

    /**
     * @When /^(?:|I )confirm the popup$/
     */
    public function confirmPopup()
    {
        $this->getSession()->getDriver()->getWebDriverSession()->accept_alert();
    }

    /**
     * @When /^(?:|I )cancel the popup$/
     */
    public function cancelPopup()
    {
        $this->getSession()->getDriver()->getWebDriverSession()->dismiss_alert();
    }

    /**
     * Spin method to loop
     * @param $lambda
     * @param int $wait
     * @return bool
     * @throws Exception
     */
    public function spin ($lambda, $wait = 240)
    {
        for ($i = 0; $i < $wait; $i++)
        {
            try {
                if ($lambda($this)) {
                    return true;
                }
            } catch (Exception $e) {
                // do nothing
            }

            usleep(250000); // 0.25 seconds
        }

        $backtrace = debug_backtrace();

        throw new Exception(
            "Timeout thrown by " . $backtrace[1]['class'] . "::" . $backtrace[1]['function']
        );
    }

    /**
     * @Then I wait for :arg element to appear
     * @param string $locator
     */
    public function iWaitForElementToAppear(string $locator)
    {
        $this->spin(function(CommonContext $context) use ($locator) {
            try {
                $context->assertSession()->elementExists('css', $locator);
                return true;
            }
            catch (RuntimeException $e) {
                throw new \RuntimeException('The element with css ' .$locator .' do not appear on the page ' .$e);
            }
        });
    }

    /**
     * @Then I wait for :arg element to disappear
     * @param string $locator
     */
    public function iWaitForElementToDisappear(string $locator)
    {
        $this->spin(function(CommonContext $context) use ($locator) {
            try {
                $context->assertSession()->elementNotExists('css', $locator);
                return true;
            }
            catch (RuntimeException $e) {
                throw New \RuntimeException('The element with css ' .$locator .' do not disappear from the page ' .$e);
            }
        });
    }

    /**
     * @Then I wait for :arg text to appear
     * @param string $text
     */
    public function iWaitForTextToAppear(string $text)
    {
        $this->spin(function(CommonContext $context) use ($text) {
            try {
                $context->assertSession()->pageTextContains($text);
                return true;
            }
            catch (RuntimeException $e) {
                throw new \RuntimeException('The text "' .$text .'" do not appear on the page ' .$e);
            }
        });
    }

    /**
     * @Then I wait for :arg text to disappear
     * @param string $text
     */
    public function iWaitForTextToDisappear(string $text)
    {
        $this->spin(function(CommonContext $context) use ($text) {
            try {
                $context->assertSession()->pageTextNotContains($text);
                return true;
            }
            catch (RuntimeException $e) {
                throw new \RuntimeException('The text "' .$text .'" do not disappear from the page ' .$e);
            }
        });
    }

    /**
     * Click on the element with given CSS
     * @When I click on :arg element
     * @param string $field
     */
    public function iClickOnElement(string $field): void
    {
        $this->rawContext->findElementByCss($field)->click();
    }

    /**
     * Double click on the element with given CSS
     * @When I double click on :arg element
     * @param string $field
     */
    public function iDoubleClickOnElement(string $field): void
    {
        $this->rawContext->findElementByCss($field)->doubleClick();
    }

    /**
     * Fills in a random word in a field and add it to test data array
     * Example: When I fill test data "John" in "firtname" field
     *
     * @When I fill test data :arg1 in :arg2 field
     * @param string $value
     * @param string $locator
     */
    public function iFillTestDataInField(string $value, string $locator): void
    {
        $faker = Faker\Factory::create('en_GB');

        if (strpos(strtolower($value), 'email') !== false) {
            $word = 'qa-tester_' . rand(1, 1000000) . '@comicrelieftest.com';
        } elseif (strpos(strtolower($value), 'postcode') !== false) {
            $word = $faker->postcode;
        } else {
            $word = $faker->firstName;
        }

        $this->testDataHandler->addTestData($value, $word);
        $this->rawContext->findElementByCss($locator)->setValue($this->testDataHandler->getTestData($value));
    }

    /**
     * @When I fill test data :arg1 in :arg2 confirm field
     * @param string $value
     * @param string $locator
     */
    public function iFillConfirmTestDataInField(string $value, string $locator): void
    {
        $this->rawContext->findElementByCss($locator)->setValue($this->testDataHandler->getTestData($value));
        $this->testDataHandler->addTestData('confirm' .$value, $this->testDataHandler->getTestData($value));
    }

    /**
     * Reads the value of a field and adds to test data array
     *
     * @When I read :arg1 from :arg2 field
     * @param string $value
     * @param string $locator
     */
    public function iReadValueFromField(string $value, string $locator): void
    {
        $fieldValue = $this->rawContext->findElementByCss($locator)->getValue();
        $this->testDataHandler->addTestData($value, $fieldValue);
    }

    /**
     * Checks, that page contains elements with specific css selectors
     * Example: Then I should see below elements:
     * Example: And I should see below elements:
     *
     * @Then I should see below elements:
     * @param TableNode $locators
     */
    public function assertPageContainsElements(TableNode $locators): void
    {
        $elementPresent = null;

        foreach ($locators as $locator){
            try{
                $this->assertSession()->elementExists('css', $locator['locator']);
                $elementPresent = true;
            }catch (\Behat\Mink\Exception\Exception $e){
                $elementPresent = false;
            }
            TestCase::assertTrue($elementPresent, 'The element with css ' .$locator['locator'] .'is not visible in the page');
        }
    }

    /**
     * Checks, that page do not contain elements with specific css selectors
     * Example: Then I should not see below elements:
     * Example: And I should not see below elements:
     *
     * @Then I should not see below elements:
     * @param TableNode $locators
     */
    public function assertPageNotContainsElements(TableNode $locators): void
    {
        $elementPresent = null;

        foreach ($locators as $locator){
            try{
                $this->assertSession()->elementNotExists('css', $locator['locator']);
                $elementPresent = true;
            }catch (\Behat\Mink\Exception\Exception $e){
                $elementPresent = false;
            }
            TestCase::assertTrue($elementPresent, 'The element with css ' .$locator['locator'] .'is visible in the page');
        }
    }

    /**
     * Checks, that page contains specified text
     * Example: Then I should see below text:
     * Example: And I should see below text:
     *
     * @Then I should see below text:
     * @param TableNode $texts
     */
    public function assertPageContainsTexts(TableNode $texts)
    {
        foreach ($texts as $text){
            $actual = $this->getSession()->getPage()->getText();
            TestCase::assertContains($text['text'], $actual);
        }
    }

    /**
     * Checks, that page do not contain specified text
     * Example: Then I should not see below text:
     * Example: And I should not see below text:
     *
     * @Then I should not see below text:
     * @param TableNode $texts
     */
    public function assertPageDonotContainTexts(TableNode $texts)
    {
        foreach ($texts as $text){
            $actual = $this->getSession()->getPage()->getText();
            TestCase::assertNotContains($text['text'], $actual);
        }
    }

    /**
     * Checks, that an element with given css contains specific text
     *
     * @Then I should see :arg1 test data in :arg2 element
     * @param string $value
     * @param string $selector
     */
    public function iShouldSeeTestDataInElement(string $value, string $selector): void
    {
        $elementHtml = $this->rawContext->findElementByCss($selector)->getHtml();
        $text = $this->testDataHandler->getTestData($value);
        if ($text !== ''){
            TestCase::assertContains($text, $elementHtml,
                'The text ' .$text .' was not found in the html of the element matching css ' .$selector);
        } else {
            echo 'The field ' .$value .'is empty';
        }
    }

    /**
     * Switches to a given iframe
     *
     * @Given /^I switch to the iframe "([^"]*)"$/
     * @param string $arg1
     */
    public function iSwitchToIframe(string $arg1 = null): void
    {
        $this->getSession()->switchToIFrame($arg1);
    }

    /**
     * Switches to the main window from an iframe
     *
     * @Given /^I switch to main window from iframe$/
     */
    public function iSwitchToMainWindowFromIframe(): void
    {
        $this->getSession()->switchToIFrame();
    }

    /**
     * Restart the current browser window
     *
     * @Then /^I restart the browser$/
     */
    public function iRestartTheBrowser(): void
    {
        $this->getSession()->restart();
    }

    /**
     * @Then /^I refresh the browser$/
     */
    public function iRefreshTheBrowser(): void
    {
        $this->getSession()->reload();
    }

    /**
     * Check page contains element specified by CSS selector
     * Example: Then I should see element "username"
     * Example: And should see element "password"
     *
     * @Then /^(?:|I )should see element "(?P<locator>(?:[^"]|\\")*)"$/
     * @param string $selector
     */
    public function iShouldSeeElementByCss($selector): void
    {
        $this->assertSession()->elementExists('css', $selector);
    }

    /**
     * Check page dooesn't contain element specified by CSS selector
     * Example: Then I should not see element "username"
     * Example: And should not see "password" element
     *
     * @Then /^(?:|I )should not see element "(?P<locator>(?:[^"]|\\")*)"$/
     * @param string $selector
     */
    public function iShouldNotSeeElementByCss($selector): void
    {
        $this->assertSession()->elementNotExists('css', $selector);
    }

    /**
     * @Then I should see :url page url
     * @param string $url
     * @throws \Exception
     */
    public function thenIShouldSeePage(string $url): void
    {
        $windowNames = $this->getSession()->getWindowNames();
        if (count($windowNames) > 1) {
            $this->rawContext->switchToNewTab($windowNames);
        }
        $current_url = $this->getSession()->getCurrentUrl();
        if (!strpos($current_url, $url)) {
            throw new \Exception("Can not find url $url");
        }
    }

}
