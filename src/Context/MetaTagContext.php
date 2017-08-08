<?php

namespace Comicrelief\Behat\Context;


class MetaTagContext extends RawContext
{
  /**
   * Match attribute content in specified css locator
   *
   * @Then meta tag og :locator content match :expectContent
   *
   * @param string @locator
   *
   * @param string @expectContent
   *
   * @throws \Exception
   */
  public function ogMetaContentMatch($locator, $expectContent)
  {
    $ogNode = $this->getSession()->getPage()->find('css',$locator);
    if (!$ogNode){
      throw new \Exception("Can not find css locator: $locator");
    }

    $ogContent = $ogNode->getAttribute('content');
    if ( trim($ogContent) != trim($expectContent) ){
      throw new \Exception("Can not find og meta content: $expectContent");
    }

  }

  /**
   * @Then check for og meta tags present
   *
   * @throws \Exception
   */
  public function checkForOgMetaTags()
  {

    $curUrl = $this->getSession()->getCurrentUrl();
    $statusCode = $this->getSession()->getStatusCode();
    if ($statusCode !== 200) {
      throw new \Exception("HTTP ERROR $statusCode : $curUrl");
    }

    $ogTitleNode = $this->getSession()->getPage()->find('css',"meta[property='og:title']");
    if (!$ogTitleNode){
      throw new \Exception("Can not find property og:title");
    }

    $ogURLNode = $this->getSession()->getPage()->find('css',"meta[property='og:url']");
    if (!$ogURLNode){
      throw new \Exception("Can not find property og:url");
    }

    $ogDescriptionNode = $this->getSession()
      ->getPage()
      ->find('css', "meta[property='og:description']");
    if (!$ogDescriptionNode) {
      throw new \Exception("Can not find property og:description");
    }

    $ogTypeNode = $this->getSession()->getPage()->find('css',"meta[property='og:type']");
    if (!$ogTypeNode){
      throw new \Exception("Can not find property og:type");
    }

    $ogSiteNameNode = $this->getSession()->getPage()->find('css',"meta[property='og:site_name']");
    if (!$ogTypeNode){
      throw new \Exception("Can not find property og:site_name");
    }

  }

}
