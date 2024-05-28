'use strict';
let chai = require('chai');
let protractor = require('protractor');
let expect = chai.expect;

class MyHelper extends Helper {

  _before() {}

  _after() {}

    /**
     * This function is used to switch on popup in test
     *
     * @param param
     */
  switchToWindow(param) {
      browser.getAllWindowHandles().then(function(handles){
          browser.switchTo().window(handles[param]);
      });
  }

  /**
   * This function is used to check text exist by element class and text
   *
   * @param selector
   * @param text
   */
  checkIfTextExist(selector, text) {
        element(by.xpath("//a[@class=\""+selector+"\"]")).getText().then(function (value) {
        expect(value).to.be.equal(text);
      });
  }

  /**
   * This function is used to check leader board list count
   */
  checkLeaderBoardList() {
    let itm = element.all(by.xpath('//div[@class="col-xs-7 col-sm-9"]//ul'));

    itm.count().then(function(originalCount) {
      expect(originalCount).to.be.equal(11);
    });
  }

  /**
   * This function is used to press ENTER on search field
   *
   */
  pressEnterOnSearch() {
    element(by.xpath('//input[@name="search"]')).sendKeys(protractor.Key.ENTER);
  }

  /**
   * This function is used to press ENTER on comment field
   *
   */
  pressEnterOnComment() {
    element(by.xpath('//textarea[@name="commentBody"]')).sendKeys(protractor.Key.ENTER);
  }

  /**
   *
   * @param data
   */
  ignoreSynchronize(data) {
    browser.ignoreSynchronization = data;
  }

}

module.exports = MyHelper;
