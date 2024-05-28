/// <reference path="../steps.d.ts" />
Feature('Test goal inner page');

BeforeSuite((I) => {
  I.resizeWindow('maximize');
});

Scenario('Test goal inner page', (I) => {
  I.amOnPage('/');
  I.see('JOIN');
  I.click('JOIN');
  I.loginUser('user1@user.com', 'Test1234');
  I.seeCurrentUrlEquals('/ideas');
  I.wait(1);
  I.amOutsideAngularApp();
  I.click('a.user-popover');
  I.click('My Bucketlist');
  I.waitForText('user1 useryan', 5);
  I.amOnPage('/goal/goal4');
  I.waitForText('One must be a fox in order to recognize traps, and a lion to frighten off wolves.');
  I.executeScript('window.scrollTo(0, 250);');
  I.click('//button[@class="mat-button"]');
  I.wait(1);
  I.fillField('commentBody', 'My first comment added');
  I.pressEnterOnComment();
  I.wait(2);
});
