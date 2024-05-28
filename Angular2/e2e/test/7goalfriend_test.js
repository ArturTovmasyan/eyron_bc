/// <reference path="../steps.d.ts" />
Feature('Test goalfriend page');

BeforeSuite((I) => {
  I.resizeWindow('maximize');
});

Scenario('Test goal friend page functionality', (I) => {
  I.amOnPage('/');
  I.see('JOIN');
  I.click('JOIN');
  I.loginUser('user1@user.com', 'Test1234');
  I.seeCurrentUrlEquals('/activity');
  I.wait(1);
  I.amOutsideAngularApp();
  I.click('a.user-popover');
  I.click('Goalfriends');
  I.waitForText('Most Active');
  I.seeCurrentUrlEquals('/goal-friends');
  I.fillField('search', 'user10');
  I.pressEnterOnSearch();
  I.wait(1);
  I.waitForText('user10 user10');
  I.click('span.close-icon');
  I.waitForText('user3 user3');
  I.click('Recently Matched');
  I.waitForText('user3 user3', 5);
  I.click('Most Matching');
  I.waitForText('No result');
  I.click('Most Active');
  I.waitForText('user9 user9', 5);
  I.click('Following');
  I.waitForText('No result', 5);
  I.click('All');
  I.waitForText('In Common 3', 5);
  I.wait(0.5);
  I.click('//h4[contains(text(), "user3")]');
  I.waitForText('Create a Goal');
  I.click('a.user-popover');
  I.click('Goalfriends');
  I.waitForText('Most Active');
  I.seeCurrentUrlEquals('/goal-friends');
  I.wait(0.5);
  I.click('//a[contains(text(), "Common")]');
  I.amOutsideAngularApp();
  I.waitForText('Added', 5);
  I.waitForText('Complete', 5);
  I.see('goal6', '#common-modal');
  I.see('goal7', '#common-modal');
});
