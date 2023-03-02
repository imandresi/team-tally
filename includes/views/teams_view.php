<?php
/**
 * Created by PhpStorm.
 * User: Itanjaka Mandresi
 * Date: 01/03/2023
 * Time: 09:06
 */

namespace TEAMTALLY\Views;

use TEAMTALLY\Models\Leagues_Model;
use TEAMTALLY\Models\Teams_Model;
use TEAMTALLY\System\Template;

class Teams_View {

	public static function new_team_page_meta_box_league_content( $leagues, $selected_league_id ) {
		$teams_list_url = '';

		if ( $selected_league_id ) {
			$teams_list_url = add_query_arg(array(
				'post_type' => Teams_Model::TEAMS_POST_TYPE,
				'league_id' => $selected_league_id
			), admin_url('edit.php'));

		}

		Template::pparse( 'admin/teams/meta_box_league.php', array(
			'leagues'            => $leagues,
			'selected_league_id' => $selected_league_id,
			'teams_list_url'     => $teams_list_url,
		) );
	}

}