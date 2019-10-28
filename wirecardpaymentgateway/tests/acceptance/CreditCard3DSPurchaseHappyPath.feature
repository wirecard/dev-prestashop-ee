Feature: CreditCard3DSPurchaseHappyPath
  As a guest  user
  I want to make a purchase with a Credit Card 3DS
  And to see that transaction was successful

  Background:
    Given I activate "creditcard" payment action "pay" in configuration
    And I prepare credit card checkout "3DS"
    And I am on "Checkout" page
    And I fill fields with "Customer Data"
    When I check "I agree to the terms and conditions and the privacy policy"
    And I click "Next"
    And I fill fields with "Billing Data"
    Then I select "Wirecard Credit Card"

  @env ui_test @patch @minor @major
  Scenario: purchase
    Given I fill fields with "Valid Credit Card Data"
    When I check "I agree to the terms of service"
    And I click "Order with an obligation to pay"
    And I am redirected to "Verified" page
    And I enter "wirecard" in field "Password"
    And I click "Continue"
    Then I am redirected to "Order Received" page
    And I see "YOUR ORDER IS CONFIRMED"
    And I see "creditcard" "purchase" in transaction table
