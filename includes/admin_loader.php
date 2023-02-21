<?php
/**
 * Created by PhpStorm.
 * User: Itanjaka Mandresi
 * Date: 11/12/2018
 * Time: 05:19
 */

namespace TEAMTALLY;

use TEAMTALLY\Core\Admin\Admin_Menu;
use TEAMTALLY\Core\Plugin_Manager;
use TEAMTALLY\System\Helper;
use TEAMTALLY\System\Singleton;

class Admin_Loader extends Singleton {

	/**
	 * Loading the class Admin_Users
	 */
/*	private function load_admin_edit() {
		$load_class = Helper::compare_page_request_to(
			array(
				'path' => '/wp-admin/edit.php',
			)
		);

		if ($load_class) {
			Core\Admin\Admin_Edit::load();
		}
	}*/

	/**
	 * Loading admin styles / scripts
	 */
	public function action_admin_enqueue_scripts() {

		// Loads theme script
		$script_list = array(
			'css/back-style.css',
			'js/back-script.js',
		);

		Helper::str_enqueue_script_list( $script_list );

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

	}

	/**
	 * Loader
	 */
	public static function load() {
		self::get_instance();
	}

}