<?php
/**
 * Created by PhpStorm.
 * User: Itanjaka Mandresi
 * Date: 01/03/2023
 * Time: 07:23
 */
?>

<style>
    #wp-content-editor-tools {
        padding-top: 0;
    }

    #title-prompt-text {
        opacity: 0.5;
    }

    .teams-history {
        margin-top: 30px;
        font-size: 165%;
        color: #888;
    }

    #team-nickname {
        font-size: 165%;
    }

    #team-nickname::placeholder {
        opacity: 0.5;
    }

    #team-nickname-div {
        margin: 20px 0;
    }

</style>

<div id="team-nickname-div">
    <input type="text"
           id="team-nickname"
           name="team_nickname"
           placeholder="Enter the team nickname"
           autocomplete="off" size="20" spellcheck="true"
           value="{{team_nickname}}">
</div>

<p class="teams-history"><?php _e( 'Enter the team history below:', TEAMTALLY_TEXT_DOMAIN ); ?></p>

