<?php
/**
 * Created by PhpStorm.
 * User: Itanjaka Mandresi
 * Date: 08/12/2018
 * Time: 18:56
 */

namespace TEAMTALLY;

use TEAMTALLY\System\Singleton;
use TEAMTALLY\System\Helper;

include_once( TEAMTALLY_INCLUDES_DIR . 'core/autoloader.php' );
include_once( TEAMTALLY_INCLUDES_DIR . 'system/singleton.php' );
include_once( TEAMTALLY_INCLUDES_DIR . 'system/helper.php' );

class Loader extends Singleton {

	static $hooks = '';


	/**
	 * Loads dependencies
	 */
	private function load_dependencies() {
		// Initializing class Autoloader
		Core\Autoloader::init();

	}

	/**
	 * Loads common classes
	 */
	private function load_common_classes() {

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

		// Loads common classes to admin and front
		$this->load_common_classes();

		// Loads frontend routines
		Front_Loader::load();

		// Loads admin routines
		Admin_Loader::load();

	}

	/**
	 * Loads and executes the class
	 */
	public static function run() {
		self::get_instance();
	}

}

Loader::run();