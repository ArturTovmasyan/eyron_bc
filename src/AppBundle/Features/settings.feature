Feature: Settings
  In order to change my info
  As a user2
  I need to be able to open the page and change the info I want

  Background:
    Given I am on "/logout"
    And I wait for angular
    And I am logged in as "user2"

  @javascript @settings
  Scenario: Open pop up and show me the info I filled in registration
    When I click on "navbar-right"
    And I am on "/edit/profile"
    Then I should see "user2@user.com"
    And I wait
    And I fill in the following:
      | bl_user_settings_firstName | userTest |
      | bl_user_settings_lastName | userTest |
      | bl_user_settings_addEmail | test8@test.am |
      | bl_user_settings_currentPassword | Test1234 |
      | bl_user_settings_plainPassword_first | test1234 |
      | bl_user_settings_plainPassword_second | test1234 |
    And I select date fields
    And I select language
    And I press "Save"
    And I click on "navbar-right"
    And I am on "/edit/profile"
    And I fill in "bl_user_settings_firstName" with "userTo"
    And I fill in "bl_user_settings_lastName" with "useryan"
    And I fill in "bl_user_settings_currentPassword" with "test1234"
    And I fill in "bl_user_settings_plainPassword_first" with "Test1234"
    And I fill in "bl_user_settings_plainPassword_second" with "Test1234"
    And I select language
    And I press "Сохранить"
    And I wait for view "1500"
    Then I should see "Primary Email"
    When I follow "Notification"
    Then I should see "What you will receive"
    When I change switch "0"
    And I change switch "1"
    And I press "Save"
    And I wait for view "500"
    And I change switch "1"
    And I press "Save"
    And I wait for view "500"




