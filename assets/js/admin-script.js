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

/***/ "./src/js/admin/admin-script-index.js":
/*!********************************************!*\
  !*** ./src/js/admin/admin-script-index.js ***!
  \********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _module_teamtally_leagues__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./module-teamtally-leagues */ \"./src/js/admin/module-teamtally-leagues.js\");\n/* harmony import */ var _module_teamtally_teams__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./module-teamtally-teams */ \"./src/js/admin/module-teamtally-teams.js\");\n/* harmony import */ var _module_teamtally_teams__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_module_teamtally_teams__WEBPACK_IMPORTED_MODULE_1__);\n\r\n\r\n\r\nwindow.TEAMTALLY = {\r\n    leagues: _module_teamtally_leagues__WEBPACK_IMPORTED_MODULE_0__[\"default\"],\r\n};\r\n\r\n\r\n\r\n\r\n\n\n//# sourceURL=webpack://teamtally/./src/js/admin/admin-script-index.js?");

/***/ }),

/***/ "./src/js/admin/module-teamtally-leagues.js":
/*!**************************************************!*\
  !*** ./src/js/admin/module-teamtally-leagues.js ***!
  \**************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (/* binding */ teamTallyModule)\n/* harmony export */ });\n/* harmony import */ var _module_tools__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./module-tools */ \"./src/js/admin/module-tools.js\");\n\r\n\r\nvar btnPhotoUpload;\r\nvar btnPhotoRemove;\r\nvar btnFrmSubmitSpinner;\r\nvar photoCtrl;\r\nvar photoPreview;\r\nvar formerPhotoCtrl;\r\nvar formerPhotoUrlCtrl;\r\n\r\nconst teamTallyModule = {};\r\n\r\n\r\n\r\n/**\r\n * Deletes a league\r\n *\r\n * @param leagueID\r\n */\r\n// teamTallyModule.confirmRemoval = (leagueID, item) => {\r\n\r\nfunction confirmLeagueRemoval(evt) {\r\n    const target = evt.target;\r\n    const leagueID = target.dataset.leagueId;\r\n\r\n    const leagueNameEl = document.querySelector(`.league-item-id-${leagueID} .league-name`);\r\n    const leagueName = leagueNameEl?.textContent;\r\n\r\n    const removeEl = document.querySelector(`.league-item-id-${leagueID} .teamtally-delete`);\r\n    const removeURL = removeEl?.dataset.removeUrl;\r\n\r\n    if (confirm(`Would you really want to delete the league \"${leagueName}\" ?`) && removeURL) {\r\n        window.location.href = removeURL;\r\n    }\r\n\r\n}\r\n\r\n/**\r\n * Open Media Uploader\r\n */\r\nfunction openMediaUploader() {\r\n    _module_tools__WEBPACK_IMPORTED_MODULE_0__[\"default\"].openMediaUploader(mediaUploader => {\r\n        // Get media attachment details from the frame state\r\n        var attachment = mediaUploader.state().get('selection').first().toJSON();\r\n\r\n        // The id of the photo will be used when saving data\r\n        photoCtrl.value = attachment.id;\r\n\r\n        // Display the photo in the preview zone\r\n        photoPreview.style.backgroundImage = `url(${attachment.url})`;\r\n        photoPreview.classList.remove('invalid');\r\n\r\n        // Hide the add image button and show remove image button\r\n        btnPhotoUpload.classList.add('hidden');\r\n        btnPhotoRemove.classList.remove('hidden');\r\n    });\r\n};\r\n\r\n/**\r\n * RemoveImage()\r\n */\r\nfunction removeImage() {\r\n    photoCtrl.value = \"\";\r\n    photoPreview.style.backgroundImage = 'none';\r\n    btnPhotoUpload.classList.remove('hidden');\r\n    btnPhotoRemove.classList.add('hidden');\r\n    photoPreview.classList.remove('invalid');\r\n\r\n    if (formerPhotoCtrl) {\r\n        photoCtrl.value = formerPhotoCtrl.value;\r\n        photoPreview.style.backgroundImage = `url(${formerPhotoUrlCtrl.value})`;\r\n    }\r\n\r\n}\r\n\r\n/**\r\n * ADD LEAGUE PAGE\r\n */\r\n_module_tools__WEBPACK_IMPORTED_MODULE_0__[\"default\"].executeIfSelectorExists('body.team-tally_page_teamtally_leagues_add', () => {\r\n    document.addEventListener('DOMContentLoaded', () => {\r\n\r\n        // Initialize global variables\r\n        btnPhotoUpload = document.querySelector('.teamtally_leagues__add-league #photo-upload');\r\n        btnPhotoRemove = document.querySelector('.teamtally_leagues__add-league #photo-remove');\r\n        photoCtrl = document.querySelector('form#add-league input[name=league-photo]');\r\n        formerPhotoCtrl = document.querySelector('form#add-league input[name=former-league-photo]');\r\n        formerPhotoUrlCtrl = document.querySelector('form#add-league input[name=former-league-photo-url]');\r\n        photoPreview = document.querySelector('.teamtally_leagues__add-league__photo');\r\n        btnFrmSubmitSpinner = document.querySelector('.teamtally_leagues__add-league .submit .spinner');\r\n\r\n        // setting events\r\n        btnPhotoUpload.addEventListener('click', () => openMediaUploader());\r\n        btnPhotoRemove.addEventListener('click', () => removeImage());\r\n\r\n        const frmEl = document.querySelector('.teamtally_leagues__add-league form');\r\n        frmEl.reset();\r\n\r\n        photoCtrl.value = formerPhotoCtrl.value;\r\n\r\n        // Submit the form - Proceed to last validation\r\n        frmEl.addEventListener('submit',\r\n            evt => {\r\n                if (!+photoCtrl.value) {\r\n                    photoPreview.classList.add('invalid');\r\n                    evt.preventDefault();\r\n                    return;\r\n                }\r\n\r\n                btnFrmSubmitSpinner.classList.add('is-active');\r\n            }\r\n        );\r\n\r\n    });\r\n});\r\n\r\n/**\r\n * LIST LEAGUES PAGE\r\n */\r\n_module_tools__WEBPACK_IMPORTED_MODULE_0__[\"default\"].executeIfSelectorExists('body.team-tally_page_teamtally_leagues_view', () => {\r\n\r\n    document.addEventListener('DOMContentLoaded', () => {\r\n\r\n        // Add remove league event\r\n        const removalLinkElements = document.querySelectorAll('.teamtally_leagues__league-item .teamtally-delete');\r\n        removalLinkElements.forEach(el => {\r\n            el.addEventListener('click', confirmLeagueRemoval);\r\n        });\r\n\r\n        // Add New league event\r\n        const btnNewLeagueEl = document.querySelector('.teamtally_leagues__league-item_new');\r\n        btnNewLeagueEl.addEventListener('click', () => {\r\n            const url = btnNewLeagueEl.dataset.newLeagueUrl;\r\n            window.location.href = url;\r\n        });\r\n\r\n    });\r\n\r\n});\r\n\r\n\r\n/* End of module */\n\n//# sourceURL=webpack://teamtally/./src/js/admin/module-teamtally-leagues.js?");

