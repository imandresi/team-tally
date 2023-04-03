<?php
/**
 * Created by PhpStorm.
 * User: Itanjaka Mandresi
 * Date: 01/04/2023
 * Time: 18:13
 */

namespace TEAMTALLY\Controllers;

use Elementor\Core\Admin\Admin;
use TEAMTALLY\Core\Admin\Plugin_Data;
use TEAMTALLY\Models\Leagues_Model;
use TEAMTALLY\Models\Teams_Model;
use TEAMTALLY\System\Admin_Notices;
use TEAMTALLY\System\Helper;
use TEAMTALLY\Views\Import_View;

class Import_Controller {

	const TEAMTALLY_NONCE = 'teamtally_import';

	/**
	 * Process to the demo import
	 *
	 * @return array
	 */
	private static function import_demo() {
		$notice_type = Admin_Notices::ADMIN_NOTICE_ERROR;

		$basename = 'teamtally_demo_data.zip';
		$filename = TEAMTALLY_DEMO_DIR . $basename;

		if ( ! file_exists( $filename ) ) {
			$demo_basename  = basename( TEAMTALLY_DEMO_DIR );
			$notice_message = sprintf( __(
				'Operation aborted. There is no DEMO data file available in the "%s" folder.',
				TEAMTALLY_TEXT_DOMAIN
			), $demo_basename );
		} else {
			$import_status = Plugin_Data::import( $filename );
			self::parse_import_status( $import_status, $notice_message, $notice_type );
		}

		return array(
			'notice_type'    => $notice_type,
			'notice_message' => $notice_message,
		);

	}

	/**
	 * Process to the archive import
	 *
	 * @return array
	 */
	private static function import_archive() {
		$notice_type = Admin_Notices::ADMIN_NOTICE_ERROR;
		$notice_message = __( 'An error occurred during import', TEAMTALLY_TEXT_DOMAIN );

		$archive_basename = $_POST['import_archive'];
		$archive_filename = TEAMTALLY_EXPORTS_DIR . $archive_basename;

		if (file_exists($archive_filename)) {
			$import_status = Plugin_Data::import( $archive_filename );
			self::parse_import_status( $import_status, $notice_message, $notice_type );
		}

		return array(
			'notice_type'    => $notice_type,
			'notice_message' => $notice_message,
		);

	}

	/**
	 * Initializes the notice type and message according to the import status
	 *
	 * Used by 'Admin_Notices' class
	 *
	 * @param $import_status
	 * @param $notice_message
	 * @param $notice_type
	 *
	 * @return void
	 */
	private static function parse_import_status( $import_status, &$notice_message, &$notice_type ) {
		$is_success     = $import_status['success'];
		$notice_message = nl2br( $import_status['message'] );
		$notice_type    = $is_success ? Admin_Notices::ADMIN_NOTICE_SUCCESS : Admin_Notices::ADMIN_NOTICE_ERROR;
	}

	/**
	 * Initializes the 'Admin_Notices' error message according to the file upload error type
	 *
	 * @param $error
	 * @param $notice_message
	 * @param $notice_type
	 *
	 * @return void
	 */
	private static function parse_file_upload_error( $error, &$notice_message, &$notice_type ) {
		$notice_message = __( 'An error occured when uploading the file', TEAMTALLY_TEXT_DOMAIN );
		$notice_type    = Admin_Notices::ADMIN_NOTICE_ERROR;

		switch ( $error ) {
			case UPLOAD_ERR_OK:
				$notice_message = __( 'The file is uploaded successfully.', TEAMTALLY_TEXT_DOMAIN );
				$notice_type    = Admin_Notices::ADMIN_NOTICE_SUCCESS;
				break;

			case UPLOAD_ERR_INI_SIZE:
			case UPLOAD_ERR_FORM_SIZE:
				$notice_message = __(
					'The uploaded file exceeds the maximum allowed.',
					TEAMTALLY_TEXT_DOMAIN
				);
				break;

			case UPLOAD_ERR_PARTIAL:
				$notice_message = __(
					'The uploaded file was only partially uploaded.',
					TEAMTALLY_TEXT_DOMAIN
				);
				break;

			case UPLOAD_ERR_NO_FILE:
				$notice_message = __(
					'No file was uploaded.',
					TEAMTALLY_TEXT_DOMAIN
				);
				break;

			case UPLOAD_ERR_NO_TMP_DIR:
				$notice_message = __(
					'Missing a temporary folder',
					TEAMTALLY_TEXT_DOMAIN
				);
				break;

			case UPLOAD_ERR_CANT_WRITE:
				$notice_message = __(
					'Failed to write file to disk.',
					TEAMTALLY_TEXT_DOMAIN
				);
				break;

			case UPLOAD_ERR_EXTENSION:
				$notice_message = __(
					'A PHP extension stopped the file upload.',
					TEAMTALLY_TEXT_DOMAIN
				);
				break;
		}

	}

