Feature: CreditCard3DSOneClickHappyPath
  As a registered user
  I want to make a one-click checkout with a Credit Card 3DS
  And to see that transaction was successful

  Background:
    Given I activate "creditcard" payment action "pay" in configuration
    And I prepare credit card checkout "3DS"
    And I am on "Checkout" page

  @ui_test @env ui_test
  Scenario: prepare-one-click-card
    Given I fill fields with "Customer Data With Password"
    And I check "I agree to the terms and conditions and the privacy policy"
    And I click "Next"
    And I fill fields with "Billing Data"
    When I select "Wirecard Credit Card"
    And I fill fields with "Valid Credit Card Data"
    And I check "Save For Later Use"
    And I check "I agree to the terms of service"
    And I click "Order with an obligation to pay"
    Then I am redirected to "Verified" page
    And I enter "wirecard" in field "Password"
    And I click "Continue"
    Then I am redirected to "Order Received" page
    And I see "YOUR ORDER IS CONFIRMED"
    And I see "creditcard" "purchase" in transaction table

  @ui_test @major @minor @patch @env ui_test
  Scenario: on-click-purchase
    Given I click "Sign in"
    And I fill fields with "Sign in data"
    And I click "Continue2"
    And I click "Continue3"
    When I select "Wirecard Credit Card"
    And I click "Use saved credit card"
    And I click "Use card"
    And I check "I agree to the terms of service"
    And I click "Order with an obligation to pay"
    And I am redirected to "Verified" page
    And I enter "wirecard" in field "Password"
    And I click "Continue"
    Then I am redirected to "Order Received" page
    And I see "YOUR ORDER IS CONFIRMED"
    And I see "creditcard" "purchase" in transaction table
