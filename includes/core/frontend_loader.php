<?php
/**
 * Created by PhpStorm.
 * User: Itanjaka Mandresi
 * Date: 15/12/2018
 * Time: 21:32
 */

namespace TEAMTALLY\Core;

use TEAMTALLY\System\Singleton;
use TEAMTALLY\System\Helper;

class Frontend_Loader extends Singleton {

	/**
	 * Enqueue theme styles and scripts
	 */
	public function action_wp_enqueue_scripts() {

		// Loads theme script
		$script_list = array(
			'css/frontend-style.css',
			'js/frontend-script.js',
		);

		Helper::str_enqueue_script_list( $script_list, TEAMTALLY_DEV_MODE );

	}


	/**
	 * Initialization
	 */
	protected function init() {

		// checks if we are in the front end
		if ( is_admin() ) {
			return;
		}

		// Enqueuing styles and scripts
		add_action( 'wp_enqueue_scripts', array( $this, 'action_wp_enqueue_scripts' ) );

	}

	/**
	 * Loader
	 */
	public static function load() {
		self::get_instance();
	}

}