<?php

namespace context;


use utils\Webconnector;

class CommonContext extends Webconnector
{

    /**
     * Waits for the given amount of time in milliseconds
     * Example: When I wait for "1000"
     * Example: And I wait for "2000"
     *
     * @When I wait for :arg1
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







}
