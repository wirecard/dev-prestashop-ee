/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

// Declaring global variables for ESLint and allowing console.error statements

/* global cartId, WPP, ccControllerUrl, ccVaultEnabled */
/* eslint no-console: ["error", {allow: ["error"]}] */

var Constants = {
    IFRAME_HEIGHT_DESKTOP: 410,
    IFRAME_HEIGHT_MOBILE: 390,
    IFRAME_HEIGHT_CUTOFF: 992,

    MODAL_ID: "#wirecard-ccvault-modal",
    IFRAME_ID: "#wirecard-integrated-payment-page-frame",
    CONTAINER_ID: "payment-processing-gateway-credit-card-form",
    PAYMENT_FORM_ID: "form[action*=\"creditcard\"]",
    CREDITCARD_RADIO_ID: "input[name=\"payment-option\"][data-module-name=\"wd-creditcard\"]",
    USE_CARD_BUTTON_ID: "button[data-tokenid]",
    DELETE_CARD_BUTTON_ID: "button[data-cardid]",
    STORED_CARD_BUTTON_ID: "#stored-card",
    SAVE_CARD_CHECKMARK_ID: "#wirecard-store-card",
    CARD_LIST_ID: "#wd-card-list",
    CARD_SPINNER_ID: "#card-spinner"
};

var SpinnerState = {
    HIDDEN: "none",
    VISIBLE: "block"
};

jQuery(function () {
    jQuery(document).on("click", Constants.CREDITCARD_RADIO_ID, onPaymentMethodSelected);
});

/*
 * Section: Initializers
 */

/**
 * Initializes all event handlers for the interface
 *
 * @since 2.4.0
 */
function initializeCreditCardEventHandlers()
{
    var $document = jQuery(document);

    $document.on("click", Constants.DELETE_CARD_BUTTON_ID, onCardDeletion);
    $document.on("click", Constants.USE_CARD_BUTTON_ID, onCardSelected);
    $document.on("submit", Constants.PAYMENT_FORM_ID, onPaymentFormSubmit);
}

/**
 * Loads the card list for one-click and renders the seamless form
 *
 * @param tokenId
 * @since 2.4.0
 */
function initializeForm(tokenId = null)
{
    getCardList();
    getFormData(tokenId);
}

/*
 * Section: Business logic functions
 */

/**
 * Initializes the form once credit card is selected
 *
 * @since 2.4.0
 */
function onPaymentMethodSelected()
{
    initializeCreditCardEventHandlers();
    initializeForm();
}

/**
 * Handles the seamless response and formats it so we can handle it in the
 * backend of our shop
 *
 * @param data
 * @since 2.4.0
 */
function onSeamlessFormSubmit(data)
{
    var $form = jQuery(Constants.PAYMENT_FORM_ID);
    var $checkmark = jQuery(Constants.SAVE_CARD_CHECKMARK_ID);
    var shouldSaveCard = $checkmark.prop("checked");

    attachFormField($form, "jsresponse", "true");
    attachFormFields($form, data);

    if (
        shouldSaveCard
        && data.hasOwnProperty("token_id")
        && data.hasOwnProperty("masked_account_number")
    ) {
        saveCardAndSubmitToShop(data["token_id"], data["masked_account_number"]);
        return;
    }

    submitFormToShop();
}

/**
 * Intercepts the submission of the shop payment form to submit our seamless form
 *
 * @param event
 * @since 2.4.0
 */
function onPaymentFormSubmit(event)
{
    event.preventDefault();

    WPP.seamlessSubmit({
        wrappingDivId: Constants.CONTAINER_ID,
        onSuccess: onSeamlessFormSubmit,
        onError: onFormError
    });
}

/**
 * Handles the cleanup after the seamless form has been rendered
 *
 * @since 2.4.0
 */
function onFormRendered()
{
    setSpinnerState(SpinnerState.HIDDEN);
    setIframeSize();
}

/**
 * Takes the transaction built in the backend and renders the seamless form
 *
 * @param formData
 * @since 2.4.0
 */
function onFormDataReceived(formData)
{
    var $form = jQuery(Constants.PAYMENT_FORM_ID);

    attachFormField($form, "cart_id", formData.field_value_1);

    WPP.seamlessRender({
        requestData: formData,
        wrappingDivId: Constants.CONTAINER_ID,
        onSuccess: onFormRendered,
        onError: onFormError
    });
}

/**
 * Updates the card list for one-click checkout
 *
 * @param cardList
 * @since 2.4.0
 */
function onCardListReceived(cardList)
{
    jQuery(Constants.CARD_LIST_ID).html(cardList.html);
}

/**
 * Handles the deletion of a card
 *
 * @since 2.4.0
 */
