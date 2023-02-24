var btnPhotoUpload;
var btnPhotoRemove;
var btnFrmSubmitSpinner;
var photoCtrl;
var photoPreview;
var formerPhotoCtrl;
var formerPhotoUrlCtrl;

const teamTallyModule = {};

export {teamTallyModule as default};

/**
 * Open Media Uploader
 */
function openMediaUploader() {
    window.TEAMTALLY.tools.openMediaUploader(mediaUploader => {
        // Get media attachment details from the frame state
        var attachment = mediaUploader.state().get('selection').first().toJSON();

        // The id of the photo will be used when saving data
        photoCtrl.value = attachment.id;

        // Display the photo in the preview zone
        photoPreview.style.backgroundImage = `url(${attachment.url})`;
        photoPreview.classList.remove('invalid');

        // Hide the add image button and show remove image button
        btnPhotoUpload.classList.add('hidden');
        btnPhotoRemove.classList.remove('hidden');
    });
};

/**
 * RemoveImage()
 */
function removeImage() {
    photoCtrl.value = "";
    photoPreview.style.backgroundImage = 'none';
    btnPhotoUpload.classList.remove('hidden');
    btnPhotoRemove.classList.add('hidden');
    photoPreview.classList.remove('invalid');

    if (formerPhotoCtrl) {
        photoCtrl.value = formerPhotoCtrl.value;
        photoPreview.style.backgroundImage = `url(${formerPhotoUrlCtrl.value})`;
    }

}

/**
 *  SCRIPT ONLY AVAILABLE FOR: team-tally_page_teamtally_leagues
 *  Check if we are in the 'leagues management' page /wp-admin/admin.php?page=teamtally_leagues
 */

const leaguesMarkerFound = document.body.classList.contains('team-tally_page_teamtally_leagues_add');

if (leaguesMarkerFound) {

    document.addEventListener('DOMContentLoaded', () => {

        // Initialize global variables
        btnPhotoUpload = document.querySelector('.teamtally_leagues__add-league #photo-upload');
        btnPhotoRemove = document.querySelector('.teamtally_leagues__add-league #photo-remove');
        photoCtrl = document.querySelector('form#add-league input[name=league-photo]');
        formerPhotoCtrl = document.querySelector('form#add-league input[name=former-league-photo]');
        formerPhotoUrlCtrl = document.querySelector('form#add-league input[name=former-league-photo-url]');
        photoCtrl = document.querySelector('form#add-league input[name=league-photo]');
        photoPreview = document.querySelector('.teamtally_leagues__add-league__photo');
        btnFrmSubmitSpinner = document.querySelector('.teamtally_leagues__add-league .submit .spinner');

        // setting events
        btnPhotoUpload.addEventListener('click', () => openMediaUploader());
        btnPhotoRemove.addEventListener('click', () => removeImage());

        const frmEl = document.querySelector('.teamtally_leagues__add-league form');
        frmEl.reset();

        photoCtrl.value = "";

        // Submit the form - Proceed to last validation
        frmEl.addEventListener('submit',
            evt => {
                if (!+photoCtrl.value) {
                    photoPreview.classList.add('invalid');
                    evt.preventDefault();
                    return;
                }

                btnFrmSubmitSpinner.classList.add('is-active');
            }
        );

    });

} /* End of module */