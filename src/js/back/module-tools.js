var mediaUploader;

const teamTallyModule = {
    mediaUploader,
};

export {teamTallyModule as default};

/**
 * openMediaUploader()
 */
teamTallyModule.openMediaUploader = (onSelectHandler) => {

    if (mediaUploader) {
        mediaUploader.open();
        return;
    }

    mediaUploader = wp.media({
        title: 'Select Image',
        button: {
            text: 'Use this image'
        },
        multiple: false
    });

    // Event handler when an image is selected
    mediaUploader.on('select', () => {
        onSelectHandler(mediaUploader);
    });

    mediaUploader.open();

}