/***/ }),

/***/ "./src/js/admin/module-teamtally-teams.js":
/*!************************************************!*\
  !*** ./src/js/admin/module-teamtally-teams.js ***!
  \************************************************/
/***/ (() => {

eval("\r\nfunction editTeamsAddListBtn() {\r\n    const newTeamBtnEl = document.querySelector('.teamtally__teams__edit_team h1.wp-heading-inline');\r\n\r\n    if (!newTeamBtnEl) {\r\n        return;\r\n    }\r\n\r\n    const btnUrl = document.querySelector('.teamtally__teams__edit_team #teams_list_url').value;\r\n\r\n    if (btnUrl) {\r\n        const listTeamsBtnEl = document.createElement('a');\r\n        listTeamsBtnEl.setAttribute('class', 'page-title-action');\r\n        listTeamsBtnEl.setAttribute('href', btnUrl);\r\n        listTeamsBtnEl.setAttribute('style', \"margin-left: 20px;\")\r\n        listTeamsBtnEl.textContent = \"List Teams\";\r\n        newTeamBtnEl.append(listTeamsBtnEl);\r\n    }\r\n}\r\n\r\ndocument.addEventListener('DOMContentLoaded', () => {\r\n    editTeamsAddListBtn();\r\n});\n\n//# sourceURL=webpack://teamtally/./src/js/admin/module-teamtally-teams.js?");

/***/ }),

/***/ "./src/js/admin/module-tools.js":
/*!**************************************!*\
  !*** ./src/js/admin/module-tools.js ***!
  \**************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (/* binding */ teamTallyModule)\n/* harmony export */ });\nvar mediaUploader;\r\n\r\nconst teamTallyModule = {\r\n    mediaUploader,\r\n};\r\n\r\n\r\n\r\n/**\r\n * openMediaUploader()\r\n */\r\nteamTallyModule.openMediaUploader = (onSelectHandler) => {\r\n\r\n    if (mediaUploader) {\r\n        mediaUploader.open();\r\n        return;\r\n    }\r\n\r\n    mediaUploader = wp.media({\r\n        title: 'Select Image',\r\n        button: {\r\n            text: 'Use this image'\r\n        },\r\n        multiple: false\r\n    });\r\n\r\n    // Event handler when an image is selected\r\n    mediaUploader.on('select', () => {\r\n        onSelectHandler(mediaUploader);\r\n    });\r\n\r\n    mediaUploader.open();\r\n\r\n}\r\n\r\n/**\r\n * Checks if a selector exists in a page and then executes the specified code\r\n *\r\n * @param selector\r\n * @param fn\r\n *\r\n */\r\nteamTallyModule.executeIfSelectorExists = (selector, fn) => {\r\n    const markerFound = document.querySelector(selector);\r\n    if (markerFound) {\r\n        fn();\r\n    }\r\n}\n\n//# sourceURL=webpack://teamtally/./src/js/admin/module-tools.js?");

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module can't be inlined because the eval devtool is used.
/******/ 	var __webpack_exports__ = __webpack_require__("./src/js/admin/admin-script-index.js");
/******/ 	
/******/ })()
;