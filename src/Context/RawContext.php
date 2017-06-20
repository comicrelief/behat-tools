<?php

namespace Comicrelief\Behat\Context;

use Comicrelief\Behat\Utils\Webconnector;

class RawContext extends Webconnector
{

    /**
     * Find element by css
     * @param string $locator
     * @return \Behat\Mink\Element\NodeElement|mixed|null
     * @throws \Exception
     */
    public function findElementByCss(string $locator)
    {
        $element = $this->getSession()->getPage()->find('css', $locator);

        if (!$element) {
            throw new \Exception("Can not find element by css : '$locator'");
        }

        return $element;
    }

    /**
     * Get text of an element by css
     * @param string $locator
     * @return null|string
     */
    public function getTextByCss(string $locator)
    {
        $text = null;
        try {
            $text = $this->getSession()->getPage()->find('css', $locator)->getText();
        } catch (\Exception $e) {
            $e->getTrace();
        }
        return $text;
    }

    /**
     *Get the current window name
     */
    public function getCurrentWindowName()
    {
        $windowName = $this->getSession()->getWindowName();
        return $windowName;
    }

    /**
     * @param Switch to new tab
     */
    public function switchToNewTab($windowNames)
    {
        $this->getSession()->switchToWindow(end($windowNames));
    }






}
