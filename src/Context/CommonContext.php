<?php

namespace Comicrelief\Behat\Context;

use Behat\Gherkin\Node\TableNode;
use Faker;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class CommonContext extends RawContext
{

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
     * @Then I wait for :arg element to appear
     * @param string $locator
     * @throws \Exception
     */
    public function iWaitForElementToAppear(string $locator)
    {
        $this->spin(function (CommonContext $context) use ($locator) {
            try {
                $context->assertSession()->elementExists('css', $locator);
                return true;
            } catch (RuntimeException $e) {
                throw new \RuntimeException('The element with css ' . $locator . ' do not appear on the page ' . $e);
            }
        });
    }

    /**
     * @Then I wait for :arg element to disappear
     * @param string $locator
     * @throws \Exception
     */
    public function iWaitForElementToDisappear(string $locator)
    {
        $this->spin(function (CommonContext $context) use ($locator) {
            try {
                $context->assertSession()->elementNotExists('css', $locator);
                return true;
            } catch (RuntimeException $e) {
                throw new \RuntimeException('The element with css ' . $locator . ' do not disappear from the page ' . $e);
            }
        });
    }

    /**
     * @Then I wait for :arg text to appear
     * @param string $text
     * @throws \Exception
     */
    public function iWaitForTextToAppear(string $text)
    {
        $this->spin(function (CommonContext $context) use ($text) {
            try {
                $context->assertSession()->pageTextContains($text);
                return true;
            } catch (RuntimeException $e) {
                throw new \RuntimeException('The text "' . $text . '" do not appear on the page ' . $e);
            }
        });
    }

    /**
     * @Then I wait for :arg text to disappear
     * @param string $text
     * @throws \Exception
     */
    public function iWaitForTextToDisappear(string $text)
    {
        $this->spin(function (CommonContext $context) use ($text) {
            try {
                $context->assertSession()->pageTextNotContains($text);
                return true;
            } catch (RuntimeException $e) {
                throw new \RuntimeException('The text "' . $text . '" do not disappear from the page ' . $e);
            }
        });
    }

    /**
     * Click on the element with given CSS
     * @When I click on :arg element
     * @param string $field
     * @throws \Exception
     */
    public function iClickOnElement(string $field): void
    {
        $this->findElementByCss($field)->click();
    }

    /**
     * Double click on the element with given CSS
     * @When I double click on :arg element
     * @param string $field
     * @throws \Exception
     */
    public function iDoubleClickOnElement(string $field): void
    {
        $this->findElementByCss($field)->doubleClick();
    }

    /**
     * Fills in a random word in a field and add it to test data array
     * Example: When I fill test data "John" in "firstname" field
     *
     * @When I fill test data :arg1 in :arg2 field
     * @param string $value
     * @param string $locator
     */
    public function iFillTestDataInField(string $value, string $locator): void
    {
        $faker = Faker\Factory::create('en_GB');
        $word = null;

        if (stripos($value, 'email') !== false) {
            $word = 'qa-tester_' . random_int(1, 1000000) . '@comicrelieftest.com';
        } elseif (stripos($value, 'postcode') !== false) {
            $word = $faker->postcode;
        } elseif (stripos($value, 'text') !== false) {
            $word = $faker->text;
        } elseif (stripos($value, 'url') !== false) {
            $word = $faker->url;
        } elseif (stripos($value, 'id') !== false) {
            $word = $faker->uuid;
        } elseif (stripos($value, 'phone') !== false) {
            $word = $faker->phoneNumber;
        } else {
            $word = $faker->bothify('?????####');
        }

        $this->testDataHandler->addTestData($value, $word);
        $this->findElementByCss($locator)
            ->setValue($this->testDataHandler->getTestData($value));
    }

    /**
     * @When I fill test data :value in :locator field with a fake :fakerFormat
     *
     * @param string $value         Test data identifier
     * @param string $fakerFormat
     * @param string $locator
     */
    public function iFillGivenFormatTestDataInField(string $value, string $fakerFormat, string $locator): void
    {
        $faker = Faker\Factory::create('en_GB');
        $word = $faker->$fakerFormat;
        $this->testDataHandler->addTestData($value, $word);
        $this->findElementByCss($locator)
            ->setValue($this->testDataHandler->getTestData($value));
    }

    /**
     * @When I fill test data :arg1 in :arg2 confirm field
     * @param string $value
     * @param string $locator
     */
    public function iFillConfirmTestDataInField(string $value, string $locator): void
    {
        $this->findElementByCss($locator)->setValue($this->testDataHandler->getTestData($value));
        $this->testDataHandler->addTestData('confirm' . $value, $this->testDataHandler->getTestData($value));
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
        $fieldValue = $this->findElementByCss($locator)->getValue();
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

        foreach ($locators as $locator) {
            try {
                $this->assertSession()->elementExists('css', $locator['locator']);
                $elementPresent = true;
            } catch (\Behat\Mink\Exception\Exception $e) {
                $elementPresent = false;
            }
            TestCase::assertTrue($elementPresent, 'The element with css ' . $locator['locator'] . 'is not visible in the page');
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

        foreach ($locators as $locator) {
            try {
                $this->assertSession()->elementNotExists('css', $locator['locator']);
                $elementPresent = true;
            } catch (\Behat\Mink\Exception\Exception $e) {
                $elementPresent = false;
            }
            TestCase::assertTrue($elementPresent, 'The element with css ' . $locator['locator'] . 'is visible in the page');
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
        foreach ($texts as $text) {
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
        foreach ($texts as $text) {
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

        $elementHtml = $this->findElementByCss($selector)->getHtml();
        $text = $this->testDataHandler->getTestData($value);
        if ($text !== '') {
            TestCase::assertContains(
                $text,
                $elementHtml,
                'The text ' . $text . ' was not found in the html of the element matching css ' . $selector
            );
        } else {
            echo 'The field ' . $value . 'is empty';
        }
    }

    /**
     * Switches to a given iframe with name
     *
     * @Given /^I switch to the iframe "([^"]*)"$/
     * @param string $arg1
     */
    public function iSwitchToIframe(string $arg1 = null): void
    {
        $this->getSession()->switchToIFrame($arg1);
    }

    /**
     * Switch to a given iFrame with css locator
     * @Then switch to iframe with css :locator
     */
    public function switchToIframeCSS($locator)
    {
        $this->iSwitchToIFrameWithCSSLocator($locator);
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
     * @deprecated
     *
     * @Then I should see :url page url
     * @param string $url
     * @throws \Exception
     */
    public function thenIShouldSeePage(string $url): void
    {
        $current_url = $this->getSession()->getCurrentUrl();
        $windowNames = $this->getSession()->getWindowNames();
        if (count($windowNames) > 1) {
            $this->switchToNewTab($windowNames);
            $current_url = $this->getSession()->getCurrentUrl();
            $this->getSession()->executeScript('window.close()');
            //switch back to main window
            $this->getSession()->switchToWindow();
        }

        if (!strpos($current_url, $url)) {
            throw new \Exception("Can not find url $url");
        }
    }

    /**
     * Mouse hover with specified CSS locator
     * @When /^I hover over the element "([^"]*)"$/
     * @param string $locator
     * @throws \Exception
     */
    public function iHoverOverTheElement($locator)
    {

        $session = $this->getSession(); // get the mink session
        $element = $session->getPage()->find('css', $locator); // runs the actual query and returns the element

        if (null === $element) {
            throw new \InvalidArgumentException(sprintf('Could not evaluate CSS selector: "%s"', $locator));
        }
        $element->mouseOver();
    }

    /**
     * @When /^(?:|I )should see "([^"]*)" in alert/
     * @param string $message The message.
     *
     * @throws \Exception
     */
    public function assertAlertMessage($message)
    {
        $alertText = $this->getSession()
            ->getDriver()
            ->getWebDriverSession()
            ->getAlert_text();
        if ($alertText !== $message) {
            throw new \RuntimeException("Modal dialog present: $alertText, when expected was $message");
        }
    }

    /**
     * Verify page has following links
     *
     * @Then I should see below links:
     *
     * @param TableNode $links
     *
     * @throws \Exception
     */
    public function assertPageContainsLinks(TableNode $links)
    {
        $actualLinks = $this->getSession()
            ->getPage()
            ->find('css', 'main')
            ->findAll('css', 'a');
        foreach ($links as $link) {
            $flag = false;
            foreach ($actualLinks as $actualLink) {
                $actualLinkText = $actualLink->getText();
                if (trim($actualLinkText) == $link['links']) {
                    $flag = true;
                }
            }
            if (!$flag) {
                throw new \RuntimeException('"' . $link['links'] . '" link can not be found.' . "\n\n");
            }
        }
    }

    /**
     * Verify page has given link
     *
     * @Then I should see the link :link
     *
     * @param string $link
     *
     * @throws \Exception
     */
    public function assertLinkVisible($link)
    {
        $element = $this->getSession()->getPage();
        $result = $element->findLink($link);
        if ($result && !$result->isVisible()) {
            throw new \RuntimeException(sprintf("No link to '%s' on the page %s", $link, $this->getSession()
                ->getCurrentUrl()));
        }
        if (empty($result)) {
            throw new \RuntimeException(sprintf("No link to '%s' on the page %s", $link, $this->getSession()
                ->getCurrentUrl()));
        }
    }

    /**
     * Fills in form field with specified css locator
     *
     * @When I fill in css :locator with :value
     */
    public function fillField($locator, $value)
    {
        $this->getSession()->getPage()->find('css', $locator)->setValue($value);
    }

    /**
     * @Given I switch to new window
     */
    public function iSwitchToNewWindow()
    {
        $windowNames = $this->getSession()->getWindowNames();
        if (count($windowNames) > 1) {
            $this->getSession()->switchToWindow($windowNames[1]);
        }
    }

    /**
     * @Given I switch to parent window
     */
    public function iSwitchToParentWindow()
    {
        $windowNames = $this->getSession()->getWindowNames();
        if (count($windowNames) > 1) {
            $this->getSession()->switchToWindow($windowNames[0]);
        }
    }

    /**
     * @Given I close the child window
     */
    public function iCloseTheChildWindow()
    {
        $windowNames = $this->getSession()->getWindowNames();
        if (count($windowNames) > 1) {
            $this->getSession()->executeScript('window.close()');
            //switch back to main window
            $this->getSession()->switchToWindow();
        }
    }

    /**
     * @Then :haystack contains my :needle
     *
     * @param string $haystack
     * @param string $needle
     */
    public function assertTestDataContainsOtherTestData($haystack, $needle)
    {
        TestCase::assertContains(
            strtolower($this->testDataHandler->getTestData($needle)),
            strtolower($this->testDataHandler->getTestData($haystack))
        );
    }

    /**
     * @Then :haystack does not contain my :needle
     *
     * @param string $haystack
     * @param string $needle
     */
    public function assertTestDataDoesNotContainOtherTestData($haystack, $needle)
    {
        TestCase::assertNotContains(
            strtolower($this->testDataHandler->getTestData($needle)),
            strtolower($this->testDataHandler->getTestData($haystack))
        );
    }
}
