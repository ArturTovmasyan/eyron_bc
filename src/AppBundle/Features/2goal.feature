Feature: Goal page
  In order to use goal create page and check it
  As a user1
  I need to be able to check goal create page

  Background:
    Given I am on "/logout"
    And I wait
    And I am logged in as "user1"

  @javascript @goalFriendSearch
  Scenario: Open goal friends page and try search friends
    When I am on "/goal-friends"
    And I wait for view "1000"
    Then I should see "user7 user7"
    And I should see "user10 user10"
    When I fill in "searchInput" with "user7 user7"
    And I am on "/goal-friends?search=user7+user7#"
    And I wait for view "2000"
    Then I should see "user7 user7"

  @javascript @goalManageDate
  Scenario: Test for completed and deadline date in goal manage
    When I click on "navbar-right"
    And I am on "/profile"
    And I scroll page to ".information"
    And I follow "Manage"
    Then I should see "Active"
    When I select date fields in manage goal "one"
    And I follow "Save"
    And I wait for angular
    Then I should see "user1"
    When I follow "Manage"
    And I wait for view "1000"
    Then I should see "2018"
    And I check radio "1"
    And I wait for view "500"
    And I should see "Deadline : 2018"
    When I check radio "0"
    And I should see "Priority"

  @javascript @goalActiveCompleted
  Scenario: Open My Bucket list and check Active/Completed filter for empty/no-empty goal
    When I click on "navbar-right"
    And I am on "/profile"
    And I wait
    Then I should see "goal9"
    When I follow "Active"
    And I wait for view "500"
    Then I should see "goal9"
    When I follow "Completed"
    And I wait for view "500"
    Then I should see "goal3"
    And I should see "Dreaming"
    And I wait for view "500"
    When I am on "/logout"
    And I wait
    And I am logged in as "user2"
    Then I should see "user2"
    When I am on "/profile"
    Then I should see "What are you doing here? Come on, add some goals"
    When I follow "Active"
    And I wait for view "1000"
    Then I should see "Your life needs goals, add some more"
    When I follow "Completed"
    And I wait for view "1000"
    Then I should see "It’s time to act and complete some goals"
    And I wait for view "500"

  @javascript @manageGoal
  Scenario: Open manage and let me change whatever I want
    When I click on "navbar-right"
    And I am on "/profile"
    When I scroll page to ".information"
    And I follow "Manage"
    Then I should see "Active"
    When I select date fields in manage goal "all"
    And I change priority
    And I fill in "stepText[ 0 ]" with "step 1"
    And I check radio "3"
    And I wait
    And I check radio "2"
    And I follow "Save"
    Then I should see "user1"
    And I wait for angular
    When I scroll page to ".information"
    And I follow "Manage"
    And I wait for angular
    When I check radio "1"
    And I wait for view "500"
    And I click on "btn btn-purple"
    Then I should see "user1"

  @javascript @preview
  Scenario: Open Preview and show me the initial state of my goal
    When I click on "navbar-right"
    And I am on "/goal/create"
    Then I should see "user1"
    When I fill in "app_bundle_goal[title]" with "TEST GOALS"
    And I fill in "app_bundle_goal[description]" with "DESCRIPTION FOR BEHAT TEST GOALS"
    And I scroll page to "top"
    And I press "btn_publish"
    And I wait for angular
    Then I should see "CONGRATULATIONS, YOUR GOAL HAS BEEN SUCCESSFULLY CREATED"
    When I scroll page to ".modal-bottom"
    And I follow "Cancel"
    And I wait for angular
    Then I should be on "/profile"
    When I am on "/goal/create"
    And I click on "iCheck-helper"
    And I fill in "app_bundle_goal[title]" with "PRIVATE GOALS"
    And I fill in "app_bundle_goal[description]" with "DESCRIPTION FOR BEHAT PRIVATE GOALS"
    And I scroll page to "top"
    And I press "btn_publish"
    And I wait for angular
    And I follow "Save"
    And I wait for angular
    Then I should be on "/profile"

    When I am on "/goal/create"
    And I fill in "app_bundle_goal[title]" with "TEST3 GOALS3"
    And I fill in "app_bundle_goal[description]" with "DESCRIPTION FOR BEHAT TEST3 GOALS3"

    And I scroll page to "top"
    And I press "btn_preview"
    Then I should be on "/goal/view/test3-goals3"
    And I should see "EDIT"
    And I am on "/goal/my-ideas/drafts"
    And I should see "TEST3 GOALS3"

  @javascript @createDraft
  Scenario: Create drafts
    When I click on "navbar-right"
    And I am on "/goal/create"
    Then I should see "user1"
    When I fill in "app_bundle_goal[title]" with "TEST2 GOALS2"
    And I fill in "app_bundle_goal[description]" with "DESCRIPTION FOR BEHAT TEST2 GOALS2"
    And I scroll page to "top"
    And I press "btn_save_draft"
    And I wait for angular
    Then I should be on "/goal/my-ideas/drafts"

  @javascript @doneGoal
  Scenario: Done a goal
    When I click on "navbar-right"
    And I am on "/profile"
    When I scroll page to ".information"
    And I follow "Complete"
    And I am on "profile/completed-goals"
    Then I should be on "profile/completed-goals"
    When I scroll page to ".information"
    And I wait for view "1000"
    Then I should see "SUCCESS STORY"

  @javascript @goalCreatePage
  Scenario: Open the page and show all the features
    When I click on "navbar-right"
    And I am on "/goal/create"
    Then I should see "Suggest as public"
    When I click on "iCheck-helper"
    And I fill in "app_bundle_goal[title]" with "TEST GOALS"
    And I scroll page to "top"
    And I press "btn_publish"
    And I wait for angular
    And I fill in "app_bundle_goal[description]" with "DESCRIPTION FOR #test #test TEST #GOALS #GOALS"
    And I scroll page to "top"
    And I press "btn_publish"
    And I wait for angular
    Then I should see "CONGRATULATIONS, YOUR GOAL HAS BEEN SUCCESSFULLY CREATED"
    And I follow "Cancel"


  @javascript @goalDescriptionTest
  Scenario: Open the create page and check submit without desc.field fill
    When I click on "navbar-right"
    And I am on "/goal/create"
    Then I should see "Suggest as public"
    When I click on "iCheck-helper"
    And I fill in "app_bundle_goal[title]" with "TEST GOALS"
    And I press "btn_publish"
    And I wait for angular
    Then I should not see "CONGRATULATIONS, YOUR GOAL HAS BEEN SUCCESSFULLY ADDED"
    And I wait for view "500"

