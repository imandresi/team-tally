import TOOLS from "./module-tools";

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
 * Deletes a league
 *
 * @param leagueID
 */
// teamTallyModule.confirmRemoval = (leagueID, item) => {

function confirmLeagueRemoval(evt) {
    const target = evt.target;
    const leagueID = target.dataset.leagueId;

    const leagueNameEl = document.querySelector(`.league-item-id-${leagueID} .league-name`);
    const leagueName = leagueNameEl?.textContent;

    const removeEl = document.querySelector(`.league-item-id-${leagueID} .teamtally-delete`);
    const removeURL = removeEl?.dataset.removeUrl;

    if (confirm(`Would you really want to delete the league "${leagueName}" ?`) && removeURL) {
        window.location.href = removeURL;
    }

}

/**
 * Open Media Uploader
 */
function openMediaUploader() {
    TOOLS.openMediaUploader(mediaUploader => {
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
 * ADD LEAGUE PAGE
 */
TOOLS.executeIfSelectorExists('body.team-tally_page_teamtally_leagues_add', () => {
    document.addEventListener('DOMContentLoaded', () => {

        // Initialize global variables
        btnPhotoUpload = document.querySelector('.teamtally_leagues__add-league #photo-upload');
        btnPhotoRemove = document.querySelector('.teamtally_leagues__add-league #photo-remove');
        photoCtrl = document.querySelector('form#add-league input[name=league-photo]');
        formerPhotoCtrl = document.querySelector('form#add-league input[name=former-league-photo]');
        formerPhotoUrlCtrl = document.querySelector('form#add-league input[name=former-league-photo-url]');
        photoPreview = document.querySelector('.teamtally_leagues__add-league__photo');
        btnFrmSubmitSpinner = document.querySelector('.teamtally_leagues__add-league .submit .spinner');

        // setting events
        btnPhotoUpload.addEventListener('click', () => openMediaUploader());
        btnPhotoRemove.addEventListener('click', () => removeImage());

        const frmEl = document.querySelector('.teamtally_leagues__add-league form');
        frmEl.reset();

        photoCtrl.value = formerPhotoCtrl.value;

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
});

/**
 * LIST LEAGUES PAGE
 */
TOOLS.executeIfSelectorExists('body.team-tally_page_teamtally_leagues_view', () => {

    document.addEventListener('DOMContentLoaded', () => {

        // Add remove league event
        const removalLinkElements = document.querySelectorAll('.teamtally_leagues__league-item .teamtally-delete');
        removalLinkElements.forEach(el => {
            el.addEventListener('click', confirmLeagueRemoval);
        });

        // Add New league event
        const btnNewLeagueEl = document.querySelector('.teamtally_leagues__league-item_new');
        btnNewLeagueEl.addEventListener('click', () => {
            const url = btnNewLeagueEl.dataset.newLeagueUrl;
            window.location.href = url;
        });

    });

});


/* End of module */