<?php
/**
 * Created by PhpStorm.
 * User: Itanjaka Mandresi
 * Date: 03/04/2023
 * Time: 18:23
 */

/** @var string $nonce */

?>

<div id="teamtally-export" class="wrap">
    <h1 class="wp-heading-inline">Export Data</h1>
    <p>This functionality allows you to compress and export the TEAM TALLY data such as the leagues, the teams with their
    corresponding logos into a ZIP filename. You can then store this backup for later use.</p>
    <p>All exported data are stored inside the WordPress 'uploads' folder and can be imported using the import interface.</p>

    <form class="mt-8" method="post">
        <input type="hidden" name="_nonce" value="<?= $nonce; ?>">
        <input type="hidden" name="action" value="do-export">

        <input type="submit" name="btn-export" id="btn-export" class="button button-primary"
               value="Proceed to export">
    </form>

</div>
