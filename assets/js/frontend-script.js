/*
 * ATTENTION: The "eval" devtool has been used (maybe by default in mode: "development").
 * This devtool is neither made for production nor for readable output files.
 * It uses "eval()" calls to create a separate source file in the browser devtools.
 * If you are trying to read the output file, select a different devtool (https://webpack.js.org/configuration/devtool/)
 * or disable the default devtool with "devtool: false".
 * If you are looking for production-ready output files, see mode: "production" (https://webpack.js.org/configuration/mode/).
 */
/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./src/js/frontend/frontend-script-index.js":
/*!**************************************************!*\
  !*** ./src/js/frontend/frontend-script-index.js ***!
  \**************************************************/
/***/ (() => {

eval("const $ = window.jQuery;\r\n\r\nif (!window.TEAMTALLY) {\r\n    window.TEAMTALLY = {};\r\n}\r\n\r\nwindow.TEAMTALLY.widgetNavigate = widgetNavigate;\r\n\r\n/**\r\n * Used by the team listing widget for pagination\r\n *\r\n * @param widgetId\r\n * @param page\r\n */\r\nfunction widgetNavigate(widgetId, page) {\r\n\r\n    const settings = window.TEAMTALLY &&\r\n        window.TEAMTALLY.SHARED_DATA &&\r\n        window.TEAMTALLY.SHARED_DATA[widgetId];\r\n\r\n    const ajaxurl = window.TEAMTALLY &&\r\n        window.TEAMTALLY.SHARED_DATA &&\r\n        window.TEAMTALLY.SHARED_DATA['ajaxurl'];\r\n\r\n    if (!settings || !ajaxurl) return;\r\n\r\n    const containerId = `team_listing_widget_${widgetId}`;\r\n    const containerEl = document.getElementById(containerId);\r\n    const parentContainerEl = containerEl.parentElement;\r\n\r\n    if (!containerEl || !parentContainerEl) return;\r\n\r\n    const setPendingStatus = (isPending) => {\r\n        const spinnerEl = parentContainerEl.querySelector('.spinner-container');\r\n        if (!spinnerEl) return;\r\n\r\n        if (isPending) {\r\n            spinnerEl.classList.add('visible');\r\n        }\r\n        else {\r\n            spinnerEl.classList.remove('visible');\r\n        }\r\n    }\r\n\r\n    setPendingStatus(true);\r\n\r\n    const $request = $.ajax({\r\n        url: ajaxurl,\r\n        method: 'POST',\r\n        data: {\r\n            action: 'elementor_pre_render_team_listing',\r\n            widget_id: widgetId,\r\n            paged: page,\r\n            settings: settings,\r\n        },\r\n    });\r\n\r\n    $request.done((response) => {\r\n        const html = response.html || '';\r\n\r\n        if (!containerEl) return;\r\n\r\n        containerEl.innerHTML = html;\r\n        setPendingStatus(false);\r\n    });\r\n\r\n}\r\n\r\n\n\n//# sourceURL=webpack://teamtally/./src/js/frontend/frontend-script-index.js?");

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module can't be inlined because the eval devtool is used.
/******/ 	var __webpack_exports__ = {};
/******/ 	__webpack_modules__["./src/js/frontend/frontend-script-index.js"]();
/******/ 	
/******/ })()
;