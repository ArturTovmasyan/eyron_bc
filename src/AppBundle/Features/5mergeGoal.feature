Feature: Merge Goal
  In order to merge goal
  As a admin user
  I need to be able to open goal merge page and merge goals

  Background:
    Given I am on "/logout"
    And I wait for angular
    And I am logged in as "admin"

  @javascript @mergeGoal
  Scenario: Open dashboard and merge goal it in
    Given I am on "/admin/admin-goal/list"
    And I wait for view "500"
    And I scroll page to ".btn btn-small btn-primary"
    When I follow merge goal
    And I follow "Select goal"
    And I click on select2 field
    And I wait for view "2000"
    And I press "submit"
    And I wait for view "1000"
    Then I should be on "/admin/admin-goal/list#"
    And I should see "has been success merged with"
