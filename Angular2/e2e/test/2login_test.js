/// <reference path="../steps.d.ts" />
Feature('Test login functionality');

BeforeSuite((I) => {
    I.resizeWindow('maximize');
});

Scenario('Check login functionality', (I) => {
    I.amOnPage('/');
    I.see('JOIN');
    I.click('JOIN');
    I.loginUser('adsscdsf@tesdfsdst.com', 'Test1234');
    I.see('Bad credentials');
    I.loginUser('testuser@test.com', 'Test1234');
    I.seeCurrentUrlEquals('/ideas');
    I.amOutsideAngularApp();
    I.click('a.user-popover');
    I.click('Logout');
    I.seeCurrentUrlEquals('/');
    I.click('JOIN');
    I.waitForText('CONNECT WITH', 3);
    I.click('Forgot password?');
    I.seeCurrentUrlEquals('/resetting/request');
    I.see('Reset your password');
    I.fillField('email', 'asasas@mail.ru');
    I.click('Send');
    I.waitForText('User not found');
    I.see('User not found');
    I.fillField('email', 'testuser@test.com');
    I.click('Send');
    I.wait(1);
    I.seeCurrentUrlEquals('/resetting/check-email');
    I.see('Check your email');
    I.click('JOIN');
    I.waitForText('Join');
    I.waitForText('CONNECT WITH', 3);
    I.click('.mat-button-ripple', '#login-page');
    I.amOutsideAngularApp();
    I.switchToWindow(1);
    I.waitForText('Facebook', 25);
    I.fillField('email', 'test2test.am');
    I.fillField('pass', 'test1324');
    I.click('login');
    I.switchToWindow(0);
    I.click('a.google');
    I.wait(5);
    I.click('a.twitter');
    I.wait(6);
    I.saveScreenshot('login.jpg');
});
