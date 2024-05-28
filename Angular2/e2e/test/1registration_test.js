/// <reference path="../steps.d.ts" />
Feature('Check registration');

BeforeSuite((I) => {
    I.resizeWindow('maximize');
});

Scenario('Test registration form and all process', (I) => {
    I.amOnPage('/');
    I.see('JOIN');
    I.click('JOIN');
    I.waitForText('CONNECT WITH');
    I.click('SIGN UP');
    I.seeCurrentUrlEquals('/register');
    I.see('Sign up and discover great ideas');
    I.registrationUser();
    I.wait(2);
    I.saveScreenshot('registration.jpg');
});
