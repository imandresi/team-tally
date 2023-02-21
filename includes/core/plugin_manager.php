<?php
/**
 * Created by PhpStorm.
 * User: Itanjaka Mandresi
 * Date: 21/02/2023
 * Time: 17:34
 */

namespace TEAMTALLY\Core;

use TEAMTALLY\System\Singleton;

class Plugin_Manager extends Singleton {

	/**
	 * Activation code
	 * @return void
	 */
	private function activate() {

	}

	/**
	 * Deactivation code
	 * @return void
	 */
	private function deactivate() {

	}

	/**
	 * Uninstall code
	 * @return void
	 */
	private function uninstall() {

	}

	/**
	 * Initialization
	 */
	protected function init() {
		register_activation_hook( TEAMTALLY_PLUGIN_ENTRY, array( $this, 'activate' ) );
		register_deactivation_hook( TEAMTALLY_PLUGIN_ENTRY, array( $this, 'deactivate' ) );
		register_uninstall_hook( TEAMTALLY_PLUGIN_ENTRY, array( $this, 'uninstall' ) );
	}

	/**
	 * Loader
	 */
	public static function setup() {
		self::get_instance();
	}

}