Feature: PayPalPurchaseHappyPath
  As a guest user
  I want to make a purchase with a Pay Pal
  And to see that transaction was successful

  Background:
    Given I activate "paypal" payment action "pay" in configuration
    And I prepare checkout
    And I am on "Checkout" page
    And I fill fields with "Customer data"
    When I check "I agree to the terms and conditions and the privacy policy"
    And I click "Next"
    And I fill fields with "Billing Data"
    Then I select "Wirecard PayPal"

  @env ui_test @patch @minor @major
  Scenario: purchase
    Given I check "I agree to the terms of service"
    And I click "Order with an obligation to pay"
    And I am redirected to "Pay Pal Log In" page
    And I login to Paypal
    When I am redirected to "Pay Pal Review" page
    And I click "Accept Cookies"
    Then I click "Continue"
    And I click pay now button
    Then I am redirected to "Order Received" page
    And I see "YOUR ORDER IS CONFIRMED"
    And I see "paypal" "debit" in transaction table
