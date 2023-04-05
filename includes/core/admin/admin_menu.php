<?php
/**
 * Created by PhpStorm.
 * User: Itanjaka Mandresi
 * Date: 13/12/2018
 * Time: 14:54
 */

namespace TEAMTALLY\Core\Admin;

use TEAMTALLY\Controllers\Export_Controller;
use TEAMTALLY\Controllers\Import_Controller;
use TEAMTALLY\Controllers\Leagues_Controller;
use TEAMTALLY\Controllers\Teams_Controller;
use TEAMTALLY\Core\Plugin_Manager;
use TEAMTALLY\Models\Leagues_Model;
use TEAMTALLY\System\Singleton;
use TEAMTALLY\System\Helper;
use TEAMTALLY\Views\About_View;

class Admin_Menu extends Singleton {

	const SLUG_MAIN_MENU = 'teamtally_main_menu';
	const SLUG_SUBMENU_LEAGUES = 'teamtally_leagues';
	const SLUG_SUBMENU_IMPORT = 'teamtally_import';
	const SLUG_SUBMENU_EXPORT = 'teamtally_export';
	const SLUG_SUBMENU_ABOUT = 'teamtally_about';
	const SLUG_SUBMENU_LEAGUES_VIEW = 'teamtally_leagues_view';
	const SLUG_SUBMENU_LEAGUES_ADD = 'teamtally_leagues_add';
	const SLUG_SUBMENU_TEAM_ADD = 'teamtally_team_add';


	const MENU_CAPABILITY = TEAMTALLY_USER_CAPABILITY;
	const MENU_ICON_FILENAME = TEAMTALLY_ASSETS_IMAGES_DIR . 'football.svg';

	public $active_page;
	public $league_data;
	public $league_id;

	/**
	 * WordPress ADMIN_MENU action
	 */
	public function action_admin_menu() {
		global $menu, $submenu;

		$this->active_page = Helper::get_var( Teams_Controller::get_instance()->active_page, false );
		$this->league_data = Helper::get_var( Teams_Controller::get_instance()->league, false );
		$this->league_id   = $this->league_data ? $this->league_data['raw']->term_id : false;

		/**
		 * Main admin menu
		 */
		add_menu_page(
			__( 'TEAM TALLY - Dashboard', TEAMTALLY_TEXT_DOMAIN ),
			'TEAM TALLY',
			self::MENU_CAPABILITY,
			self::SLUG_MAIN_MENU,
			function () {
			},
			Helper::svg_file_to_base64( self::MENU_ICON_FILENAME ),
			3
		);

		add_submenu_page(
			self::SLUG_MAIN_MENU,
			__( 'LEAGUES MANAGER', TEAMTALLY_TEXT_DOMAIN ),
			__( 'All Leagues', TEAMTALLY_TEXT_DOMAIN ),
			self::MENU_CAPABILITY,
			self::SLUG_SUBMENU_LEAGUES_VIEW,
			array( Leagues_Controller::class, 'admin_page_list_leagues' ),
			null
		);

		add_submenu_page(
			self::SLUG_MAIN_MENU,
			__( 'ADD / EDIT LEAGUE', TEAMTALLY_TEXT_DOMAIN ),
			__( 'Add New League', TEAMTALLY_TEXT_DOMAIN ),
			self::MENU_CAPABILITY,
			self::SLUG_SUBMENU_LEAGUES_ADD,
			array( Leagues_Controller::class, 'admin_page_add_or_edit_league' ),
			null
		);

		// Only add this submenu if there are leagues available
		$leagues = Leagues_Model::get_all_leagues();

		if ( $leagues ) {

			// List Teams (only of the selected league)
			if ( $this->league_id ) {
				$url         = "edit.php?post_type=teamtally_teams&league_id={$this->league_id}";
				$league_name = strtoupper( $this->league_data['data']['league_name'] );
				$menu_title  = sprintf( __( 'Teams in %s', TEAMTALLY_TEXT_DOMAIN ), $league_name );
				add_submenu_page(
					self::SLUG_MAIN_MENU,
					__( 'List Teams', TEAMTALLY_TEXT_DOMAIN ),
					$menu_title,
					self::MENU_CAPABILITY,
					$url,
					null,
					null
				);
			}

			// Add New Team
			$url = 'post-new.php?post_type=teamtally_teams';
			$url .= $this->league_id ? "&league_id={$this->league_id}" : '';
			add_submenu_page(
				self::SLUG_MAIN_MENU,
				__( 'Add New Team', TEAMTALLY_TEXT_DOMAIN ),
				__( 'Add New Team', TEAMTALLY_TEXT_DOMAIN ),
				self::MENU_CAPABILITY,
				$url,
				null,
				null
			);
		}

		add_submenu_page(
			self::SLUG_MAIN_MENU,
			__( 'Import TEAM TALLY Demo or file', TEAMTALLY_TEXT_DOMAIN ),
			__( 'Import TEAM TALLY Demo or file', TEAMTALLY_TEXT_DOMAIN ),
			self::MENU_CAPABILITY,
			self::SLUG_SUBMENU_IMPORT,
			array( Import_Controller::class, 'import_page' ),
			null
		);

		add_submenu_page(
			self::SLUG_MAIN_MENU,
			__( 'Export current TEAM TALLY data', TEAMTALLY_TEXT_DOMAIN ),
			__( 'Export current TEAM TALLY data', TEAMTALLY_TEXT_DOMAIN ),
			self::MENU_CAPABILITY,
			self::SLUG_SUBMENU_EXPORT,
			array( Export_Controller::class, 'export_page' ),
			null
		);

		add_submenu_page(
			self::SLUG_MAIN_MENU,
			__( 'ABOUT', TEAMTALLY_TEXT_DOMAIN ),
			__( 'About', TEAMTALLY_TEXT_DOMAIN ),
			self::MENU_CAPABILITY,
			self::SLUG_SUBMENU_ABOUT,
			array( About_View::class, 'display_about_page' ),
			null
		);

		unset ( $submenu['teamtally_main_menu'][0] );

		$this->init_admin_menu_selected_items();

	}

	/**
	 * Defines the selected items in the admin menu
	 *
	 * @return void
	 */
	public function init_admin_menu_selected_items() {
		add_action( 'parent_file', function ( $parent_file ) {
			if ( $this->active_page ) {
				$parent_file = self::SLUG_MAIN_MENU;
			}

			return $parent_file;
		}, 10, 1 );

		add_action( 'submenu_file', function ( $submenu_file, $parent_file ) {
			if ( $this->active_page && $this->league_id ) {
				$submenu_file .= "&league_id={$this->league_id}";
			}

			return $submenu_file;
		}, 10, 2 );
	}

	/**
	 * Initialization
	 */
	protected function init() {
		add_action( 'admin_menu', array( $this, 'action_admin_menu' ), 2000 );
	}

	/**
	 * Executes the class
	 */
	public static function load() {
		self::get_instance();
	}

}

