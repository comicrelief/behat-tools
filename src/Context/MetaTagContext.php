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
        $ogNode = $this->getSession()->getPage()->find('css', $locator);
        if (!$ogNode) {
            throw new \UnexpectedValueException("Can not find css locator: $locator");
        }

        $ogContent = $ogNode->getAttribute('content');
        if (trim($ogContent) != trim($expectContent)) {
            throw new \UnexpectedValueException("Can not find og meta content: $expectContent");
        }
    }

  /**
   * @Then check for og meta tags present
   *
   * @throws \Exception
   */
    public function checkForOgMetaTags()
    {

        $currentPage = $this->getSession()->getCurrentUrl();
        $statusCode = $this->getSession()->getStatusCode();
        if ($statusCode !== 200) {
            throw new \UnexpectedValueException("HTTP ERROR $statusCode : $currentPage");
        }

        $ogTitleNode = $this->getSession()->getPage()->find('css', "meta[property='og:title']");
        if (!$ogTitleNode) {
            throw new \UnexpectedValueException("Can not find property og:title on $currentPage");
        }

        $ogURLNode = $this->getSession()->getPage()->find('css', "meta[property='og:url']");
        if (!$ogURLNode) {
            throw new \UnexpectedValueException("Can not find property og:url on $currentPage");
        }

        $ogDescriptionNode = $this->getSession()
        ->getPage()
        ->find('css', "meta[property='og:description']");
        if (!$ogDescriptionNode) {
            throw new \UnexpectedValueException("Can not find property og:description on $currentPage");
        }

        $ogTypeNode = $this->getSession()->getPage()->find('css', "meta[property='og:type']");
        if (!$ogTypeNode) {
            throw new \UnexpectedValueException("Can not find property og:type on $currentPage");
        }

        $ogSiteNameNode = $this->getSession()->getPage()->find('css', "meta[property='og:site_name']");
        if (!$ogSiteNameNode) {
            throw new \UnexpectedValueException("Can not find property og:site_name on $currentPage");
        }

        $ogImageNode = $this->getSession()->getPage()->find('css', "meta[property='og:image']");
        if (!$ogImageNode) {
            throw new \UnexpectedValueException("Can not find property og:image on $currentPage");
        }
    }
}
