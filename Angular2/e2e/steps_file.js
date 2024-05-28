'use strict';
// in this file you can append custom step methods to 'I' object
module.exports = function() {

    return actor({

        loginUser: function(username, password) {
            this.waitForText('CONNECT WITH');
            this.fillField('_username', username);
            this.fillField('_password', password);
            this.click('SIGN IN');
        },
        registrationUser: function() {
            this.attachFile('form input[name=file]', 'e2e/output/homepage.jpg');
            this.fillField('firstName', '');
            this.fillField('lastName', '');
            this.fillField('email', '');
            this.fillField('password', 'asa');
            this.fillField('plainPassword', 'tessdsdsad');
            this.fillField('email', 'asasasasa');
            this.fillField('password', 'asasasa');
            this.fillField('plainPassword', 'asasasa');
            this.fillField('firstName', 'ARTUR');
            this.fillField('lastName', 'Tovmasyan');
            this.fillField('email', 'user1@user.com');
            this.fillField('password', 'Test1234');
            this.fillField('plainPassword', 'Test1234');
            this.setDateFields(2);
            this.click('register');
            this.wait(1.5);
            this.amOutsideAngularApp();
            this.see('Account with this email already exists, please, sign in.');
            this.fillField('email', 'testuser@test.com');
            this.click('register');
            this.wait(1.5);
        },
        setDateFields: function(data) {
            this.click('//div[@class="col-xs-4"][1]');
            this.click('//md-option['+data+']');
            this.click('//div[@class="col-xs-4"][2]');
            this.wait(1);
            this.click('//md-option['+data+']');
            this.click('//div[@class="col-xs-4"][3]');
            this.wait(1);
            this.click('//md-option['+data+']');
        },
      changeNotifySettings: function () {
        this.click('//md-slide-toggle[1]');
        this.click('//md-slide-toggle[2]');
        this.click('//md-slide-toggle[3]');
        this.click('//md-slide-toggle[4]');
        this.click('//md-slide-toggle[5]');
        this.click('//md-slide-toggle[6]');
        this.click('//md-slide-toggle[7]');
        this.click('//md-slide-toggle[8]');
        this.click('//md-slide-toggle[9]');
        this.click('//md-slide-toggle[10]');
      }

  });
};
