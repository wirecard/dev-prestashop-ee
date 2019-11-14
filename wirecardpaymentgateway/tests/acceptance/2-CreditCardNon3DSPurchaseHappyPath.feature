Feature: CreditCardNon3DSPurchaseHappyPath
  As a guest  user
  I want to make a purchase with a Credit Card Non3DS
  And to see that transaction was successful

  Background:
    Given I activate "creditcard" payment action "pay" in configuration
    And I prepare credit card checkout "Non3DS"
    And I am on "Checkout" page
    And I fill fields with "Customer Data"
    When I check "I agree to the terms and conditions and the privacy policy"
    And I click "Next"
    And I fill fields with "Billing Data"
    Then I select "Wirecard Credit Card"

  @ui_test @env ui_test
  Scenario: purchase
    Given I fill fields with "Valid Credit Card Data"
    When I check "I agree to the terms of service"
    And I click "Order with an obligation to pay"
    Then I am redirected to "Order Received" page
    And I see "YOUR ORDER IS CONFIRMED"
    And I see "creditcard" "purchase" in transaction table
