<?php

namespace AppBundle\Features\Context;

use Behat\Behat\Tester\Exception\PendingException;
use Behat\MinkExtension\Context\MinkAwareContext;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Behat\Testwork\Hook\Scope\BeforeSuiteScope;
use Symfony\Component\HttpFoundation\Session\Session;
use Behat\Symfony2Extension\Context\KernelDictionary;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\MinkExtension\Context\MinkContext;

class FeatureContext extends MinkContext implements KernelAwareContext, SnippetAcceptingContext, MinkAwareContext
{
    use KernelDictionary;

    public function __construct(Session $session, $simpleArg)
    {
    }

    /**
     * @When I wait for angular
     */
    public function iWaitForAngular()
    {
        //&& 0 === jQuery.active
        $this->getSession()->wait(2000, "typeof(jQuery)=='undefined'");
    }

    /**
     * @When I wait for ajax
     */
    public function iWaitForAjax()
    {
        $this->getSession()->wait(5000, "0 === jQuery.active");
    }

    /**
     * @When I wait
     */
    public function iWait()
    {
        $this->getSession()->wait(5000, "document.readyState == 'complete'");
    }

    /**
     * @When I wait for view :time
     */
    public function iWaitForView($time)
    {
        $this->getSession()->wait($time);
    }

    /**
     * @BeforeScenario
     */
    public function BeforeScenario()
    {
        $this->getSession()->getDriver()->maximizeWindow();
    }

    /** @BeforeSuite */
    public static function callFixturesCommand(BeforeSuiteScope $scope)
    {
        $scope->output = shell_exec('./bin/behat.sh');
    }

    /**
     * @Given I am logged in as :user
     */
    public function iAmLoggedInAs($user)
    {
        //set default value
        $userName = null;

        //set userName
        if($user == 'admin') {
            $userName = 'admin@admin.com';
        }
        elseif($user == 'user1')
        {
            $userName = 'user1@user.com';
        }
        elseif($user == 'user2')
        {
            $userName = 'user2@user.com';
        }
        elseif($user == 'user5')
        {
            $userName = 'user15@user.com';
        }

        //set password
        $password = 'Test1234';

        //open login form
        $this->clickLink('JOIN');

        $this->iWaitForView(500);

        //set data in login form
        $this->iSetUsernameAndPassword($userName, $password);

        //wait for open page after login
        $this->iWaitForView(1000);

        //check if user admin
        if($user == 'admin') {
            $this->assertSession()->pageTextContains('admin');
        }
        else
        {
            $this->assertSession()->pageTextContains('user');
        }
    }

    /**
     * @Then I should see categories
     */
    public function iShouldSeeCategories()
    {
        //get session
        $session = $this->getSession(); // assume extends RawMinkContext

        //get page
        $page = $session->getPage();

        $categoryCount = $page->findAll('xpath', '//ul[@class="filter"]//li');

        if(count($categoryCount) == 9) {
            return;
        }
        else{
            throw new \LogicException('Wrong category count');
        }
    }

    /**
     * @When I set username :userName and password :password
     */
    public function iSetUsernameAndPassword($userName, $password)
    {
        //get session
        $session = $this->getSession(); // assume extends RawMinkContext

        //get page
        $page = $session->getPage();

        //find username and set data
        $page->fillField('_username', $userName);

        //find password
        $page->fillField('_password', $password);

        $element = $page->find('xpath', '//button[@type="submit"]');

        $element->doubleClick();
    }

    /**
     * @When I select date fields in manage goal :count
     */
    public function iSelectDateFieldsInManageGoal($count)
    {
        //get mink session
        $session = $this->getSession(); // assume extends RawMinkContext

        //get current page
        $page = $session->getPage();

        //get date fields
        $dateElements = $page->findAll(
            'xpath',
            $session->getSelectorsHandler()->selectorToXpath('xpath', '//div[contains(@class, "col-sm-4 date") and not(contains(@class ,"ng-hide"))]')
        );

        if (null === $dateElements) {
            throw new \InvalidArgumentException(sprintf('Cannot find $dateElements'));
        }

        foreach($dateElements as $dateElement) {

            //click on date filed
            $dateElement->click();

            //get options list in field
            $optionsList = $dateElement->find('css', '.ui-select-choices-group');

            //get value in opt
            $option = $optionsList->findAll(
                'xpath',
                $session->getSelectorsHandler()->selectorToXpath('xpath', '//a[@class="ui-select-choices-row-inner"]')
            );

            $option[3]->click();
            if($count == "one") {
                break;
            }
        }
    }