	/**
	 * Process to the importation of data from the file upload
	 *
	 * @return array
	 */
	private static function import_upload() {
		$notice_type    = Admin_Notices::ADMIN_NOTICE_ERROR;
		$notice_message = __( 'The upload failed. Pleasy try again.', TEAMTALLY_TEXT_DOMAIN );

		$files = Helper::get_var( $_FILES['import_data'] );
		if ( $files ) {
			wp_mkdir_p(TEAMTALLY_TEMP_DIR);
			$uploaded_file = TEAMTALLY_TEMP_DIR . basename( $files['name'] );
			$upload_error  = $files['error'];

			if ( $upload_error ) {
				self::parse_file_upload_error( $upload_error, $notice_message, $notice_type );
			}

			if ( move_uploaded_file( $files['tmp_name'], $uploaded_file ) ) {
				$import_status = Plugin_Data::import( $uploaded_file );
				self::parse_import_status( $import_status, $notice_message, $notice_type );
				@unlink( $uploaded_file );
			}
		}

		return array(
			'notice_type'    => $notice_type,
			'notice_message' => $notice_message,
		);

	}

	/**
	 * Do the import process from the import form
	 *
	 * @return void
	 */
	public static function process_import() {
		if ( isset( $_POST['action'] ) && ( $_POST['action'] == 'do-import' ) ) {
			if ( ! check_admin_referer( self::TEAMTALLY_NONCE, '_nonce' ) ) {
				die ( __( 'Operation not allowed. Please log in and try again.', TEAMTALLY_TEXT_DOMAIN ) );
			}

			$import_type = $_POST['import_type'];

			$status = array(
				'notice_message' => __( 'An error occurred during import', TEAMTALLY_TEXT_DOMAIN ),
				'notice_type'    => Admin_Notices::ADMIN_NOTICE_ERROR,
			);

			// checks if previous data have to be cleared
			$clear_previous_data = Helper::get_var($_POST['clear_previous_data']);
			if ($clear_previous_data) {
				Teams_Model::delete_all_teams();
				Leagues_Model::delete_all_leagues();
			}

			// importing data according to import type
			switch ( $import_type ) {
				case 'demo':
					$status = self::import_demo();
					break;

				case 'archive':
					$status = self::import_archive();
					break;

				case 'upload':
					$status = self::import_upload();
					break;
			}

			$notice_message = '<b>TEAM TALLY:</b><br>' . $status['notice_message'];
			$notice_type    = $status['notice_type'];

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
	 * Returns a list of archives filenames stored in the TEAMTALLY_EXPORTS_DIR
	 *
	 * @return array|false
	 */
	private static function get_archive_list() {
		$result = array();

		$files = @scandir( TEAMTALLY_EXPORTS_DIR, SCANDIR_SORT_ASCENDING );
		if ( ! $files ) {
			return false;
		}

		$regexp = "/^teamtally_data_export_(\d{4})(\d{2})(\d{2})_(\d{2})(\d{2})(\d{2})\.zip$/is";

		if ( $files ) {
			foreach ( $files as $file ) {
				if ( preg_match( $regexp, $file, $matches ) ) {
					$year   = $matches[1];
					$month  = $matches[2];
					$day    = $matches[3];
					$hour   = $matches[4];
					$minute = $matches[5];
					$second = $matches[6];

					$date      = mktime( $hour, $minute, $second, $month, $day, $year );
					$full_date = date( "l, F j, Y - h:i:s A", $date );
					$result[]  = array(
						'caption'  => $full_date,
						'basename' => $file
					);
				}
			}
		}

		return $result;

	}

	/**
	 * Displays the import page
	 *
	 * @return void
	 */
	public static function import_page() {

		// build archive data here
		$archive_list = self::get_archive_list();
		$nonce        = wp_create_nonce( self::TEAMTALLY_NONCE );

		Import_View::display_import_page( array(
			'nonce'   => $nonce,
			'archive' => $archive_list,
		) );

	}

	/**
	 * Initialization for admin interface
	 *
	 * @return void
	 */
	private static function admin_init() {
		add_action( 'init', array( self::class, 'process_import' ) );
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