<?php
/**
 * Created by PhpStorm.
 * User: Itanjaka Mandresi
 * Date: 08/12/2018
 * Time: 18:56
 */

namespace TEAMTALLY\Core;

use TEAMTALLY\Controllers\Leagues_Controller;
use TEAMTALLY\Controllers\Teams_Controller;
use TEAMTALLY\Models\Leagues_Model;
use TEAMTALLY\Models\Teams_Model;
use TEAMTALLY\System\Sessions;
use TEAMTALLY\System\Singleton;
use TEAMTALLY\System\Helper;

include_once( TEAMTALLY_INCLUDES_DIR . 'core/autoloader.php' );
include_once( TEAMTALLY_INCLUDES_DIR . 'system/singleton.php' );
include_once( TEAMTALLY_INCLUDES_DIR . 'system/helper.php' );

class Loader extends Singleton {

	/**
	 * Loads dependencies
	 */
	private function load_dependencies() {
		// Initializing class Autoloader
		Autoloader::init();

		// Initializing sessions
		Sessions::init();

		// Loads models
		Leagues_Model::init();
		Teams_Model::init();

		// Initialization of activation/deactivation/uninstall hooks
		Plugin_Manager::init();

		// Loads frontend routines
		Frontend_Loader::load();

		// Loads admin routines
		Admin_Loader::load();

		// Loads controllers
		Leagues_Controller::init();
		Teams_Controller::load();

	}

	/**
	 * Initializes language
	 */
	public function language_setup() {
		load_child_theme_textdomain( TEAMTALLY_TEXT_DOMAIN, TEAMTALLY_LANGUAGES_DIR );
	}

	/**
	 * Initialization routine
	 */
	protected function init() {
		$this->load_dependencies();
		$this->language_setup();
	}

	/**
	 * Loads and executes the class
	 */
	public static function run() {
		self::get_instance();
	}

}

Loader::run();