    /**
     * @When I select date fields
     */
    public function iSelectDateFields()
    {
        //get mink session
        $session = $this->getSession(); // assume extends RawMinkContext

        //get current page
        $page = $session->getPage();

        //get date fields
        $dateElements = $page->findAll('css','.date');

        if (null === $dateElements) {
            throw new \InvalidArgumentException(sprintf('Cannot find $dateElements'));
        }

        foreach($dateElements as $dateElement) {

            //click on date filed
            $dateElement->click();

            //get options list in field
            $optionsList = $dateElement->find('css', '.list');

            //get value in opt
            $option = $optionsList->find(
                'xpath',
                $session->getSelectorsHandler()->selectorToXpath('xpath', '//li[4]')
            );

            $option->click();

        }
    }

    /**
     * @When I select language
     */
    public function iSelectLanguage()
    {
        //get session
        $session = $this->getSession(); // assume extends RawMinkContext

        $page = $session->getPage();

        $languageField = $page->find('css', '.lng');

        $languageField->click();

        $option = $languageField->find('css', '.list');

        $lang = $option->find(
            'xpath',
            $session->getSelectorsHandler()->selectorToXpath('xpath', '//li[not(contains(@class, "selected"))]')
        );

        $lang->click();
    }

    /**
     * @When I scroll page to :value
     */
    public function iScrollPageTo($value)
    {
        //get session
        $session = $this->getSession();

        if($value == 'top') {
            $session->executeScript("window.scrollTo(0, document.body.scrollTop)");
        }
        else {
            $session->executeScript("jQuery($('body').scrollTo('".$value."'));");
        }
    }

    /**
     * @When I press key :key
     */
    public function iPressKey($key)
    {
        //get session
        $session = $this->getSession();

        //13 it is 'enter' key on keyboard for selenium2 Driver
        $session->getDriver()->keyPress('//input[@name="search"]', $key);
    }

    /**
     * @When I switch to iframe :value
     */
    public function iSwitchToIframe($value)
    {
        //get session
        $session = $this->getSession(); // assume extends RawMinkContext

        //get page
        $page = $session->getPage();

        //get iframe blcok
        $iframe = $page->find('css', $value);

        //check if iframe block exist
        if (null === $iframe) {
            throw new \LogicException('Iframe is not found');
        }

        //get iframe id
        $iframeId = $iframe->getAttribute('id');

        if($iframeId == null) {

            //get iframe name
            $iframeId = $iframe->getAttribute('name');
        }

        if (null === $iframeId) {

            throw new \LogicException('Iframe id is not found');
        }

        //swith to iframe window
        $this->getSession()->switchToIframe($iframeId);
    }

    /**
     * @When I click on :value
     */
    public function iClickOn($value)
    {
        //get session
        $session = $this->getSession(); // assume extends RawMinkContext

        //get page
        $page = $session->getPage();

        //get icon
        $icon = $page->find('xpath',$session->getSelectorsHandler()->selectorToXpath('xpath', '//*[contains(@class, normalize-space("'.$value.'")) or contains(@id, normalize-space("'.$value.'"))]'));

        //click on icon
        $icon->click();
    }

    /**
     * @When I click on close :value
     */
    public function iClickOnClose($value)
    {
        //get session
        $session = $this->getSession(); // assume extends RawMinkContext

        //get page
        $page = $session->getPage();

        //get close button in login popover
        $icon = $page->find(
            'xpath',
            $session->getSelectorsHandler()->selectorToXpath('xpath', '//div[@id="signin"]//*[@class="'.$value.'"]')
        );

        //click on icon
        $icon->click();
    }

