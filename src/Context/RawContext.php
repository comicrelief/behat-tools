<?php

namespace Comicrelief\Behat\Context;

use Behat\MinkExtension\Context\RawMinkContext;

/**
 * Class RawContext
 *
 * @package Comicrelief\Behat\Context
 */
class RawContext extends RawMinkContext
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

  /**
   * switch to iframe with css :locator
   * @param String $locator
   * @throws \Exception
   */
  public function iSwitchToIFrameWithCSSLocator($locator)
  {
    $iframe = $this->getSession()->getPage()->find("css", $locator);
    $iframeName = $iframe->getAttribute("name");
    if ($iframeName == "") {
      $javascript = "(function(){
            var iframes = document.getElementsByTagName('iframe');
            for (var i = 0; i < iframes.length; i++) {
                iframes[i].name = 'iframe_number_' + (i + 1) ;
            }
            })()";
      $this->getSession()->executeScript($javascript);
      $iframe = $this->getSession()->getPage()->find("css", $locator);
      $iframeName = $iframe->getAttribute("name");
    } else {
      throw new \Exception("iFrame already has a name: " . $iframeName);
    }
    $this->getSession()->getDriver()->switchToIFrame($iframeName);
  }

}
