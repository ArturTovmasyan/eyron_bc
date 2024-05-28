Feature: Idea
  In order to use Idea page
  As a anonymous
  I need to be able to see ideas page and search

  @javascript @ideas
  Scenario: Open Ideas page and show list of ideas
    Given I am on "/ideas"
    And I wait for view "1000"
    Then I should see "Explore thousands of great ideas for your Bucket List"
    And I should not see "Sorry, we couldn't find anything, but you can explore other ideas:"
    And I should see "Listed by"
    And I should see "Completed by"
    When I follow "Map"
    And I wait for angular
    And I follow "Map"
    Then I am on "/ideas/most-popular"
    And I wait for view "500"
    Then I should see "Most Popular"
    And I wait
    And I should see "Listed by"
    And I should see "Completed by"

  @javascript @linkInIdeasPage
  Scenario: Open ideas page and check Add, Done, Share links
    Given I am on "/logout"
    And I wait
    And I am on "/ideas"
    And I wait for angular
    And I should see "Explore thousands of great ideas for your Bucket List"
    And I click button "add"
    And I wait for view "500"
    Then I should see "SIGN IN"
    And I click on close "close-icon"
    And I wait for angular
    When I follow "JOIN"
    And I wait for angular
    And I click on close "close-icon"
    And I wait for angular
    And I click button "done"
    And I wait for view "500"
    Then I should see "SIGN IN"
