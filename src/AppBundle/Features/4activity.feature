Feature: Activity
  In order to see Activity page
  As a user
  I should have goals/goalfriends
  Where I see what my goalfriends have done

  @javascript @remainingActivity
  Scenario: All the remaining features in Activity page
    Given I am on "/logout"
    And I wait for angular
    When I am logged in as "user2"
    And I am on "/goal/goal9"
    And I follow "ADD"
    And I wait for angular
    And I scroll page to ".modal-bottom"
    And I wait
    And I check radio "3"
    And I wait
    And I check radio "2"
    And  I click on "btn btn-purple"
    And I wait for angular
    And I am on "/profile"
    And I scroll page to ".information"
    And I click on "btn btn-transparent"
    And I wait for angular
    And I scroll page to ".btn btn-transparent button-lg"
    And I click on "btn btn-transparent button-lg"
    And I wait
    And I scroll page to ".information"
    Then I should see "SUCCESS STORY"
    When I click on "btn btn-transparent successtory ng-isolate-scope"
    And I wait for angular
    And I fill in "story" with "STORY1"
    And I fill in "videoLink0" with "www.google.com"
    And I wait for view "500"
    And I fill in "videoLink1" with "www.google.am"
    And I wait for view "500"
    And I fill in "videoLink2" with "www.google.ru"
    And I wait for view "500"
    And I click on "btn btn-purple button-lg"
    And I wait for view "1500"
    And I am on "/logout"
    And I wait for angular
    And I am logged in as "user1"
    Then I should see "user1"
    And I should be on "/activity"

    When I click on "navbar-right"
    And I am on "/profile"
    And I wait
    And I should see "Active"
    And I should see "Completed"
    When I am on "/goal-friends"
    And I wait for view "2000"
    Then I should see "user2"
    And I scroll page to "icon-top-idea"
    And I should see "Top Ideas"
    When I follow "Top Ideas"
    Then I should be on "/ideas/most-popular"
    And I should not see "Sorry, we couldn't find anything, but you can explore other ideas:"
    When I scroll page to ".social"
    And I wait for view "500"

  @javascript @notification
  Scenario: Check notification functionality
    Given I am on "/logout"
    And I wait
    When I am logged in as "user1"
    Then I should see "user1"
    And I should see "4"
    When I click notify icon
    And I wait
    And I am on "/notifications"
    And I wait
    Then I should see "TEST3 NOTE"
    And I hover over the element
    And I should not see "userTo useryan wrote success story on your important goal"
    And I click mark as read
    And I am on "/"
    And I reload the page
    Then I should not see number on note icon
    And I wait

  @javascript @goalFriend
  Scenario: Show me the my goalfriends and when I click on them let me see their inner pages
    Given I am on "/logout"
    And I wait
    When I am logged in as "user1"
    And I should see "user1"
    When I am on "/goal-friends"
    And I wait for angular
    Then I should see "user3 user3"
    And I should see "Listed"
    And I should see "Completed"
    When I click on "col-xs-9"
    And I wait
    And I should see "All"
    And I should see "In Common"

  @javascript @userProfileGoalText
  Scenario: Other user profile empty goal text checking
    Given I am on "/logout"
    And I wait
    When I am logged in as "user1"
    And I should see "user1"
    And I am on "/profile/777777"
    And I wait
    Then I should not see "What are you doing here? Come on, add some goals"
    When I follow "Active"
    Then I should not see "Your life needs goals, add some more."
    When I follow "Completed"
    Then I should not see "It’s time to act and complete some goals."
    And I wait for view "500"

  @javascript @activity
  Scenario: Open the page and show me my goal friends activities.
    Given I am on "/logout"
    And I wait for angular
      When I am logged in as "user1"
      Then I should be on "/activity"
      And I wait
      And I should see "user2"
      And I should see "goal9"
      And I should see "ADDED"
      And I wait

  @javascript @successStories
  Scenario: Show me success stories
      Given I am on "/logout"
      And I wait
      When I am logged in as "user1"
      And I should see "user2"
      And I am on "/goal/goal9"
      Then I should see "goal9"
      When I scroll page to ".text-dark-gray"
      Then I should see "Success stories"
      When I wait for view "1000"
      Then I should see "user2 useryan"
      And I should see "STORY1"
      And I wait


  @javascript @innerPage
  Scenario: Open idea inner page and show me the corresponding features.
    Given I am on "/logout"
    And I wait
    When I am logged in as "user1"
    And I should see "user1"
    And I am on "/goal/goal9"
    And I wait for angular
    Then I should see "One must be a fox in order to recognize traps, and a lion to frighten off wolves."
    And I should see "goal9"
    And I click on "icon-manage"
    And I wait for angular
    And follow "Cancel"
    And I wait for view "1000"
    And I click on "icon-ok-icon"
    And I wait for angular
    And follow "Cancel"
    And I reload the page
    When I scroll page to ".text-dark-gray"
    And I wait
    Then I should see "COMPLETED BY"
    And I should see "LISTED"
    When I am on "/done-users/goal9"
    And I wait for view "1000"
    Then I should see "user2"
    And I move backward one page
    When I am on "/listed-users/goal9"
    And I wait
    Then I should see "user1"
    And I should see "user2"
    When I move backward one page
     And I am on "/goal/goal2"
    Then I should not see "One must be a fox in order to recognize traps, and a lion to frighten off wolves."
    And I should not see "Map"
    When I am on "/goal/goal9"
    And I wait
    And I scroll page to ".text-dark-gray"
    And I wait

  @javascript @profile
  Scenario: Open Complete profile dropdown and show me the 7 points in it
    Given I am on "/logout"
    And I wait
    When I am logged in as "user1"
    And I am on "/profile"
    And I click on "question-icon-new"
    And I wait for view "500"
    And I follow "Upload an image"
    And I wait for view "500"
    And I am on "/profile"
    And I wait
    And I click on "question-icon-new"
    And I wait for view "500"
    And I am on "/goal/create"
    And I should see "user1"

  @javascript @someMore
  Scenario: Open My Bucket List and follow the instructions
    Given I am on "/logout"
    And I wait
    When I am logged in as "user1"
    And I wait
    And I am on "/profile"
    And I follow "Create a Goal"
    Then I should be on "/goal/create"
    When I move backward one page
    And I am on "/ideas"
    And I wait
