/// <reference path="../steps.d.ts" />
Feature('Test add goal functionality');

BeforeSuite((I) => {
  I.resizeWindow('maximize');
});

Scenario('Test add goal', (I) => {
  I.amOnPage('/');
  I.see('JOIN');
  I.click('JOIN');
  I.loginUser('user1@user.com', 'Test1234');
  I.seeCurrentUrlEquals('/ideas');
  I.wait(1);
  I.amOutsideAngularApp();
  I.waitForText('goal1', 2);
  I.click('Add');
  I.amOutsideAngularApp();
  I.wait(1);
  I.click('Completed');
  I.click('Active');
  I.see('Goal Status');
  I.see('Visibility');
  I.see('Deadline');
  I.setDateFields(2);
  I.click('Invisible');
  I.click('Visible');
  I.click('Save');
  I.amOutsideAngularApp();
  I.click('a.user-popover');
  I.click('My Bucketlist');
  I.waitForVisible('i.icon-manage', 5);
  I.click('Manage');
  I.waitForText('Goal Status', 5);
  I.click('Invisible');
  I.click('Visible');
  I.click('NEXT');
  I.waitForText('Priority');
  I.amOutsideAngularApp();
  I.wait(1);
  I.click('Save');
  I.click('Manage');
  I.waitForText('Goal Status', 5);
  I.click('REMOVE');
  I.waitForText('YOU ARE ABOUT TO REMOVE YOUR GOAL.');
  I.click('//div[@class="delete-content"]/a[1]');
  I.wait(1);
});
