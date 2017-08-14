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
class RawContext extends RawMinkContext {

  /* @var TestDataHandler */
  protected $testDataHandler;

  /**
   * CommonContext constructor.
   */
  public function __construct() {
    $this->testDataHandler = new TestDataHandler();
  }


  /**
   * Find element by css
   *
   * @param string $locator
   *
   * @return \Behat\Mink\Element\NodeElement|mixed|null
   * @throws \Exception
   */
  public function findElementByCss(string $locator) {
    $element = $this->getSession()->getPage()->find('css', $locator);

    if (!$element) {
      throw new \Exception("Can not find element by css : '$locator'");
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
  public function getTextByCss(string $locator) {
    $text = NULL;
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
  public function getCurrentWindowName() {
    $windowName = $this->getSession()->getWindowName();
    return $windowName;
  }

  /**
   * @param Switch to new tab
   */
  public function switchToNewTab($windowNames) {
    $this->getSession()->switchToWindow(end($windowNames));
  }

  /**
   * switch to iframe with css :locator
   *
   * @param String $locator
   *
   * @throws \Exception
   */
  public function iSwitchToIFrameWithCSSLocator($locator) {
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
    }
    else {
      throw new \Exception("iFrame already has a name: " . $iframeName);
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
  public function spin($lambda, $wait = 240) {
    for ($i = 0; $i < $wait; $i++) {
      try {
        if ($lambda($this)) {
          return TRUE;
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
   * Helper function to generalize metatag behat tests
   *
   * @throws \Exception
   */
  public function assertMetaRegionGeneric($type, $metatag, $value, $comparison) {
    $element = $this->getSession()
      ->getPage()
      ->find('xpath', '/head/meta[@' . $type . '="' . $metatag . '"]');
    if ($element) {
      $contentValue = $element->getAttribute('content');
      if ($comparison == 'equals' && $value == $contentValue) {
        $result = $value;
      }
      else {
        if ($comparison == 'contains' && strpos($contentValue, $value) !== FALSE) {
          $result = $value;
        }
      }
    }
    if (empty($result)) {
      throw new \Exception(sprintf('Metatag "%s" expected to be "%s", but found "%s" on the page %s', $metatag, $value, $element->getText(), $this->getSession()
        ->getCurrentUrl()));
    }
  }

  /**
   * Check page all links HTTP response code 200
   *
   * @throws \Exception
   */

  public function checkHttpResponseCode() {
    $curUrl = $this->getSession()->getCurrentUrl();
    $statusCode = $this->getSession()->getStatusCode();
    if ($statusCode !== 200) {
      throw new \Exception("HTTP ERROR $statusCode : $curUrl");
    }
    $elementA = $this->getSession()
      ->getPage()
      ->find('css', 'main')
      ->findAll('css', 'a');

    foreach ($elementA as $a) {
      $this->getSession()->visit($curUrl);
      $linkUrl = trim($a->getAttribute('href'));
      $linkText = $a->getText();
      if (strlen($linkUrl) > 0) {
        if (!strpos($linkUrl, '.mp4') && !strpos($linkUrl, '.pdf')
          && !strpos($linkUrl, '.docx') && !strpos($linkUrl, '.doc')
          && !strpos($linkUrl, '.ppt') && !strpos($linkUrl, '.zip')
          && !strpos($linkUrl, '.png') && !(strpos($linkUrl,
              'mailto:') === 0)
        ) {
          $this->getSession()->visit($linkUrl);
          $statusCode = $this->getSession()->getStatusCode();
          if ($statusCode !== 200) {
            throw new \Exception("HTTP ERROR $statusCode : '$linkText' link in '$curUrl' page");
          }
        }
      }
    }

  }

}
