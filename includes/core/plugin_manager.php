<?php
/**
 * Created by PhpStorm.
 * User: Itanjaka Mandresi
 * Date: 21/02/2023
 * Time: 17:34
 */

namespace TEAMTALLY\Core;

use TEAMTALLY\Models\Leagues_Model;
use TEAMTALLY\Models\Teams_Model;
use TEAMTALLY\System\Helper;

class Plugin_Manager {

	/**
	 * Activation code
	 * @return void
	 */
	public static function activate() {

	}

	/**
	 * Deactivation code
	 * @return void
	 */
	public static function deactivate() {

	}

	/**
	 * Deactivation code
	 * @return void
	 */
	public static function uninstall() {
		Teams_Model::initialize_data_model();
		Leagues_Model::initialize_data_model();

		Teams_Model::delete_all_teams();
		Leagues_Model::delete_all_leagues(true);
	}

	/**
	 * Initialization
	 */
	public static function init() {
		register_activation_hook( TEAMTALLY_PLUGIN_ENTRY, array( self::class, 'activate' ) );
		register_deactivation_hook( TEAMTALLY_PLUGIN_ENTRY, array( self::class, 'deactivate' ) );
		register_uninstall_hook( TEAMTALLY_PLUGIN_ENTRY, array( self::class, 'uninstall' ) );
	}

}