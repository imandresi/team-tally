<?php
/**
 * Created by PhpStorm.
 * User: Itanjaka Mandresi
 * Date: 22/02/2023
 * Time: 13:32
 */

use TEAMTALLY\System\Admin_Notices;
use TEAMTALLY\Views\Leagues_View;
use TEAMTALLY\Models\Teams_Model;

/** @var array $leagues */
/** @var string $new_league_url */
?>

<?php Admin_Notices::all_pending_notices(true); ?>

<div class="teamtally_leagues__list-leagues wrap">
    <h1 class="wp-heading-inline">LEAGUE MANAGEMENT</h1>
    <h2>List of all leagues</h2>
    <p>A <b>football league</b> is a competition where multiple <b>football teams</b> play against each other in a
        series of matches
        over a set period of time to earn points. The team with the most points at the end of the season is declared the
        winner of the league.</p>

    <p>On this page, you have the ability to manage leagues. You can add new leagues or modify their properties,
        including their logo, name, or country. Additionally, you have the option to delete a league.
        If you click on a specific league, you will gain access to team management options.</p>

    <div class="leagues-zone">
		<?php foreach ( $leagues as $league ) : ?>
            <?php $teams_count = Teams_Model::count_teams_in_league($league); ?>
            <?php $teams_count = $teams_count ? "($teams_count)" : ""; ?>
			<?php Leagues_View::display_league( $league, $teams_count); ?>
		<?php endforeach; ?>

        <!-- Add New League Button -->
        <?php Leagues_View::display_new_league_big_btn(); ?>

    </div>

</div>



