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
            throw new \Exception("Can not find css locator: $locator");
        }

        $ogContent = $ogNode->getAttribute('content');
        if (trim($ogContent) != trim($expectContent)) {
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

        $ogTitleNode = $this->getSession()->getPage()->find('css', "meta[property='og:title']");
        if (!$ogTitleNode) {
            throw new \Exception("Can not find property og:title on $curUrl");
        }

        $ogURLNode = $this->getSession()->getPage()->find('css', "meta[property='og:url']");
        if (!$ogURLNode) {
            throw new \Exception("Can not find property og:url on $curUrl");
        }

        $ogDescriptionNode = $this->getSession()
        ->getPage()
        ->find('css', "meta[property='og:description']");
        if (!$ogDescriptionNode) {
            throw new \Exception("Can not find property og:description on $curUrl");
        }

        $ogTypeNode = $this->getSession()->getPage()->find('css', "meta[property='og:type']");
        if (!$ogTypeNode) {
            throw new \Exception("Can not find property og:type on $curUrl");
        }

        $ogSiteNameNode = $this->getSession()->getPage()->find('css', "meta[property='og:site_name']");
        if (!$ogSiteNameNode) {
            throw new \Exception("Can not find property og:site_name on $curUrl");
        }

        $ogImageNode = $this->getSession()->getPage()->find('css', "meta[property='og:image']");
        if (!$ogImageNode) {
          throw new \Exception("Can not find property og:image on $curUrl");
        }
    }
}
