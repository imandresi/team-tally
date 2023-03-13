import {displayNotice, hideNotice} from "./module-custom-admin-notice";

/**
 * Initialize global variables
 */
const elementor = window.elementor;
const $ = window.jQuery;
const ajaxurl = window.ajaxurl;
const $e = window.$e;

/**
 * Action automatically triggered when the 'team_listing_widget' panel is activated
 */
elementor.hooks.addAction('panel/open_editor/widget/team_listing_widget', function (panel, model, view) {
    let lastClickedTemplateTabBtn;

    /**
     * HELPER FOR TEMPLATES
     */
    const templatesHelper = {

        /**
         * Displays a notice inside the TEMPLATE CONTROL TAB
         *
         * @param noticeType
         * @param noticeMsg
         */
        showTemplateNotice: (noticeType, noticeMsg) => {
            const controlView = getControlView('template_admin_notice');
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

            const controlView = getControlView('chosen_template');
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
            const $pendingEl = $('.elementor-control-template_pending');
            if (!$pendingEl.length) {
                return;
            }

            const btnSaveView = getControlView('template_btn_save');
            const $btn = btnSaveView.$el;
            if (!$btn.length) {
                return;
            }

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

    function createPreviewButton() {
        const panel = document.querySelector('#elementor-panel');
        const previewBtn = document.createElement('div');
        previewBtn.setAttribute('class', 'team-preview-button');
        previewBtn.innerHTML = `<button class="elementor-button elementor-button-default" type="button">PREVIEW CHANGES</button>`;

        const panelContainer = panel.querySelector("#elementor-panel-page-editor");
        panelContainer.prepend(previewBtn);
        panelContainer.appendChild(previewBtn.cloneNode(true));

    }

    function setPanelNavigationEvent() {
        const panel = document.querySelector('#elementor-panel');
        const panelNavigationEl = panel.querySelector(".elementor-panel-navigation");
        const panelNavigationEvent = e => {
            const target = e.target.parentElement;

            if ((target.classList.contains('elementor-component-tab')) &&
                (!target.classList.contains('elementor-active'))) {
                setPanelNavigationEvent();
                createPreviewButton();
            }
        };

        panelNavigationEl.addEventListener('click', panelNavigationEvent);
    }

    /**
     * Click event on the panel
     *
     * Uses 'event delegation' to handle clicks on elements
     * contained inside the panel.
     *
     * @param e
     */
    function panelOnClick(e) {
        const target = e.target;

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
                                action: 'elementor_team_listing_get_template',
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
                const template_nonce = model.getSetting('template_nonce');

                templatesHelper.enableTemplateEditing(false);

                const $request = $.ajax({
                    url: ajaxurl,
                    method: 'POST',
                    data: {
                        action: 'elementor_team_listing_update_template',
                        template_name: template_name,
                        template_container: template_container,
                        template_item: template_item,
                        template_nonce: template_nonce,
                    },
                });

                $request.done(function (response) {

                        let noticeType;
                        let noticeMsg = response.message;

                        if (response.success) {
                            noticeType = 'notice-success';

                            templatesHelper.updateTemplatesList(response.templates);
                            setControlValue('template_nonce', response.template_nonce);
                        } else {
                            noticeType = 'notice-error';
                        }

                        templatesHelper.showTemplateNotice(noticeType, noticeMsg);

                        templatesHelper.enableTemplateEditing(true);
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
                const template_nonce = model.getSetting('template_nonce');

                templatesHelper.enableTemplateEditing(false);

                const $request = $.ajax({
                    url: ajaxurl,
                    method: 'POST',
                    data: {
                        action: 'elementor_team_listing_delete_template',
                        template_name: template_name,
                        template_nonce: template_nonce,
                    },
                });

                // executed if the ajax request is ok
                $request.done(function (response) {
                    let noticeType, nonce;
                    let noticeMsg = response.message;

                    if (response.success) {
                        noticeType = 'notice-success';

                        templatesHelper.updateTemplatesList(response.templates);
                        setControlValue('template_nonce', response.template_nonce);

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

    // console.log('panel', panel);
    // console.log('model', model);
    // console.log('view', view);

    /**
     * Initialization part
     */
    setPanelNavigationEvent();
    createPreviewButton();

    // set click event handler on the widget panel
    panel.content.$el.on('click', panelOnClick);

});






