<?php
/**
 * Created by PhpStorm.
 * User: Itanjaka Mandresi
 * Date: 01/03/2023
 * Time: 09:06
 */

namespace TEAMTALLY\Views;

use TEAMTALLY\Models\Leagues_Model;
use TEAMTALLY\System\Template;

class Teams_View {

	public static function new_team_page_meta_box_league_content( $leagues, $selected_league_id ) {

		Template::pparse( 'admin/teams/meta_box_league.php', array(
			'leagues' => $leagues,
			'selected_league_id' => $selected_league_id
		) );
	}

}