<?php

namespace Comicrelief\Behat\Context;

use Behat\MinkExtension\Context\RawMinkContext;
use Comicrelief\Behat\Utils\TestDataHandler;
use Exception;

/**
 * Class RawContext
 *
 * @package Comicrelief\Behat\Context
 */
class RawContext extends RawMinkContext
{

  /* @var TestDataHandler */
    protected $testDataHandler;

  /**
   * CommonContext constructor.
   */
    public function __construct()
    {
        $this->testDataHandler = new TestDataHandler();
    }

  /**
   * Find element by css
   *
   * @param string $locator
   *
   * @return \Behat\Mink\Element\NodeElement|mixed
   * @throws \Exception
   */
    public function findElementByCss(string $locator)
    {
        $element = $this->getSession()->getPage()->find('css', $locator);

        if (!$element) {
            throw new \RuntimeException("Can not find element by css : '$locator'");
        }

        return $element;
    }

  /**
   * Get text of an element by css
   *
   * @param string $locator
   *
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
        return $this->getSession()->getWindowName();
    }

  /**
   * @param Switch to new tab
   */
    public function switchToNewTab($windowNames)
    {
        $this->getSession()->switchToWindow(end($windowNames));
    }

  /**
   * switch to iframe with css :locator
   *
   * @param String $locator
   *
   * @throws \Exception
   */
    public function iSwitchToIFrameWithCSSLocator($locator)
    {
        $iframe = $this->getSession()->getPage()->find('css', $locator);
        $iframeName = $iframe->getAttribute('name');
        if ($iframeName == '') {
            $javascript = "(function(){
            var iframes = document.getElementsByTagName('iframe');
            for (var i = 0; i < iframes.length; i++) {
                iframes[i].name = 'iframe_number_' + (i + 1) ;
            }
            })()";
            $this->getSession()->executeScript($javascript);
            $iframe = $this->getSession()->getPage()->find('css', $locator);
            $iframeName = $iframe->getAttribute('name');
        } else {
            throw new \RuntimeException('iFrame already has a name: ' . $iframeName);
        }
        $this->getSession()->getDriver()->switchToIFrame($iframeName);
    }

  /**
   * Spin method to loop
   *
   * @param $lambda
   * @param int $wait
   *
   * @return bool
   * @throws Exception
   */
    public function spin($lambda, $wait = 500)
    {
        for ($i = 0; $i < $wait; $i++) {
            try {
                if ($lambda($this)) {
                    return true;
                }
            } catch (Exception $e) {
              // do nothing
            }

            usleep(25000); // 0.25 seconds
        }

        $backtrace = debug_backtrace();

        throw new \RuntimeException(
            'Timeout thrown by ' . $backtrace[1]['class'] . '::' . $backtrace[1]['function']
        );
    }
}
