<?php
/**
 * Used by TEAMTALLY\System\Shared_Data::share_to_js()
 * It shares a PHP data to JS
 */

/** @var array $shared_data */

?>
    if (typeof window.TEAMTALLY === 'undefined') {
    window.TEAMTALLY = {};
    }

    if (typeof window.TEAMTALLY.SHARED_DATA === 'undefined') {
    window.TEAMTALLY.SHARED_DATA = {};
    }

<?php foreach ( $shared_data as $key => $value ): ?>
    window.TEAMTALLY.SHARED_DATA["<?= addslashes( $key ); ?>"] = <?= json_encode( $value ); ?>;
<?php endforeach; ?>