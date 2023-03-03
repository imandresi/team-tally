<?php
/**
 * Created by PhpStorm.
 * User: Itanjaka Mandresi
 * Date: 24/02/2023
 * Time: 07:15
 */

?>

<div class="teamtally_leagues__league-item league-item league-item-id-{{league_id}}">
    <div class="thumbnail">
        <div class="thumbnail__content" style="background-image: url('{{{league_photo_url}}}')"></div>
        <div class="thumbnail__legend"><a href="{{{manage_teams_url}}}">Click to manage TEAMS</a></div>
    </div>
    <div class="footer">
        <div class="league-data">
            <p><span class="league-name">{{league_name}}</span>&nbsp;{{teams_count}}</p>
            <p class="league-country">{{league_country}}</p>
        </div>
        <div class="league-item-buttons">
            <a href="{{{edit_league_url}}}" class="teamtally_btn teamtally_icon teamtally_icon_edit"></a>
            <div
               class="teamtally_btn teamtally_icon teamtally_icon_delete teamtally-delete teamtally_pointer_cursor"
               data-league-id="{{league_id}}"
               data-remove-url="{{{remove_league_url}}}"></div>
        </div>
    </div>
</div>

