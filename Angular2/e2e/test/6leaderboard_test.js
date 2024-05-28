/// <reference path="../steps.d.ts" />
Feature('Test leader board page');

BeforeSuite((I) => {
  I.resizeWindow('maximize');
});

Scenario('Test leader board page functionality', (I) => {
  I.amOnPage('/');
  I.see('JOIN');
  I.click('JOIN');
  I.loginUser('user1@user.com', 'Test1234');
  I.seeCurrentUrlEquals('/activity');
  I.wait(1);
  I.amOutsideAngularApp();
  I.click('a.user-popover');
  I.click('Leaderboard');
  I.waitForText('user9 user9', 3);
  I.seeCurrentUrlEquals('/leaderboard');
  I.wait(1);
  I.checkIfTextExist('text-dark-gray', 'user3 user3');
  I.checkLeaderBoardList();
  I.click('Mentor');
  I.waitForVisible('div.leaderboard-space', 5);
  I.click('Innovator');
  I.waitForVisible('div.leaderboard-space', 5);
  I.waitForText('user7 user7', 3);
  I.saveScreenshot('leaderBoard.jpg');
});
