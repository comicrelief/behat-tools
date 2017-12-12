Behat Tools
===========

This repository is a shared tools and context definitions to help automating Behat scenarios 
in separate repositories and projects of Comic Relief.

This library contains several `*Context.php` files which can be included into `behat.yml` 
and use directly in `*.feature` files.

* `Comicrelief\Behat\Context\CommonContext`: Contains several context definitions to be used in web site
browser behaviour testing.Filling fields, asserting elements exists and waiting for elements to appear, etc...
Requires browser automation using `Selenium/Browserstack` 
* `Comicrelief\Behat\Context\GoutteContext`: Contains context definition to check broken links.
* `Comicrelief\Behat\Context\MessageQueueContext`: Contains context definitions to automate message queue testing.
* `Comicrelief\Behat\Context\MetaTagContext`: Contains context definitions to automate HTTP meta tag validations.
* `Comicrelief\Behat\Context\RestContext`: Contains context definitions to automate RESTful API testing

## Usage
Import to your project using composer
```bash
composer require --dev comicrelief/behat-tools
```
Use context classes in `behat.yml`
```yaml
default:
  suites:
    my-suite:
      paths:
        - %paths.base%/tests/Features
      contexts:
        - Comicrelief\Behat\Context\CommonContext
        - Comicrelief\Behat\Context\MessageQueueContext
...
``` 

## Release management

Releases are automatically created for every merge into the master branch. If no release type is defined in the commit 
message, then the commit will default to being a `patch` release.

Release types should be defined in the initial line of the commit message as either a `patch`, `minor` or `major` 
release.

For reference please refer to the pull request template.

