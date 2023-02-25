<?php
/**
 * Created by PhpStorm.
 * User: Itanjaka Mandresi
 * Date: 22/02/2023
 * Time: 13:32
 */

use TEAMTALLY\Views\Leagues_View;

/** @var array $leagues */
?>

<div class="teamtally_leagues__list-leagues wrap">
    <h1 class="wp-heading-inline">Leagues</h1>
    <p>A <b>football league</b> is a competition where multiple <b>football teams</b> play against each other in a series of matches
        over a set period of time to earn points. The team with the most points at the end of the season is declared the
        winner of the league.</p>

    <div class="leagues-zone">
		<?php foreach ( $leagues as $league ) : ?>
			<?php Leagues_View::display_league( $league ); ?>
		<?php endforeach; ?>
    </div>

</div>



