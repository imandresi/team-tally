<?php
/**
 * Created by PhpStorm.
 * User: Itanjaka Mandresi
 * Date: 11/12/2018
 * Time: 05:19
 */

namespace TEAMTALLY\Core;

use TEAMTALLY\Core\Admin\Admin_Menu;
use TEAMTALLY\Core\Plugin_Manager;
use TEAMTALLY\System\Helper;
use TEAMTALLY\System\Singleton;

class Admin_Loader extends Singleton {

	/**
	 * Leagues Management
	 *
	 * Checks if in /wp-admin/admin.php?page=teamtally_leagues
	 * and executes various initialization tasks
	 */
	private function init_admin_leagues() {
/*
		$do_init = Helper::compare_page_request_to(
			array(
				'path'  => '/wp-admin/admin.php',
				'query' => array(
					'page' => 'teamtally_leagues'
				)
			)
		);

		if ( ! $do_init ) {
			return;
		}
*/
		// Prepare the use of wp.media for file uploading
		add_action( 'admin_enqueue_scripts', function ( $hook_suffix ) {
			if ( $hook_suffix == 'team-tally_page_teamtally_leagues_add' ) {
				wp_enqueue_media();
			}
		} );

	}


	/**
	 * Loading admin styles / scripts
	 */
	public function action_admin_enqueue_scripts() {

		// Loads theme script
		$script_list = array(
			'css/back-style.css',
			'js/back-script.js'
		);

		Helper::str_enqueue_script_list( $script_list, TEAMTALLY_DEV_MODE );

	}

	/**
	 * Automatically called at initialization
	 */
	protected function init() {
		if ( ! is_admin() ) {
			return;
		}

		// Loading admin styles and scripts
		add_action( 'admin_enqueue_scripts', array( $this, 'action_admin_enqueue_scripts' ) );

		Plugin_Manager::setup();
		Admin_Menu::load();

		// Loading
		$this->init_admin_leagues();
	}

	/**
	 * Loader
	 */
	public static function load() {
		self::get_instance();
	}

}