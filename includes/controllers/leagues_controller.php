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
use TEAMTALLY\System\Template;
use TEAMTALLY\Views\Leagues_View;

class Leagues_Controller {

	/**
	 * Handles the page managing the leagues
	 *
	 * Called when clicking the 'All Leagues' admin menu
	 *
	 * @return void
	 */
	public static function admin_page_list_leagues() {

		// makes a loop to build the league $data
		$leagues = Leagues_Model::get_all_leagues();

		// displays the data using the View
		Leagues_View::admin_page_list_leagues( $leagues );

	}


	/**
	 * Hendles the page used to add or edit leagues
	 *
	 * Called when clicking the 'Add League' admin menu
	 *
	 * @return void
	 */
	public static function admin_page_add_or_edit_league() {
		$post_id = Helper::get_var( $_REQUEST['post_id'], 0 );
		Leagues_View::admin_page_add_or_edit_league( $post_id, true );
	}

	/**
	 * Process the add or edit league form
	 *
	 * @return void
	 */
	public static function process_form_add_edit_league() {
		if ( isset( $_POST['action'] ) && ( $_POST['action'] == 'add-edit-league' ) ) {
			check_admin_referer( 'add-league', 'add-league-nonce' );
			$post_id = Helper::get_var( $_POST['id'], 0 );
			Leagues_Model::update_league( $_POST, $post_id );

			// redirect to 'list of leagues'
			$url = admin_url( 'admin.php?page=teamtally_leagues_view' );
			wp_redirect( $url );
			exit;
		}
	}

	private static function admin_init() {
		add_action( 'init', array( self::class, 'process_form_add_edit_league' ) );
	}

	/**
	 * Initialization
	 *
	 * @return void
	 */
	public static function init() {
		if ( is_admin() ) {
			self::admin_init();
		}

	}

}