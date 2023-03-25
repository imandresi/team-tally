import {displayNotice, hideNotice} from "./module-custom-admin-notice";

/**
 * Initialize global variables
 */
const elementor = window.elementor;
const ajaxurl = window.ajaxurl;
const $ = window.jQuery;
const $e = window.$e;

/**
 * DATA SENT BY PHP
 *   string widget_name
 *   string nonce
 */

if (!window.TEAMTALLY) {
    window.TEAMTALLY = {
        SHARED_DATA: {}
    };
}

const SHARED_DATA = window.TEAMTALLY.SHARED_DATA;

/**
 * Various constants
 */

const WIDGET_NAME = SHARED_DATA['widget_name'];

if (!window.TEAMTALLY[WIDGET_NAME]) {
    window.TEAMTALLY[WIDGET_NAME] = {};
}

const EVENT_NAME_KEYPRESS_CUSTOM_CSS = `elementor/widget/${WIDGET_NAME}/custom_css/keypress`;

/**
 * Action automatically triggered when the 'WIDGET' panel is activated
 */
elementor.hooks.addAction(`panel/open_editor/widget/${WIDGET_NAME}`, function (panel, model, view) {
    let lastClickedTemplateTabBtn;

    /**
     * HELPER FOR TEMPLATES
     */
    const templatesHelper = {

        /**
         * Template Section is clicked
         *
         * @param sectionInfo
         */
        activate: (sectionInfo) => {

            // disable the select control and adds a spinner if is pending
            const setTemplateSelectPending = (status = true, chosenTemplateView = null) => {

                if (!chosenTemplateView) {
                    chosenTemplateView = getControlView('chosen_template');
                }

                const $selectEl = chosenTemplateView.$el.find('select');

                if (status) {
                    chosenTemplateView.$el.addClass('is-pending');
                    $selectEl.attr('disabled', 'disabled');
                } else {
                    chosenTemplateView.$el.removeClass('is-pending');
                    $selectEl.removeAttr('disabled');
                }
            }

            if (!sectionInfo.isOpened) return;

            // check if 'CHOOSE' tab is clicked
            const chooseTabView = getControlView('use_template_tab');
            if (!chooseTabView) return;

            const chosenTemplateView = getControlView('chosen_template');
            setTemplateSelectPending(false, chosenTemplateView);

            if (!chooseTabView.$el.hasClass('elementor-tab-active')) return;
            setTemplateSelectPending(true, chosenTemplateView);

            const currentlySelectedTemplate = model.getSetting('chosen_template');
            templatesHelper.updateTemplatesList(null);

            // loads list of templates
            const $request = $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {
                    action: 'elementor_get_all_templates',
                    widget_name: WIDGET_NAME,
                },
            });

            $request.done((response) => {

                if (response.success) {
                    templatesHelper.updateTemplatesList(response.templates, currentlySelectedTemplate);
                } else {
                    templatesHelper.showTemplateNotice(
                        'notice-error',
                        'ERROR: Can not load the list of templates'
                    );
                }

                setTemplateSelectPending(false, chosenTemplateView);
            });

        },

        /**
         * Displays a notice inside the TEMPLATE CONTROL TAB
         *
         * @param noticeType
         * @param noticeMsg
         */
        showTemplateNotice: (noticeType, noticeMsg) => {
            if (panel.currentPageView.activeSection !== 'template_section') return;

            const controlView = getControlView('template_admin_notice');
            if (!controlView) return;

            displayNotice(controlView.$el, noticeMsg, noticeType, true);
        },

        /**
         * Updates the list of templates
         *
         * @param array templates
         * @param string newSelectedValue
         * @return void
         */
        updateTemplatesList: (templates, newSelectedValue = '') => {

            if (panel.currentPageView.activeSection !== 'template_section') return;

            const controlView = getControlView('chosen_template');

            if (!controlView) return;

            const controlModel = controlView.model;
            const $control = controlView.$el.find('select');

            // Gets the current selected valueToBeSelected
            const currentSelectedValue = controlView.getControlValue();

            // Clears the select control
            controlModel.set('options', {});
            controlView.setValue('');

            if (!templates || !templates.length) {
                return;
            }

            // set the new select options
            let valueToBeSelected = newSelectedValue ? newSelectedValue : currentSelectedValue;
            let selectOptions = {};

            templates.forEach(template => {
                selectOptions[template] = template;
            });

            controlModel.set('options', selectOptions);

            // set the selected option
            // Checks if the value to be selected exists in the new options
            // - if 'yes' then use that value
            // - if 'no' then use the first value of the option list
            const finalSelectedValue = (!~templates.indexOf(valueToBeSelected)) ? templates[0] : valueToBeSelected;
            controlView.setValue(finalSelectedValue)

            // reflect the changes in the browser
            controlView.render();

        },

        /**
         * Enable or disable editing controls inside the template tab
         *
         * @param boolean enabled
         */
        enableTemplateEditing(enabled = true) {

            if (panel.currentPageView.activeSection !== 'template_section') return;

            const $pendingEl = $('.elementor-control-template_pending');
            if (!$pendingEl.length) return;

            const btnSaveView = getControlView('template_btn_save');
            const $btn = btnSaveView.$el;
            if (!$btn.length) return;

            const pendingElOffset = $pendingEl.offset();
            const btnOffset = $btn.offset();
            const height =
                btnOffset.top -
                pendingElOffset.top +
                $btn.height() +
                parseInt($pendingEl.css('padding-top')) +
                parseInt($pendingEl.css('padding-bottom')) +
                20;

            $pendingEl.css('height', height + 'px');

            if (!enabled) {
                $pendingEl.addClass('is-pending spinner');
            } else {
                $pendingEl.removeClass('is-pending spinner');
            }

        }
    };

    /**
     * Changes the value of a control and the model at the same time
     *
     * This is a sort of hack because model.setSetting(..) or
     * model.attribute.settings.set(..) seems not working because
     * it does not refresh the content of the control. It seems that
     * it only initializes the control value.
     *
     * @param key
     * @param value
     */
    function setControlValue(key, value) {
        const controlView = getControlView(key);
        if (!controlView) return;

        controlView.setValue(value);
        controlView.render();
    }

    /**
     * Digs in the panel and returns the control associated to 'controlName'
     *
     * @param controlName
     * @returns {*}
     */
    function getControlView(controlName) {
        const controlView = panel.currentPageView.getControlViewByName(controlName);

        return controlView;
    }

    /**
     * Scrolls the editor until the control appears
     *
     * @param controlName
     */
    function scrollIntoView(controlName) {
        const controlView = getControlView(controlName);
        if (!controlView) return;

        controlView.el.scrollIntoView(true);
    }

    /**
     * Click event on the panel during the bubbling phase
     *
     * Uses 'event delegation' to handle clicks on elements
     * contained inside the panel.
     *
     * @param e
     */
    function panelOnClick(e) {
        const target = e.target;

        // this will prevent the event to execute for other widget panels
        if ((panel &&
            panel.currentPageView &&
            panel.currentPageView.model &&
            panel.currentPageView.model.get('widgetType')) !== WIDGET_NAME) {
            return;
        }

        /**
         * Checks if the edit button of the template tab is clicked and processes it
         *
         * @param target
         * @returns {boolean} // true if processed
         */
        function editTemplateTab_processClickOn(target) {
            let processed = false;

            if (target.classList.contains('elementor-panel-tab-heading')) {

                const $tabContainerBtn = $(target).closest('.elementor-control-edit_template_tab');

                if ($tabContainerBtn.length > 0) {
                    const activeTabBtn = ($tabContainerBtn[0] === lastClickedTemplateTabBtn);

                    if (!activeTabBtn) {

                        /*
                         * Populate template fields
                         */
                        const template_name = decodeURIComponent(model.getSetting('chosen_template'));

                        // initialize content first
                        setControlValue('template_name', template_name);
                        setControlValue('template_container', '');
                        setControlValue('template_item', '');

                        if (!template_name) {
                            return;
                        }

                        templatesHelper.enableTemplateEditing(false);

                        // send ajax request to the server
                        const $request = $.ajax({
                            url: ajaxurl,
                            method: 'POST',
                            data: {
                                action: 'elementor_get_template',
                                widget_name: WIDGET_NAME,
                                template_name: template_name
                            },
                        });

                        $request.done(function (response) {

                            // get ajax request and populate the fields with data
                            if (response.success) {
                                setControlValue('template_name', template_name);
                                setControlValue('template_container', response.container);
                                setControlValue('template_item', response.item);
                            } else {
                                const message = response.message;
                                templatesHelper.showTemplateNotice(
                                    'notice-error',
                                    message
                                );

                                setControlValue('template_name', '');
                                setControlValue('template_container', '');
                                setControlValue('template_item', '');
                            }

                            templatesHelper.enableTemplateEditing(true);
                        });

                        processed = true;
                    }
                }

                lastClickedTemplateTabBtn = $tabContainerBtn[0];

            }

            return processed;
        }

        /**
         * Checks if the save template button is clicked and processes it
         *
         * @returns {boolean}
         */
        function editTemplateTab_processClickOnSaveBtn() {
            let process = false;

            if ((target.classList.contains('elementor-button')) &&
                ($(target).closest('.elementor-control-template_btn_save').length > 0)) {
                const template_name = model.getSetting('template_name');
                const template_container = model.getSetting('template_container');
                const template_item = model.getSetting('template_item');
                const nonce = SHARED_DATA.nonce;

                templatesHelper.enableTemplateEditing(false);

                const $request = $.ajax({
                    url: ajaxurl,
                    method: 'POST',
                    data: {
                        action: 'elementor_update_template',
                        widget_name: WIDGET_NAME,
                        template_name: template_name,
                        template_container: template_container,
                        template_item: template_item,
                        _nonce: nonce,
                    },
                });

                $request.done(function (response) {

                        let noticeType;
                        let noticeMsg = response.message;

                        SHARED_DATA.nonce = response._nonce;

                        if (response.success) {
                            noticeType = 'notice-success';
                            templatesHelper.updateTemplatesList(response.templates);
                        } else {
                            noticeType = 'notice-error';
                        }

                        templatesHelper.showTemplateNotice(noticeType, noticeMsg);
                        templatesHelper.enableTemplateEditing(true);

                        scrollIntoView('template_section');

                    }
                );

                process = true;
            }

            return process;
        }

        /**
         * Checks if the remove template button is clicked and then processes it
         *
         * @returns {boolean}
         */
        function editTemplateTab_processClickOnRemoveBtn() {
            let process = false;
            if ((target.classList.contains('elementor-button')) &&
                ($(target).closest('.elementor-control-template_btn_remove').length > 0)) {
                const template_name = model.getSetting('template_name');
                const nonce = SHARED_DATA.nonce;

                templatesHelper.enableTemplateEditing(false);

                const $request = $.ajax({
                    url: ajaxurl,
                    method: 'POST',
                    data: {
                        action: 'elementor_delete_template',
                        widget_name: WIDGET_NAME,
                        template_name: template_name,
                        _nonce: nonce,
                    },
                });

                // executed if the ajax request is ok
                $request.done(function (response) {
                    let noticeType, nonce;
                    let noticeMsg = response.message;

                    SHARED_DATA.nonce = response._nonce;

                    if (response.success) {
                        noticeType = 'notice-success';
                        templatesHelper.updateTemplatesList(response.templates);
                    } else {
                        noticeType = 'notice-error';
                    }

                    templatesHelper.showTemplateNotice(
                        noticeType,
                        noticeMsg
                    );

                    // reset all values
                    setControlValue('template_name', '');
                    setControlValue('template_container', '');
                    setControlValue('template_item', '');

                    templatesHelper.enableTemplateEditing(true);

                    // go back to the 'choose template' tab
                    // simulates a click event
                    const controlView = getControlView('use_template_tab');
                    const $controlTarget = controlView.$el.find('.elementor-panel-tab-heading');
                    if ($controlTarget.length) {
                        controlView.$el.trigger('click');
                        editTemplateTab_processClickOn($controlTarget[0]);
                    }

                    scrollIntoView('template_section');

                });

                process = true;
            }

            return process;

        }

        /**
         * main operations for 'panelOnClick()'
         */

        // Is the EDIT BUTTON on the template tab clicked ?
        if (editTemplateTab_processClickOn(target)) {
            return;
        }

        // TEMPLATE - button save
        if (editTemplateTab_processClickOnSaveBtn()) {
            return;
        }

        // TEMPLATE - button remove
        if (editTemplateTab_processClickOnRemoveBtn()) {
            return;
        }

    }

    /**
     * onClick event handler on the panel during the capture phase
     *
     * @param e
     */
    function panelCaptureOnClick(e) {

        const target = e.target;

        // this will prevent the event to execute for other widget panels
        if ((panel &&
            panel.currentPageView &&
            panel.currentPageView.model &&
            panel.currentPageView.model.get('widgetType')) !== WIDGET_NAME) {
            return;
        }

        const $target = $(target);
        if (!$target.hasClass('elementor-section-title')) {
            return false;
        }

        const $sectionTitleContainer = $target.closest('.elementor-control-type-section');
        const sectionTitleClasses = Array.from($sectionTitleContainer[0].classList);
        const sectionName = sectionTitleClasses.reduce((newValue, value) => {
            const exclude = [
                'elementor-control-type-section',
                'elementor-control-separator-none',
                'elementor-control'
            ];

            // value found - check if it is a section id
            if (!~exclude.indexOf(value)) {
                const re = /^elementor\-control\-(.+)/i;
                const matches = value.match(re);

                if (matches) {
                    const sectionName = matches[1];
                    const section = panel.currentPageView.options.controls[sectionName];
                    if (section && section.type === 'section') {
                        newValue = sectionName;
                    }
                }
            }

            return newValue;

        }, '');

        // Section is clicked - call callback
        if (sectionName) {
            panelControlSectionClicked({
                el: $sectionTitleContainer[0],
                sectionName: sectionName,
                isOpened: !$sectionTitleContainer.hasClass('elementor-open'),
            });

        }

    }

    /**
     * Callback fired when a section of the widget panel is clicked
     *
     * sectionInfo = {
     *     el: DOM Element of the section container,
     *     sectionName: ...,
     *     isOpened: ...
     * }
     *
     * @param sectionInfo
     */
    function panelControlSectionClicked(sectionInfo) {

        switch (sectionInfo.sectionName) {

            case 'template_section':
                // as we are still in the capture event, we have to
                // wait a little in order for elementor to process
                // correctly all its internal routines in the bubbling
                // phase before excuting ours.
                // TODO: look for better solutions
                setTimeout(() => {
                    templatesHelper.activate(sectionInfo);
                }, 500);
                break;

        }

    }

    /**
     * Initialize all events
     */
    function setPanelEvents() {

        // This will prevent multiple executions
        if (window.TEAMTALLY[WIDGET_NAME].isRunning) {
            return;
        }

        window.TEAMTALLY[WIDGET_NAME].isRunning = true;

        // set click event handler on the widget panel - useCapture: false
        panel.el.addEventListener('click', panelOnClick, false);

        // set click event handler on the widget panel - useCapture: true
        // used to detect if a section is clicked or not
        panel.el.addEventListener('click', panelCaptureOnClick, true);

    }

    // console.log('panel', panel);
    // console.log('model', model);
    // console.log('view', view);

    /**
     * Initialization part
     */

    setPanelEvents();

});