@javascript @addGoal
  Scenario: Add a goal
    Given I am on "/goal/goal1"
    And I wait
    And I follow "ADD"
    And I wait for angular
    Then I should see "CONGRATULATIONS, YOUR GOAL HAS BEEN SUCCESSFULLY ADDED"
    And I scroll page to "top"
    And I select date fields in manage goal "all"
    And I change priority
    And I fill in "stepText[ 0 ]" with "step 1"
    And I check radio "3"
    And I wait
    And I check radio "2"
    And I follow "Save"
    And I am on "/profile"
    When I scroll page to ".information"
    And I follow "Manage"
    And I wait
    And I follow "REMOVE"
    And I click on remove button
    Then I should be on "/profile"
    When I am on "/goal/goal1"
    Then I should see "ADD"


  @javascript @goalDraft
  Scenario: Open My Bucket list and show me the list of my drafts
    Given I am on "/profile"
    And I wait
    When I follow "My Ideas"
    Then I should be on "/goal/my-ideas"
    And I wait
    Then I should see "My Private Ideas"
    When I follow "Drafts"
    Then I should be on "/goal/my-ideas/drafts"
    And I should see "Edit"
    And I should see "Remove"
    When I follow "Edit"
    Then I should see "Suggest as public"
    And I scroll page to "top"
    And I press "btn_publish"
    And I wait for view "1000"
    And I follow "Save"
    And I wait for angular
    Then I should be on "/profile"
    When I am on "/goal/my-ideas/drafts"
    Then I should not see "TEST2 GOALS2"
    And I follow "Remove"
    And I wait for view "1000"
    When I click on "btn btn-danger"
    Then I should be on "/goal/my-ideas/drafts"
    And I should see "Currently there are no draft goals in your draft list"

