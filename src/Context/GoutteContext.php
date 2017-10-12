<?php

namespace Comicrelief\Behat\Context;

class GoutteContext extends RawContext{

  /**
   * @Then I visit each link to check HTTP response code 200
   **/
  public function iVisitEachLinkToCheckHTTPResponseCode200() {

    $curUrl = $this->getSession()->getCurrentUrl();
    $statusCode = $this->getSession()->getStatusCode();
    $urls[] = NULL;

    if ($statusCode !== 200) {
      throw new \Exception("HTTP ERROR $statusCode : $curUrl");
    }

    $elementA = $this->getSession()
      ->getPage()
      ->find('css', 'main')
      ->findAll('css', 'a');

    foreach ($elementA as $a) {
      if ($a) {
        $urls[] = trim($a->getAttribute('href'));
      }
    }

    if($urls) {
      foreach ($urls as $url) {
        if ($url && !(strpos($url, 'mailto:') === 0)) {
          var_dump($url);
          $this->visitPath($url);
          $statusCode = $this->getSession()->getStatusCode();
          if ($statusCode !== 200) {
            throw new \Exception("'$url' link not found ( HTTP ERROR : $statusCode ) in Page '$curUrl' ");
          }
        }
      }
    }

  }
}
