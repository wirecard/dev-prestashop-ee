Feature: CreditCard3DSOneClickHappyPath
  As a registered user
  I want to make a one-click checkout with a Credit Card 3DS
  And to see that transaction was successful

  Background:
    Given I prepare credit card checkout "3DS"
    And I am on "Checkout" page
    And I click "Sign in"
    And I fill fields with "Sign in data"
    And I click "Continue2"
    And I click "Continue3"
    Then I select "Wirecard Credit Card"

  @ui_test @env ui_test
  Scenario: one-click
    When I click "Use saved credit card"
    And I click "Use card"
    And I click "Order with an obligation to pay"
    And I am redirected to "Verified" page
    And I enter "wirecard" in field "Password"
    And I click "Continue"
    Then I am redirected to "Order Received" page
    And I see "YOUR ORDER IS CONFIRMED"
    And I see "creditcard" "purchase" in transaction table
