<?php
/**
 * Created by PhpStorm.
 * User: Itanjaka Mandresi
 * Date: 22/02/2023
 * Time: 13:24
 */

namespace TEAMTALLY\Controllers;

use TEAMTALLY\Models\Generic_Model;
use TEAMTALLY\Models\Leagues_Model;
use TEAMTALLY\System\Helper;
use TEAMTALLY\Views\Leagues_View;

class Leagues_Controller {

	/**
	 * Handles the page managing the leagues
	 *
	 * Called when clicking the 'All Leagues' admin menu
	 *
	 * @return void
	 */
	public static function admin_management_page() {

	}


	/**
	 * Hendles the page used to add or edit leagues
	 *
	 * Called when clicking the 'Add League' admin menu
	 *
	 * @return void
	 */
	public static function admin_page_add_or_edit() {
		$post_id = Helper::get_var( $_REQUEST['post_id'], 0 );

		// Process Form
		if ( isset( $_POST['action'] ) ) {
			check_admin_referer( 'add-league', 'add-league-nonce' );
			$post_id = Helper::get_var( $_POST['id'], 0 );
			$post_id = Leagues_Model::update_league( $_POST, $post_id );
		}

		// Display the page
		Leagues_View::add_or_edit_page( $post_id, true );

	}

}