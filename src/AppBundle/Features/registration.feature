Feature: Registration
  In order to use register user
  As an anonymous user
  I need to be able to register user

  @javascript @register
  Scenario: Registration
    Given I am on "/register/"
    When I fill in the following:
      | fos_user_registration_form[firstName]             | Art |
      | fos_user_registration_form[lastName]              | Tovmasyan |
      | fos_user_registration_form[email]                 | test6@test.am |
      | fos_user_registration_form[plainPassword][first]  | test1234 |
      | fos_user_registration_form[plainPassword][second] | test1234 |
    And I select date fields
    And I press "SIGN UP"
    And I wait for view "2000"
    Then I should be on "/ideas"
    And I should see "Art"

