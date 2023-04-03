<?php
/**
 * Created by PhpStorm.
 * User: Itanjaka Mandresi
 * Date: 03/04/2023
 * Time: 18:33
 */

namespace TEAMTALLY\Controllers;

use TEAMTALLY\Core\Admin\Plugin_Data;
use TEAMTALLY\System\Admin_Notices;
use TEAMTALLY\System\Helper;
use TEAMTALLY\Views\Export_View;

class Export_Controller {

	const TEAMTALLY_NONCE = 'teamtally_export';

	/**
	 * Do the export process
	 *
	 * @return void
	 */
	public static function process_export() {
		if ( isset( $_POST['action'] ) && ( $_POST['action'] == 'do-export' ) ) {
			if ( ! check_admin_referer( self::TEAMTALLY_NONCE, '_nonce' ) ) {
				die ( __( 'Operation not allowed. Please log in and try again.', TEAMTALLY_TEXT_DOMAIN ) );
			}

			$export_data  = Plugin_Data::export();
			$zip_filename = Helper::get_var( $export_data['zip_filename'] );
			$zip_basename = basename( $zip_filename );
			$zip_url      = Helper::get_var( $export_data['zip_url'] );

			$notice_message = sprintf( __(
				'<b>TEAM TALLY:</b> Data exported. You can download it through this <a href="%s">link</a>',
				TEAMTALLY_TEXT_DOMAIN ), $zip_url
			);

			$notice_type = Admin_Notices::ADMIN_NOTICE_SUCCESS;

			Admin_Notices::set_message(
				$notice_message,
				$notice_type,
				true,
				null,
				false
			);

		}
	}

	/**
	 * Displays the export page
	 *
	 * @return void
	 */
	public static function export_page() {
		$nonce = wp_create_nonce( self::TEAMTALLY_NONCE );
		Export_View::display_export_page( array(
			'nonce' => $nonce,
		) );

	}


	/**
	 * Initialization for admin interface
	 *
	 * @return void
	 */
	private static function admin_init() {
		add_action( 'init', array( self::class, 'process_export' ) );
	}

	/**
	 * Initialization
	 *
	 * @return void
	 */
	public static function init() {
		if ( is_admin() ) {
			self::admin_init();
		}

	}
}