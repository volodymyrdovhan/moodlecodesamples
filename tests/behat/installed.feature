@local @local_intellicart
Feature: Installation succeeds
  In order to use this plugin
  As a user
  I need the installation to work

  Background:
    Given I log in as "admin"
    And I am on site homepage

  @javascript
  Scenario: Check the Plugins overview for the name of this plugin
    When I navigate to "Plugins > Plugins overview" in site administration
    Then the following should exist in the "plugins-control-panel" table:
      |IntelliCart|
      |local_intellicart|
      |IntelliCart enrollments|
      |enrol_intellicart|
      |Products Catalog|
      |block_products_catalog|

  @javascript
  Scenario: Check the plugin enabled and working
    Given the following config values are set as admin:
      | enabled | 1 | local_intellicart |
      | license_email | behat@intelliboard.net | local_intellicart |
      | license_apikey | behat-key | local_intellicart |
    When I click on "IntelliCart" "link"
    Then I should not see "IntelliCart License is not valid"
    Then I should see "Products"
    Then I should see "IntelliBoard"