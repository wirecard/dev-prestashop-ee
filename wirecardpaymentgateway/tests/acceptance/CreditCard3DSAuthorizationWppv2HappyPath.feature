Feature: CreditCard3DSAuthorizeHappyPath
  As a guest  user
  I want to make a authorization with a Credit Card 3DS
  And to see that authorization was successful

  Background:
    Given I activate payment action "reserve" in configuration
    And I prepare checkout
    And I am on "Checkout" page
    And I fill fields with "Customer Data"
    When I check "I agree to the terms and conditions and the privacy policy"
    And I click "Continue To Billing Data"
    And I fill fields with "Valid Billing Data"
    Then I see "Wirecard Credit Card"

  @env ui_test @ui_test
  Scenario: authorize
    Given I fill fields with "Valid Credit Card Data"
    When I check "I agree to the terms of service"
    And I click "Order with an obligation to pay"
    And I am redirected to "Verified" page
    And I enter "wirecard" in field "Password"
    And I click "Continue"
    Then I am redirected to "Order Received" page
    And I see "YOUR ORDER IS CONFIRMED"
    And I see "authorization" in transaction table
