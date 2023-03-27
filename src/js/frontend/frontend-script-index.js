const $ = window.jQuery;

if (!window.TEAMTALLY) {
    window.TEAMTALLY = {};
}

window.TEAMTALLY.widgetNavigate = widgetNavigate;

/**
 * Used by the team listing widget for pagination
 *
 * @param widgetId
 * @param page
 */
function widgetNavigate(widgetId, page) {

    const settings = window.TEAMTALLY &&
        window.TEAMTALLY.SHARED_DATA &&
        window.TEAMTALLY.SHARED_DATA[widgetId];

    const ajaxurl = window.TEAMTALLY &&
        window.TEAMTALLY.SHARED_DATA &&
        window.TEAMTALLY.SHARED_DATA['ajaxurl'];

    if (!settings || !ajaxurl) return;

    const containerId = `team_listing_widget_${widgetId}`;
    const containerEl = document.getElementById(containerId);
    const parentContainerEl = containerEl.parentElement;

    if (!containerEl || !parentContainerEl) return;

    const setPendingStatus = (isPending) => {
        const spinnerEl = parentContainerEl.querySelector('.spinner-container');
        if (!spinnerEl) return;

        if (isPending) {
            spinnerEl.classList.add('visible');
        }
        else {
            spinnerEl.classList.remove('visible');
        }
    }

    setPendingStatus(true);

    const $request = $.ajax({
        url: ajaxurl,
        method: 'POST',
        data: {
            action: 'elementor_pre_render_team_listing',
            widget_id: widgetId,
            paged: page,
            settings: settings,
        },
    });

    $request.done((response) => {
        const html = response.html || '';

        if (!containerEl) return;

        containerEl.innerHTML = html;
        setPendingStatus(false);
    });

}

/**
 * Back button implementation
 */
jQuery(document).ready(function() {
    jQuery('#btn-back').on('click', function() {
        window.history.go(-1);
        return false;
    });
});