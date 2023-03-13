const $ = window.jQuery;

// the following functions can be applied on jQuery element
// as well as on DOM element notice containers

/**
 * Shows the notice message
 *
 * @param el
 * @param msg
 * @param msgType
 * @param isDismissible
 * @param hideAfter
 */
export function displayNotice(
    el, msg, msgType = 'notice-success', isDismissible = false, hideAfter = 5000) {

    let $el = el;

    // convert to jQuery object if necessary
    if (el.nodeType) {
        $el = $(el);
    }

    // is jQuery object ?
    if ($.type($el) !== 'object') {
        return;
    }

    // is the container a message container ?
    if (!$el.hasClass('notice')) {
        $el = $el.find('.notice');
        if (!$el.length) {
            return;
        }
    }

    // Does it a have a message zone ?
    const $msgEl = $el.find('p');
    if (!$msgEl.length) {
        return;
    }

    $msgEl.text(msg);

    // Dismissible ?
    if (isDismissible) {
        $el.addClass('is-dismissible');
    } else {
        $el.removeClass('is-dismissible');
    }

    // set message type
    $el.removeClass('notice-success notice-error');
    $el.addClass(msgType);

    // show notice
    setVisibility($el, true);

    // handle the notice auto hiding
    if (hideAfter) {
        setTimeout(() => {
            setVisibility($el, false);
        }, hideAfter);
    }

}

/**
 * Hides the notice message
 *
 * @param el
 */
export function hideNotice(el) {
    setVisibility(el, false);
}

/**
 * Shows or hides the notice message
 *
 * @param el
 * @param isVisible
 */
function setVisibility(el, isVisible = true) {

    let $el = el;

    // convert to jQuery object if necessary
    if (el.nodeType) {
        $el = $(el);
    }

    // is jQuery object ?
    if ($.type($el) !== 'object') {
        return;
    }

    // not a notice container ?
    if (!$el.hasClass('notice')) {
        $el = $el.find('.notice');
        if (!$el.length) {
            return;
        }
    }

    const isCurrentlyVisible = !$el.hasClass('is-hidden');

    // abort if on same visibility
    // i.e. notice visible but we ask it to be visible
    // i.e. notice hidden but we ask it to be hidden
    if (isVisible === isCurrentlyVisible) {
        return;
    }

    // hide
    if (!isVisible) {
        $el.addClass('is-hidden');
        $el.fadeOut(2000, function() {
            $el.hide();
        });

    }

    // show
    else {
        $el = $(el).removeClass('is-hidden');
        $el.hide();
        $el.fadeIn(2000);
    }

}