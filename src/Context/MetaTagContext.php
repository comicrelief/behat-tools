<?php

namespace Comicrelief\Behat\Context;


class MetaTagContext extends RawContext
{

  /**
   * @Then og title meta content match :expectContent
   *
   * @param string @expectContent
   *
   * @throws \Exception
   */
  public function ogTitleMetaContentMatch($expectContent)
  {
    $ogTitleNode = $this->getSession()->getPage()->find('css',"meta[property='og:title']");
    if (!$ogTitleNode){
      throw new \Exception("Can not find property og:title");
    }

    $ogTitleContent = $ogTitleNode->getAttribute('content');
    if ( $ogTitleContent != $expectContent ){
      throw new \Exception("Can not find og:title meta content: $expectContent");
    }

  }

  /**
   * @Then og url meta content match :expectContent
   *
   * @param string @expectContent
   *
   * @throws \Exception
   */
  public function ogUrlMetaContentMatch($expectContent)
  {
    $ogURLNode = $this->getSession()->getPage()->find('css',"meta[property='og:url']");
    if (!$ogURLNode){
      throw new \Exception("Can not find property og:url");
    }

    $ogUrlContent = $ogURLNode->getAttribute('content');
    if ( !strpos($ogUrlContent, $expectContent) ){
      throw new \Exception("Can not find og:url meta content: $expectContent");
    }
  }

  /**
   * @Then og description meta content match :expectContent
   *
   * @param string @expectContent
   *
   * @throws \Exception
   */
  public function ogDescriptionMetaContentMatch($expectContent) {
    $ogDescriptionNode = $this->getSession()
      ->getPage()
      ->find('css', "meta[property='og:description']");
    if (!$ogDescriptionNode) {
      throw new \Exception("Can not find property og:description");
    }

    $ogDescriptionContent = $ogDescriptionNode->getAttribute('content');
    if ($ogDescriptionContent != $expectContent) {
      throw new \Exception("Can not find og:description meta content: $expectContent");
    }
  }

  /**
   * @Then I check for og meta tags
   *
   * @throws \Exception
   */
  public function iCheckForOgMetaTags()
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

  }

}