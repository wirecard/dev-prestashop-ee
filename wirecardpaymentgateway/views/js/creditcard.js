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
    PAYMENT_METHODS_RADIO_ID: "input[name=\"payment-option\"]",
    USE_CARD_BUTTON_ID: "#use-new-card",
    DELETE_CARD_BUTTON_ID: "button[data-cardid]",
    STORED_CARD_BUTTON_ID: "#stored-card",
    SAVE_CARD_CHECKMARK_ID: "#wirecard-store-card",
    SELECT_TOKEN_RADIO_ID: "input:radio[name='cc-reuse']",
    CARD_LIST_ID: "#wd-card-list",
    CARD_SPINNER_ID: "#card-spinner",
    NOTIFICATION_ID: "error-notification",
    NOTIFICATIONS_ID: "#notifications",
    ERROR_MESSAGE_GENERIC: "error_message_generic",
    ERROR_WPP: "errorWPP",
    ERROR_ERRORS: "errors",
    ERROR_PREFIX: "error_"
};

var SpinnerState = {
    HIDDEN: "none",
    VISIBLE: "block"
};

jQuery(function () {
    jQuery(document).on("click", Constants.PAYMENT_METHODS_RADIO_ID, function () {
        jQuery(Constants.NOTIFICATIONS_ID).hide();
    });
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
    $document.on("hidden.bs.modal", Constants.MODAL_ID, onModalHide);

    jQuery(Constants.STORED_CARD_BUTTON_ID).hide();
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
    var $container = jQuery("#" + Constants.CONTAINER_ID);

    if ($container.children().length === 0) {
        initializeCreditCardEventHandlers();
        initializeForm();
    }
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
    var $creditCardRadioButton = jQuery(Constants.CREDITCARD_RADIO_ID);

    if (!$creditCardRadioButton.is(":checked")) {
        return;
    }

    event.preventDefault();

    WPP.seamlessSubmit({
        wrappingDivId: Constants.CONTAINER_ID,
        onSuccess: onSeamlessFormSubmit,
        onError: onSeamlessFormError
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

    if (typeof WPP !== "undefined") {
        WPP.seamlessRender({
            requestData: formData,
            wrappingDivId: Constants.CONTAINER_ID,
            onSuccess: onFormRendered,
            onError: onSeamlessFormError
        });
    } else {
        onSeamlessFormError({
            errorWPP: Constants.ERROR_MESSAGE_GENERIC
        });
    }
}

/**
 * Updates the card list for one-click checkout
 *
 * @param cardList
 * @since 2.4.0
 */
function onCardListReceived(cardList)
{
    if (cardList.hasOwnProperty("count")) {
        if (cardList.count > 0) {
            jQuery(Constants.STORED_CARD_BUTTON_ID).show();
        } else {
            jQuery(Constants.STORED_CARD_BUTTON_ID).hide();
            if ($(Constants.MODAL_ID).is(":visible")) {
                onCardSelected();
            }
        }
    }
    jQuery(Constants.CARD_LIST_ID).html(cardList.html);
    var $tokenRadio = jQuery(Constants.SELECT_TOKEN_RADIO_ID);
    $tokenRadio.on("change", onTokenSelected);
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
 * Handles the selection of a card
 *
 * @since 2.9.0
 */
function onTokenSelected()
{
    var $button = jQuery(Constants.USE_CARD_BUTTON_ID);
    $button.prop("disabled", false);
}

/**
 * Reloads the seamless form with a pre-existing token
 *
 * @since 2.4.0
 */
function onCardSelected()
{
    let tokenId = jQuery("#payment-form input[name=cc-reuse]:checked").val();

    jQuery(Constants.MODAL_ID).modal("hide");
    setSpinnerState(SpinnerState.VISIBLE);
    initializeForm(tokenId);
}

/**
 * Get list of cards after hiding the modal
 *
 * @since 2.9.0
 */
function onModalHide()
{
    getCardList();
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
            "cart_id": cartId,
            "token_id": tokenId
        }
    });

    formDataRequest
        .done(onFormDataReceived)
        .fail(onSeamlessFormError);
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
            "token_id": tokenId,
            "masked_pan": maskedPan
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
            "card_id": cardId
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
function onSeamlessFormError(error)
{
    let $form = jQuery(Constants.PAYMENT_FORM_ID);
    let $errorList = [];
    if (error.hasOwnProperty(Constants.ERROR_ERRORS)) {
        error.errors.forEach((responseKey) => {
            $errorList.push(responseKey.error.description);
        });
    }
    if (error.hasOwnProperty(Constants.ERROR_WPP)) {
        $errorList.push(error.errorWPP);
    }
    Object.entries(error).forEach(([responseKey, value]) => {
        if (responseKey.startsWith(Constants.ERROR_PREFIX)) {
            $errorList.push(value);
        }
    });
    let $input = jQuery("<input>").attr({
        type: "hidden",
        value: JSON.stringify($errorList),
        name: Constants.NOTIFICATION_ID,
    });
    $form.append($input);

    submitFormToShop();
}
