/**
 * Import Module
 */

/**
 * Activates the upload radio button when the file upload control is clicked
 */
function initUploadEvent() {
    const fileUploadEl = document.querySelector('#teamtally-import #import_data');
    const uploadRadioEl = document.querySelector('#teamtally-import #import_upload_type');

    if (!fileUploadEl || !uploadRadioEl) return;

    fileUploadEl.addEventListener('click', e => {
        uploadRadioEl.checked = true;
    });

}

/**
 * Activates the archive radio button when select control is clicked
 */
function initArchiveEvent() {
    const archiveSelectEl = document.querySelector('#teamtally-import #import_archive');
    const archiveRadioEl = document.querySelector('#teamtally-import #import_archive_type');

    if (!archiveSelectEl || !archiveRadioEl) return;

    archiveSelectEl.addEventListener('click', e => {
        archiveRadioEl.checked = true;
    });

}

/**
 * Main initialization
 */
document.addEventListener('DOMContentLoaded', e => {
    initUploadEvent();
    initArchiveEvent();
});