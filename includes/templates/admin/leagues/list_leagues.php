<?php
/**
 * Created by PhpStorm.
 * User: Itanjaka Mandresi
 * Date: 22/02/2023
 * Time: 13:32
 */

use TEAMTALLY\Views\Leagues_View;

/** @var array $leagues */
/** @var string $new_league_url */
?>

<div class="teamtally_leagues__list-leagues wrap">
    <h1 class="wp-heading-inline">Leagues</h1>
    <p>A <b>football league</b> is a competition where multiple <b>football teams</b> play against each other in a
        series of matches
        over a set period of time to earn points. The team with the most points at the end of the season is declared the
        winner of the league.</p>

    <div class="leagues-zone">
		<?php foreach ( $leagues as $league ) : ?>
			<?php Leagues_View::display_league( $league ); ?>
		<?php endforeach; ?>

        <!-- Add New League Button -->
        <div class="teamtally_leagues__league-item_new league-item"
             data-new-league-url="{{new_league_url}}">
            <div class="teamtally_icon_add">
                <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg"
                     xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                     viewBox="0 0 666.7 666.7" style="enable-background:new 0 0 666.7 666.7;" xml:space="preserve">
                <path id="svg-cross"
                      d="M0,0v666.7h666.7V0H0z M500,366.7H366.9v133.6H300V366.7H166.6V300H300V164.9h66.9V300H500V366.7z"/>
            </svg>
            </div>
            <div class="legend">Add New League</div>
        </div>

    </div>

</div>