function onCardDeletion()
{
    var $button = jQuery(this);
    var cardId = $button.data("cardid");

    deleteCard(cardId);
}

/**
 * Reloads the seamless form with a pre-existing token
 *
 * @since 2.4.0
 */
function onCardSelected()
{
    var $button = jQuery(this);
    var tokenId = $button.data("tokenid");

    jQuery(Constants.MODAL_ID).modal("hide");
    setSpinnerState(SpinnerState.VISIBLE);
    initializeForm(tokenId);
}

/*
 * Section: AJAX requests
 */

/**
 * Loads the necessary data for the seamless credit card form
 *
 * @param tokenId
 * @since 2.4.0
 */
function getFormData(tokenId = null)
{
    var formDataRequest = jQuery.ajax({
        url: ccControllerUrl,
        dataType: "json",
        data: {
            action: "getSeamlessConfig",
            cartId: cartId,
            tokenId: tokenId
        }
    });

    formDataRequest
        .done(onFormDataReceived)
        .fail(onFormError);
}

/**
 * Gets all available cards for this customer
 *
 * @since 2.4.0
 */
function getCardList()
{
    if (!ccVaultEnabled) {
        return;
    }

    var cardListRequest = jQuery.ajax({
        url: ccControllerUrl,
        data: {
            action: "listStoredCards"
        }
    });

    cardListRequest
        .done(onCardListReceived)
        .fail(onError);
}

/**
 * Saves the card and submits the payment form to the shop
 *
 * @param tokenId
 * @param maskedPan
 * @since 2.4.0
 */
function saveCardAndSubmitToShop(tokenId, maskedPan)
{
    var cardSavingRequest = jQuery.ajax({
        url: ccControllerUrl,
        data: {
            action: "saveCard",
            tokenId: tokenId,
            maskedPan: maskedPan
        }
    });

    cardSavingRequest
        .done(submitFormToShop)
        .fail(onError);
}

/**
 * Deletes the saved card from the backend
 *
 * @param cardId
 * @since 2.4.0
 */
function deleteCard(cardId)
{
    var cardDeletionRequest =  jQuery.ajax({
        url: ccControllerUrl,
        data: {
            action: "deleteCard",
            cardId: cardId
        }
    });

    cardDeletionRequest
        .done(onCardListReceived)
        .fail(onError);
}

/*
 * Section: DOM manipulation functions
 */

/**
 * Sets the loading spinner visible or hidden
 *
 * @param state
 * @since 2.4.0
 */
function setSpinnerState(state)
{
    var $button = jQuery(Constants.STORED_CARD_BUTTON_ID);
    var $container = jQuery("#" + Constants.CONTAINER_ID);

    jQuery(Constants.CARD_SPINNER_ID).css("display", state);

    if (SpinnerState.HIDDEN === state) {
        $button.removeAttr("disabled");
        $container.css("display", "block");

        return;
    }

    $button.attr("disabled", "disabled");
    $container.css("display", "none");
}

/**
 * Add a hidden form field to the given form element
 *
 * @param $form
 * @param name
 * @param value
 * @since 2.4.0
 */
function attachFormField($form, name, value)
{
    var $input = jQuery("<input>").attr({
        type: "hidden",
        value: value,
        name: name,
    });

    $form.append($input);
}

/**
 * Attaches all fields in data to a given form element
 *
 * @param $form
 * @param data
 * @since 2.4.0
 */
function attachFormFields($form, data)
{
    for (var prop in data) {
        if (data.hasOwnProperty(prop)) {
            attachFormField($form, prop, data[prop.toString()]);
        }
    }
}

/**
 * Sets the iframe size appropriate for its contents
 *
 * @since 2.4.0
 */
function setIframeSize()
{
    var $iframe = jQuery(Constants.IFRAME_ID);
    var $window = jQuery(window);

    if ($window.width() < Constants.IFRAME_HEIGHT_CUTOFF) {
        $iframe.height(Constants.IFRAME_HEIGHT_MOBILE);
    }

    $iframe.height(Constants.IFRAME_HEIGHT_DESKTOP);
}

/**
 * Submits the payment form to the shop
 *
 * @since 2.4.0
 */
function submitFormToShop()
{
    var $document = jQuery(document);
    var $form = jQuery(Constants.PAYMENT_FORM_ID);

    $document.off("submit", Constants.PAYMENT_FORM_ID);
    $form.submit();
}


/*
 * Section: Error handlers
 */

/**
 * Error handling for generic AJAX requests
 *
 * @param error
 * @since 2.4.0
 */
function onError(error)
{
    console.error("Run-time error:", error.responseText);
}

/**
 * Error handling for WPP errors
 *
 * @param error
 * @since 2.4.0
 */
function onFormError(error)
{
    console.error("Form error:", error);

    initializeForm();
}
