<?php
/**
 * Created by PhpStorm.
 * User: Itanjaka Mandresi
 * Date: 13/12/2018
 * Time: 14:54
 */

namespace TEAMTALLY\Core\Admin;

use TEAMTALLY\System\Singleton;
use TEAMTALLY\System\Helper;

class Admin_Menu extends Singleton {

	/**
	 * WordPress ADMIN_MENU action
	 */
	public function action_admin_menu() {
		global $menu, $submenu;

		/**
		 * Main admin menu : TOP PROF Mada
		 */
		add_menu_page(
			'TOP PROF Mada - Administration',
			'TOP PROF Mada',
			'edit_courses',
			'topprofmada_main_menu',
			function () {
				return '<pre>test</pre>';
			},
			'dashicons-universal-access-alt',
			3
		);

		/**
		 * Submenu: 'Lister les cours'
		 */
		$submenu['topprofmada_main_menu'][400] = array(
			'Lister les cours',
			'edit_courses',
			'edit.php?post_type=topprofmada_cours'
		);

		/**
		 * Submenu: 'Ajouter un cours'
		 */
		$submenu['topprofmada_main_menu'][420] = array(
			'Ajouter un cours',
			'edit_courses',
			'post-new.php?post_type=topprofmada_cours'
		);

		/**
		 * Creates the submenu for the 'matiere' taxonomy
		 */
		$submenu['topprofmada_main_menu'][500] = array(
			'Matières',
			'manage_options',
			'/wp-admin/edit-tags.php?taxonomy=matiere'
		);

		/**
		 * Creates the submenu for the 'import matiere'
		 */
		add_submenu_page(
			'topprofmada_main_menu',
			'Gestionnaire de matières',
			'Importer des matières',
			'manage_options',
			'topprofmada_import_matieres',
			array( __NAMESPACE__ . '\Admin_Import_Matieres', 'main_page' )
		);

		unset( $submenu['edit.php?post_type=topprofmada_cours'] );

		remove_menu_page( 'edit.php?post_type=topprofmada_cours' );

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