    /**
     * @When I switch to window
     */
    public function iSwitchToWindow()
    {
        //get window name
        $windowNames = $this->getSession()->getWindowNames();
        if(count($windowNames) > 1) {

            //switch to new window
            $this->getSession()->switchToWindow($windowNames[1]);
        }
    }

    /**
     * @When I change date
     */
    public function iChangeDate()
    {
        //get session
        $session = $this->getSession(); // assume extends RawMinkContext

        //get page
        $page = $session->getPage();

        //get date
        $date = $page->find('xpath',$session->getSelectorsHandler()->selectorToXpath('xpath', '//td[@class="day"][7]'));

        //click on icon
        $date->click();
    }

    /**
     * @When I change priority
     */
    public function iChangePriority()
    {
        //get session
        $session = $this->getSession(); // assume extends RawMinkContext

        //get page
        $page = $session->getPage();

        //get date
        $priority = $page->findAll('xpath',$session->getSelectorsHandler()->selectorToXpath('xpath', '//md-checkbox'));

        //click on icon
        $priority[0]->click();
    }

    /**
     * @When I change switch :number
     */
    public function iChangeSwitch($number)
    {
        //get session
        $session = $this->getSession(); // assume extends RawMinkContext

        //get page
        $page = $session->getPage();

        //get date
        $switchs = $page->findAll('xpath',$session->getSelectorsHandler()->selectorToXpath('xpath', '(//label[@class="onoffswitch-label"])'));

        //click on icon
        $switchs[$number]->click();
    }

    /**
     * @When I check radio :number
     */
    public function iCheckRadio($number)
    {
        //get session
        $session = $this->getSession(); // assume extends RawMinkContext

        //get page
        $page = $session->getPage();

        //get date
        $switchs = $page->findAll('xpath',$session->getSelectorsHandler()->selectorToXpath('xpath', '(//md-radio-button)'));

        //click on icon
        $switchs[$number]->click();
    }

    /**
     * @When I check subfilters :value
     */
    public function iCheckSubfilters($value)
    {
        //get session
        $session = $this->getSession(); // assume extends RawMinkContext

        //get page
        $page = $session->getPage();

        //get all sub filters in my bucket list
        $subFilters = $page->findAll('xpath',$session->getSelectorsHandler()->selectorToXpath('xpath', '(//ins[@class="iCheck-helper"])'));

        $subFilters[$value]->click();
    }

    /**
     * @When I attache profile image
     */
    public function iAttacheProfileImage()
    {
        //get image path
        $path = __DIR__.'/leon.jpg';

        //get file filed name
        $field = 'bl_user_settings_file';

        //set step argument for file field
        $field = $this->fixStepArgument($field);

        //get session
        $session = $this->getSession(); // assume extends RawMinkContext

        //remove hide class for upload file
        $javascript = '$( "div" ).removeClass( "hide" );';

        //execute js code
        $session->executeScript($javascript);

        //attache picture for user
        $this->attachFileToField($field, $path);
    }

    /**
     * @Then I click on show more
     */
    public function iClickOnShowMore()
    {
        //get session
        $session = $this->getSession(); // assume extends RawMinkContext

        //get page
        $page = $session->getPage();

        //get all sub filters in my bucket list
        $linkBlock = $page->find('xpath',$session->getSelectorsHandler()->selectorToXpath('xpath', '(//div[@class="navigation text-center"])'));

        //get show link
        $link = $linkBlock->find('css', '.show-more');

        //click on show more link
        $link->click();
    }

    /**
     * @When I click on select2 field
     */
    public function iClickOnSelectField()
    {
        //get session
        $session = $this->getSession(); // assume extends RawMinkContext

        //get page
        $page = $session->getPage();

        //get all sub filters in my bucket list
        $linkBlock = $page->findAll('xpath',$session->getSelectorsHandler()->selectorToXpath('xpath', '(//ul[@class="select2-results"]//div[@class="select2-result-label"])'));

        //click on show more link
        $linkBlock[3]->click();
    }

