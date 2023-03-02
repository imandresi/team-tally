<?php
/**
 * Created by PhpStorm.
 * User: Itanjaka Mandresi
 * Date: 13/12/2018
 * Time: 14:54
 */

namespace TEAMTALLY\Core\Admin;

use TEAMTALLY\Controllers\Leagues_Controller;
use TEAMTALLY\System\Singleton;
use TEAMTALLY\System\Helper;

class Admin_Menu extends Singleton {

	const SLUG_MAIN_MENU = 'teamtally_main_menu';
	const SLUG_SUBMENU_LEAGUES = 'teamtally_leagues';
	const SLUG_SUBMENU_ABOUT = 'teamtally_about';
	const SLUG_SUBMENU_LEAGUES_VIEW = 'teamtally_leagues_view';
	const SLUG_SUBMENU_LEAGUES_ADD = 'teamtally_leagues_add';

	const MENU_CAPABILITY = TEAMTALLY_USER_CAPABILITY;
	const MENU_ICON_FILENAME = TEAMTALLY_ASSETS_IMAGES_DIR . 'football.svg';

	/**
	 * WordPress ADMIN_MENU action
	 */
	public function action_admin_menu() {
		global $menu, $submenu;

		/**
		 * Main admin menu : TOP PROF Mada
		 */
		add_menu_page(
			'TEAM Tally - Dashboard',
			'TEAM Tally',
			self::MENU_CAPABILITY,
			self::SLUG_MAIN_MENU,
			function () {
			},
			Helper::svg_file_to_base64( self::MENU_ICON_FILENAME ),
			3
		);

		add_submenu_page(
			self::SLUG_MAIN_MENU,
			'LEAGUES MANAGER',
			'All Leagues',
			self::MENU_CAPABILITY,
			self::SLUG_SUBMENU_LEAGUES_VIEW,
			array(Leagues_Controller::class, 'admin_page_list_leagues' ),
			null
		);

		add_submenu_page(
			self::SLUG_MAIN_MENU,
			'ADD / EDIT LEAGUE',
			'Add New League',
			self::MENU_CAPABILITY,
			self::SLUG_SUBMENU_LEAGUES_ADD,
			array(Leagues_Controller::class, 'admin_page_add_or_edit_league' ),
			null
		);

		add_submenu_page(
			self::SLUG_MAIN_MENU,
			'ABOUT THE AUTHOR',
			'About',
			self::MENU_CAPABILITY,
			self::SLUG_SUBMENU_ABOUT,
			function () {
			},
			null
		);

		unset ( $submenu['teamtally_main_menu'][0] );

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

Admin_Menu::load();