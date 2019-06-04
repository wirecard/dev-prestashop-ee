Feature: checkCreditCard3DSFunctionalityHappyPath
  As a guest  user
  I want to make a purchase with a Credit Card non 3DS
  And to see that transaction was successful

  Background:
    Given I prepare checkout
    And I am on "Checkout" page
    And I fill fields with "Customer data"
    Then I see "Wirecard Credit Card"

  @env ui_test @ui_test
  Scenario: try purchaseCheck
    Given I fill fields with "Valid Credit Card Data"
    When I check "I agree to the terms of service"
    When I click "Order with an obligation to pay"
    And I see "YOUR ORDER IS CONFIRMED"