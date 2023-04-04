<?php
/**
 * Created by PhpStorm.
 * User: Itanjaka Mandresi
 * Date: 01/04/2023
 * Time: 19:01
 */

/** @var string $nonce */
/** @var array $archive */

\TEAMTALLY\System\Helper::debug( $archive, '$archive', true );
?>

<div id="teamtally-import" class="wrap">
    <h1 class="wp-heading-inline">Import Data</h1>

    <div class="teamtally_card">
        <h2>Description</h2>
        <p>This section allows you to import a data set of the leagues, the teams and each of their corresponding logos
            which are automatically inserted inside the media library. Those data are stored in a zip file which and has
            been generated using the "export" functionality.</p>

        <p>You can use 3 sorts of data:</p>
        <ol>
            <li>"DEMO DATA" - You can use those data in order to test the plugin.</li>
            <li>"ARCHIVE DATA" from export - Those data may be data backup. They are listed by date. Note that this
                option
                is only available after using the "Export" functionality.
            </li>
            <li>"UPLOADED" data - Those are external data you upload.</li>
        </ol>
    </div>

    <h2 class="mt-8 mb-5">Choose the type of import</h2>
    <form enctype="multipart/form-data" method="post">
        <input type="hidden" name="_nonce" value="<?= $nonce; ?>">
        <input type="hidden" name="action" value="do-import">
        <div id="demo-radio" class="mb-5">
            <input type="radio" id="import_demo_type" name="import_type" value="demo" checked>
            <label for="import_demo_type">Import <strong>TEAM TALLY DEMO</strong></label>
        </div>

		<?php if ( $archive && is_array( $archive ) ): ?>
            <div class="mb-5">
                <input type="radio" id="import_archive_type" name="import_type" value="archive">
                <label for="import_archive_type">Import data from exported archive</label>
                <div class="mt-2 ms-5">
                    <select id="import_archive" name="import_archive">
						<?php foreach ( $archive as $archive_item ) : ?>
                            <option value="<?= $archive_item['basename']; ?>"><?= $archive_item['caption']; ?></option>
						<?php endforeach; ?>
                    </select>
                </div>
            </div>
		<?php endif; ?>

        <div class="mb-6">
            <input type="radio" id="import_upload_type" name="import_type" value="upload">
            <label for="import_upload_type">Import data from uploaded file</label>
            <div class="mt-2 ms-5">
                <input type="file" id="import_data" name="import_data">
            </div>
        </div>

        <div>
            <input type="checkbox" id="clear_previous_data" name="clear_previous_data" value="on">
            <label for="clear_previous_data">Remove all previous TEAM TALLY data</label>
        </div>

        <div class="mt-8">
            <input type="submit" name="btn-import" id="btn-import" class="button button-primary"
                   value="Proceed to import">
        </div>

    </form>

</div>

