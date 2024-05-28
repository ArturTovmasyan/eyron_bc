Feature: My Bucket list
  In order to use my bucket list,home,search page and check it
  As a user1
  I need to be able to check my bucket list page

  @javascript @homepage
  Scenario: I should see Homepage irrespective of the fact whether I am logged in or not
    Given I am on "/"
    Then I should see "Have your list of goals for life and see how much more you would achieve over what you could possibly imagine."
    Then I should see "Discover thousands of great ideas for your Bucket List"
    When I click on "btn btn-purple"
    Then I should be on "/ideas"

  @javascript @search
  Scenario: Open Bucket List and show me my search result
    Given I am on "/ideas"
    When I fill in "search" with "goal1"
    And I press key "13"
    And I wait for view "1000"
    Then I should see "Listed by"
    And I should see "ADD"
    When I fill in "search" with "TEST777"
    And I press key "13"
    And I wait for angular
    Then I should see "Sorry, we couldn't find anything."


  @javascript @myBucketList
  Scenario: Open My BucketList and show me my all goals
    Given I am on "/logout"
    And I wait
    When I am logged in as "user1"
    And I click on "navbar-right"
    And I am on "/profile"
    And I wait
    Then I should see "Active"
    And I should see "user1 useryan"
    And I should see "Listed"
    And I should see "Completed"
    When I click on "pull-right"
    And I wait for angular

  @javascript @subfilters
  Scenario: Open subfilters:dreams, important, urgent.......
    Given I am on "/logout"
    And I wait
    When I am logged in as "user1"
    And I click on "navbar-right"
    Then I am on "/profile"
    When I check subfilters "0"
    And I wait for view "500"

    When I check subfilters "0"
    And I wait for view "500"
    And I check subfilters "1"
    And I wait for view "500"
    Then I should see "goal9"

    When I check subfilters "1"
    And I wait for view "500"
    And I check subfilters "2"
    And I wait for view "500"
    Then I should see "goal4"

    When I check subfilters "2"
    And I wait for view "500"
    And I check subfilters "3"
    And I wait for view "500"
    Then I should see "goal3"

    When I check subfilters "3"
    And I wait for view "500"
    And I check subfilters "4"
    And I wait for view "500"
    Then I should see "goal7"

    When I check subfilters "4"
    And I wait for view "500"

  @javascript @subfiltersEmpty
  Scenario: Open subfilters when not goal:dreams, important, urgent.......
    Given I am on "/logout"
    And I wait
    When I am logged in as "user2"

    And I click on "navbar-right"
    Then I am on "/profile"

    When I check subfilters "0"
    And I wait for view "500"
    Then I should see "No deadline, No goal! You are just dreaming "

    When I check subfilters "0"
    And I wait for view "500"
    And I check subfilters "1"
    And I wait for view "500"
    Then I should see "There are no goals to correspond to this priority combination "

    When I check subfilters "1"
    And I wait for view "500"
    And I check subfilters "2"
    And I wait for view "500"
    Then I should see "There are no goals to correspond to this priority combination "

    When I check subfilters "2"
    And I wait for view "500"
    And I check subfilters "3"
    And I wait for view "500"
    Then I should see "There are no goals to correspond to this priority combination"

    When I check subfilters "3"
    And I wait for view "500"
    And I check subfilters "4"
    And I wait for view "500"
    Then I should see "There are no goals to correspond to this priority combination "

    When I check subfilters "4"
    And I wait for view "500"
    Then I should see "What are you doing here? Come on, add some goals"

  @javascript @comments
  Scenario: show comments in inner page
    Given I am on "/logout"
    And I wait
    When I am logged in as "user1"
    And I click on "navbar-right"
    And I am on "/profile"
    And I am on "/goal/goal9"
    Then I should see "Comments"
    And should see "Show More"
    And I fill in "commentBody" with "GOOD COMMENT"
    And I wait for view "500"
    Then I should see "Comment5"

