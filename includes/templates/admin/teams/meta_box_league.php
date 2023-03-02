<?php
/**
 * Created by PhpStorm.
 * User: Itanjaka Mandresi
 * Date: 01/03/2023
 * Time: 08:22
 */

/** @var array $leagues */
/** @var int $selected_league_id */

?>

<style>
    label[for=teams_league] {
        display: block;
        margin-bottom: 15px;
    }

    #teams_league {
        margin:
    }
</style>

<div>
    <input type="hidden" name="league_id" id="league_id" value="{{selected_league_id}}">
    <input type="hidden" name="teams_list_url" id="teams_list_url" value="{{teams_list_url}}">
    <label for="teams_league">Select the league which is associated to the team.</label>
    <select id="teams_league" name="teams_league" required autocomplete="off">
        <option>Select a league</option>
		<?php foreach ( $leagues as $league ) : ?>
            <option value="<?= $league['data']['league_name']; ?>"
				<?= $league['data']['term_id'] == $selected_league_id ? 'selected="selected"' : ''; ?>>
				<?= $league['data']['league_name']; ?>
            </option>
		<?php endforeach; ?>
    </select>
</div>