    /**
     * @When I follow merge goal
     */
    public function iFollowMergeGoal()
    {
        //get session
        $session = $this->getSession(); // assume extends RawMinkContext

        //get page
        $page = $session->getPage();

        //get merge goal a tag
        $mergeGoal = $page->findAll('xpath',$session->getSelectorsHandler()->selectorToXpath('xpath', '(//a[@title="Merge"])'));

        //get merge goal9 link
        $linkMergeGoal = $mergeGoal[8]->getAttribute('href');

        //click on show more link
        $this->visit($linkMergeGoal);

    }

    /**
     * @Given I click button :arg1
     */
    public function iClickButton($arg1)
    {
        $class = null;

        if($arg1 == 'add') {
            $class = 'icon-plus-icon';
        }
        elseif($arg1 == 'done') {
            $class = 'icon-ok-icon';
        }

        //get session
        $session = $this->getSession(); // assume extends RawMinkContext

        //get page
        $page = $session->getPage();

        //get merge goal a tag
        $goalLink = $page->find('xpath',$session->getSelectorsHandler()->selectorToXpath('xpath', '(//i[@class="'.$class.'"])'));

        $session->executeScript("window.scrollBy(0,20)", "");

        //click goal link
        $goalLink->click();
    }

    /**
     * @When I click notify icon
     */
    public function iClickNotifyIcon()
    {
        //get session
        $session = $this->getSession(); // assume extends RawMinkContext

        //execute js code
        $session->executeScript(' $( "div.dropdown" ).addClass( "open" );');
    }

    /**
     * @When I click mark as read
     */
    public function iClickMarkAsRead()
    {
        //get session
        $session = $this->getSession(); // assume extends RawMinkContext

        //execute js code
        $session->executeScript(' $.get( "http://behat.bucketlist.loc/api/v1.0/notification/all/read" );');
    }

    /**
     * @Then I should not see number on note icon
     */
    public function iShouldNotSeeNumberOnNoteIcon()
    {
        //get session
        $session = $this->getSession(); // assume extends RawMinkContext

        //get page
        $page = $session->getPage();

        //get merge goal a tag
        $noteLink = $page->find('xpath',$session->getSelectorsHandler()->selectorToXpath('xpath', '(//sup[@class="ng-binding"])'));

        if(count($noteLink) > 0) {
            throw new PendingException("Notification read all link don't work correctly");
        }
    }


    /**
     * @Then I hover over the element
     */
    public function iHoverOverTheElement()
    {
        //get session
        $session = $this->getSession();
        
        //get page
        $page = $session->getPage();

        //get element for hover
        $element = $page->find('xpath',$session->getSelectorsHandler()->selectorToXpath('xpath', '(//ul[@id="notification-list"]//li[2]//div[@class="clearfix"])'));

        // errors must not pass silently
        if (null === $element) {
        throw new \InvalidArgumentException(sprintf('Could not evaluate CSS selector: "%s"', 'notification-list'));
        }

        // ok, let's hover it
        $element->mouseOver();

        //get merge goal a tag
        $removeLinks = $page->find('xpath',$session->getSelectorsHandler()->selectorToXpath('xpath', '(//ul[@id="notification-list"]//i[@class="close-icon"])'));

        $removeLinks->click();

        $this->iWaitForView(1000);
    }

    /**
     * @Then I click on remove button
     */
    public function iClickOnRemoveButton()
    {
        //get session
        $session = $this->getSession();

        //get page
        $page = $session->getPage();

        //get element for hover
        $element = $page->find('xpath',$session->getSelectorsHandler()->selectorToXpath('xpath', '(//a[text()="REMOVE"])'));

        // errors must not pass silently
        if (null === $element) {
            throw new \InvalidArgumentException(sprintf('Could not evaluate CSS selector: "%s"', 'notification-list'));
        }

        $element->click();
        
        $this->iWaitForView(2000);
    }
}
