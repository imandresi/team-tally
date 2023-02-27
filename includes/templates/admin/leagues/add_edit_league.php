<?php
/**
 * Created by PhpStorm.
 * User: Itanjaka Mandresi
 * Date: 22/02/2023
 * Time: 12:04
 */

use TEAMTALLY\System\Admin_Notices;

/** @var array-key $country_list */
/** @var string $league_country */
/** @var string $league_photo */
/** @var int $former_league_photo */
/** @var string $former_league_photo_url */
?>

<?php Admin_Notices::all_pending_notices(true); ?>
<div class="wrap">
    <h1>League Management</h1>
    <div class="form-wrap">
        <h2>{{page_title}}</h2>
        <div class="teamtally_leagues__add-league">
            <form id="add-league" method="post" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add-edit-league">
                <input type="hidden" name="id" value="{{{id}}}">
                <input type="hidden" name="league-photo" value="{{{league_photo}}}">
                <input type="hidden" name="former-league-photo" value="{{{former_league_photo}}}">
                <input type="hidden" name="former-league-photo-url" value="{{{former_league_photo_url}}}">
				<?php wp_nonce_field( 'add-league', 'add-league-nonce' ); ?>

                <div class="row">
                    <div class="left">

                        <div class="form-field form-required">
                            <label for="league-name">League Name</label>
                            <input name="league-name" id="league-name" type="text" size="40"
                                   value="{{{league_name}}}"
                                   required
                                   aria-required="true"
                                   aria-describedby="name-description">
                            <p id="name-description">It is the name of the league you want to create.</p>
                        </div>

                        <div class="form-field">
                            <label for="league-country">League Country</label>
                            <select name="league-country" id="league-country"
                                    aria-describedby="country-description"
                                    required>
                                <option value="">Select the country</option>
								<?php
								foreach ( $country_list as $country_name ) {
									$selected = $league_country == $country_name ? 'selected' : '';
									print "<option \"$country_name\" $selected>$country_name</option>";
								}
								?>
                            </select>
                            <p id="country-description">It is the country where the league is.</p>
                        </div>

                    </div>
                    <div class="right">
                        <div class="teamtally_leagues__add-league__photo"
                             style="background-image: url('{{former_league_photo_url}}')"></div>
                        <div class="teamtally_alignleft">
                            <input type="button" name="photo-upload" id="photo-upload"
                                   class="button"
                                   value="Upload image">

                            <input type="button" name="photo-remove" id="photo-remove"
                                   class="button hidden"
                                   value="Remove image">
                        </div>
                    </div>
                </div>
                <div class="submit">
                    <input type="submit" name="submit" id="submit" class="button button-primary"
                           value="Save League">
                    <span class="spinner"></span>
                </div>
            </form>
        </div>
    </div>

</div>