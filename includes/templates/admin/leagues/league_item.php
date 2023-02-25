<?php
/**
 * Created by PhpStorm.
 * User: Itanjaka Mandresi
 * Date: 24/02/2023
 * Time: 07:15
 */

?>

<div class="teamtally_leagues__league-item">
    <div class="thumbnail">
        <div class="thumbnail__content" style="background-image: url('{{{league_photo_url}}}')"></div>
        <div class="thumbnail__legend">Click to manage TEAMS</div>
    </div>
    <div class="footer">
        <div class="league-data">
            <p class="leaague-name">{{league_name}}</p>
            <p class="league-country">{{league_country}}</p>
        </div>
        <div class="league-item-buttons">
            <a href="{{{edit_league_url}}}" class="teamtally_icon teamtally_icon_edit"></a>
            <a href="{{{remove_league_url}}}" class="teamtally_icon teamtally_icon_delete"></a>
        </div>
    </div>
</div>

