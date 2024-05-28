/// <reference path="../steps.d.ts" />
Feature('Test Settings functionality');

BeforeSuite((I) => {
  I.resizeWindow('maximize');
});

Scenario('Test Settings all functionality', (I) => {

  I.amOnPage('/');
  I.see('JOIN');
  I.click('JOIN');
  I.loginUser('user2@user.com', 'Test1234');
  I.seeCurrentUrlEquals('/ideas');
  I.wait(1);
  I.amOutsideAngularApp();
  I.click('a.user-popover');
  I.click('Settings');
  I.waitForText('userToo useryan');
  I.seeCurrentUrlEquals('/edit/profile');
  I.click('Notification');
  I.waitForText('What you will receive');
  I.changeNotifySettings();
  I.click('save');
  I.waitForText('Your profile has been successfully updated', 5);
  I.changeNotifySettings();
  I.click('save');
  I.waitForText('Your profile has been successfully updated', 5);
  I.executeScript('window.history.back();');
  I.wait(2);
  I.attachFile('form input[name=file]', 'e2e/output/login.jpg');
  I.fillField('input[formControlName=firstName]', 'testUserToo');
  I.fillField('input[formControlName=lastName]', 'leonids');
  I.click('//md-radio-button[@ng-reflect-value="testangular@ang.com"]');
  I.click('save');
  I.waitForText('Your profile has been successfully updated', 5);
  I.click('//md-radio-button[@ng-reflect-value="user2@user.com"]');
  I.fillField('input[formControlName=addEmail]', 'grno@mail.ru');
  I.click('save');
  I.waitForText('Your profile has been successfully updated', 5);
  I.fillField('input[formControlName=currentPassword]', 'Test1234');
  I.fillField('input[formControlName=password]', 'Test1234');
  I.fillField('input[formControlName=plainPassword]', 'Test1234');
  I.setDateFields(2);
  I.click('//md-select[@formcontrolname="language"]');
  I.wait(1);
  I.click('//md-option[2]');
  I.click('save');
  I.waitForText('Ваш профиль был успешно обновлен', 5);
  I.fillField('input[formControlName=currentPassword]', 'Test1234');
  I.click('//md-select[@formcontrolname="language"]');
  I.wait(1);
  I.click('//md-option[1]');
  I.click('save');
  I.saveScreenshot('settings.jpg');

});
