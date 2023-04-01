<?php
/**
 * Created by PhpStorm.
 * User: Itanjaka Mandresi
 * Date: 27/03/2023
 * Time: 18:12
 */

namespace TEAMTALLY\Core\Admin;

use SimpleXMLElement;
use TEAMTALLY\Models\Leagues_Model;
use TEAMTALLY\Models\Teams_Model;
use TEAMTALLY\System\Helper;
use TEAMTALLY\System\Singleton;
use WP_Query;

class Plugin_Data extends Singleton {

	const LEAGUES_DATA_FILENAME = 'leagues.json';
	const TEAMS_DATA_FILENAME = 'teams.json';
	const MEDIA_DATA_FILENAME = 'media.json';

	private $work_dir = '';
	private $media_dir = '';
	private $media_data = array();

	private $import = array();


	/**
	 * Imports team and league data from a zip file
	 *
	 * @param $zip_filename
	 *
	 * @return array
	 */
	public static function import( $zip_filename ) {
		@include_once( ABSPATH . 'wp-admin/includes/image.php' );

		$instance         = self::get_instance();
		$instance->import = array();

		$instance->work_dir = TEAMTALLY_UPLOAD_DIR . 'tmp/' . uniqid() . '/';
		wp_mkdir_p( $instance->work_dir );

		Helper::unzip_file( $zip_filename, $instance->work_dir );

		$status  = $instance->_import_media();
		$message = $status['message'];

		if ( $status['success'] ) {
			$status  = $instance->_import_leagues();
			$message .= "\n" . $status['message'];
		}

		if ( $status['success'] ) {
			$status  = $instance->_import_teams();
			$message .= "\n" . $status['message'];
		}

		// deletes the working folder
		Helper::delete_directory( $instance->work_dir, TEAMTALLY_UPLOAD_DIR );

		$status = array(
			"success" => $status['success'],
			"message" => $message,
		);

		return $status;

	}

	/**
	 * Exports the plugin data and compresses it
	 *
	 * @return array
	 */
	public static function export( $zip_filename = '' ) {
		$instance = self::get_instance();

		// prepare working folder
		$instance->work_dir = TEAMTALLY_UPLOAD_DIR . 'tmp/' . uniqid() . '/';
		wp_mkdir_p( $instance->work_dir );

		$instance->_init_media();
		$instance->_export_leagues();
		$instance->_export_teams();
		$instance->_save_media();

		// compresses the folder
		$export_dir = 'exports/';

		if ( ! $zip_filename ) {
			$suffix = date('_Ymd_His');
			$zip_basename = "teamtally_data_export{$suffix}.zip";
			$zip_dir      = TEAMTALLY_UPLOAD_DIR . $export_dir;
			wp_mkdir_p( $zip_dir );

			$zip_filename = $zip_dir . $zip_basename;
		} else {
			$zip_basename = basename( $zip_filename );
		}

		$zip_url = TEAMTALLY_UPLOAD_URL . $export_dir . $zip_basename;

		Helper::create_zip_file( $instance->work_dir, $zip_filename );

		$result = array(
			'zip_filename' => $zip_filename,
			'zip_url'      => $zip_url,
		);

		// deletes the working folder
		Helper::delete_directory( $instance->work_dir, TEAMTALLY_UPLOAD_DIR );

		return $result;

	}


	/**
	 * Loads a json filename and returns its decoded data
	 *
	 * @param $filename
	 * @param $data
	 *
	 * @return void
	 */
	private function _load_json_data( $filename, &$data ) {
		$data = null;

		if ( ! file_exists( $filename ) ) {
			return;
		}

		$raw_data = file_get_contents( $filename );
		if ( $raw_data === false ) {
			return;
		}

		$data = json_decode( $raw_data, true );

	}

	/**
	 * Imports 'media.json' into the media library
	 *
	 * Internally used by import()
	 *
	 * @return array
	 */
	private function _import_media() {
		$result = array(
			'success' => false,
			'message' => __( 'MEDIA IMPORT ERROR', TEAMTALLY_TEXT_DOMAIN ),
		);

		$this->import['media'] = array();

		$this->_load_json_data( $this->work_dir . 'media.json', $media_data );

		if ( ! $media_data ) {
			$result['message'] = __( 'MEDIA IMPORT ERROR: Unable to load the "media.json" file.', TEAMTALLY_TEXT_DOMAIN );

			return $result;
		}

		$this->import['media'] = $media_data;

		// init source dir
		$source_dir = $this->work_dir . 'media/';
		if ( ! file_exists( $source_dir ) ) {
			$result['message'] = __( 'MEDIA IMPORT ERROR: "./media" folder does not exist.', TEAMTALLY_TEXT_DOMAIN );

			return $result;
		}

		// prepares imported media destination folder
		$destination_dir = TEAMTALLY_UPLOAD_DIR . 'imports/' . date( 'Ymd_His' ) . '/';
		wp_mkdir_p( $destination_dir );

		if ( is_array( $media_data ) ) {
			foreach ( $media_data as $media_id => $media_info ) {

				// prepares the destination media filename
				$media_filename = $media_info['original_filename'];
				if ( file_exists( $destination_dir . $media_filename ) ) {
					$media_filename = preg_replace(
						"/\.\w+$/is",
						"_" . uniqid() . "$0",
						$media_filename
					);
				}
				$media_filename = $destination_dir . $media_filename;

				// copies the media into the destination directory
				$source_filename = $source_dir . $media_info['filename'];
				if ( ! file_exists( $source_filename ) ) {
					continue;
				}

				copy( $source_filename, $media_filename );

				// inserts the media into the media library
				$attachment = array(
					'post_mime_type' => mime_content_type( $media_filename ),
					'post_title'     => $media_info['title'],
					'post_content'   => $media_info['description'],
					'post_status'    => 'inherit'
				);

				$attachment_id   = wp_insert_attachment( $attachment, $media_filename );
				$attachment_data = wp_generate_attachment_metadata( $attachment_id, $media_filename );

				wp_update_attachment_metadata( $attachment_id, $attachment_data );

				// updates the media data
				$this->import['media'][ $media_id ]['new_filepath'] = $media_filename;
				$this->import['media'][ $media_id ]['new_media_id'] = $attachment_id;

			}

			$result = array(
				"success" => true,
				"message" => __( 'Media library imported successfully', TEAMTALLY_TEXT_DOMAIN )
			);

		}

		return $result;

	}

	/**
	 * Imports 'leagues.json' and builds leagues taxonomies
	 *
	 * Internally used by import()
	 *
	 * @return array
	 */
	private function _import_leagues() {
		$result = array(
			"success" => false,
			"message" => __( 'LEAGUE IMPORT ERROR', TEAMTALLY_TEXT_DOMAIN )
		);

		$this->import['leagues'] = array();

		$this->_load_json_data( $this->work_dir . 'leagues.json', $leagues_data );

		if ( ! $leagues_data ) {
			$result['message'] = __( 'LEAGUE IMPORT ERROR: Unable to load the "leagues.json" file.', TEAMTALLY_TEXT_DOMAIN );

			return $result;
		}

		$this->import['leagues'] = $leagues_data;

		foreach ( $leagues_data as $league_id => $league_info ) {
			$media_id     = $league_info[ Leagues_Model::LEAGUES_FIELD_PHOTO ];
			$new_media_id = Helper::get_var( $this->import['media'][ $media_id ]['new_media_id'] );

			if ( ! $new_media_id ) {
				continue;
			}

			$league_info[ Leagues_Model::LEAGUES_FIELD_PHOTO ] = $new_media_id;

			$new_league_id = Leagues_Model::update_league( $league_info );
			if ( ! $new_league_id ) {
				continue;
			}

			$this->import['leagues'][ $league_id ]['new_league_id'] = $new_league_id;

		}

		$result = array(
			"success" => true,
			"message" => __( 'Leagues imported successfully', TEAMTALLY_TEXT_DOMAIN )
		);

		return $result;

	}

	/**
	 * Imports 'teams.json' and creates all corresponding posts
	 *
	 * Internally used by import()
	 *
	 * @return array
	 */
	private function _import_teams() {
		$result = array(
			"success" => false,
			"message" => __( 'TEAMS IMPORT ERROR', TEAMTALLY_TEXT_DOMAIN )
		);

		$this->import['teams'] = array();

		$this->_load_json_data( $this->work_dir . 'teams.json', $teams_data );

		if ( ! $teams_data ) {
			$result['message'] = __( 'TEAMS IMPORT ERROR: Unable to load the "teams.json" file.', TEAMTALLY_TEXT_DOMAIN );

			return $result;
		}

		$this->import['teams'] = $teams_data;

		foreach ( $teams_data as $team_id => $team_info ) {

			// managing post thumbnail
			$logo_id     = $team_info[ Teams_Model::TEAMS_FIELD_LOGO ];
			$new_logo_id = Helper::get_var(
				$this->import['media'][ $logo_id ]['new_media_id'],
				0
			);

			if ( ! $new_logo_id ) {
				$result['message'] = sprintf( __(
					"TEAMS IMPORT ERROR: An error occurred when loading the team logo #%s.",
					TEAMTALLY_TEXT_DOMAIN
				), $logo_id );

				return $result;
			}

			$team_info[ Teams_Model::TEAMS_FIELD_LOGO ] = $new_logo_id;

			// managing league
			$league_id     = $team_info['league_id'];
			$new_league_id = Helper::get_var(
				$this->import['leagues'][ $league_id ]['new_league_id'],
				0
			);

			$team_name = $team_info[ Teams_Model::TEAMS_FIELD_NAME ];

			if ( ! $new_league_id ) {
				$result['message'] = sprintf( __(
					'TEAMS IMPORT ERROR: An error occurred when assigning the league #%s to the team - %s.',
					TEAMTALLY_TEXT_DOMAIN
				), $league_id, $team_name );

				return $result;
			}

			$team_info['league_id'] = $new_league_id;

			$new_team_id = Teams_Model::update_team( $team_info );

			if ( ! $new_team_id ) {
				$result['message'] = sprintf( __(
					'TEAMS IMPORT ERROR: An error occurred when saving the team - [ %s ].',
					TEAMTALLY_TEXT_DOMAIN
				), $team_name );

				return $result;
			}

		}

		$result = array(
			"success" => true,
			"message" => __( 'Teams imported successfully', TEAMTALLY_TEXT_DOMAIN )
		);

		return $result;

	}

	/**
	 * Prepares media environment for exporting
	 *
	 * @return void
	 */
	private function _init_media() {
		$this->media_dir = $this->work_dir . 'media/';
		wp_mkdir_p( $this->media_dir );

		$this->media_data = array();

	}

	/**
	 * stores infos about media
	 *
	 * @param $media_id
	 *
	 * @return false
	 */
	private function _add_media( $media_id ) {
		$media_filename = get_attached_file( $media_id, true );

		if ( ! $media_filename ) {
			return false;
		}

		if ( isset( $this->media_data[ $media_id ] ) ) {
			return $media_id;
		}

		// copy the media inside the media dir
		$ext            = pathinfo( $media_filename, PATHINFO_EXTENSION );
		$media_basename = basename( $media_filename, ".{$ext}" );
		$ext            = strtolower( $ext );
		$new_filename   = "{$media_basename}_{$media_id}.{$ext}";

		$dest_filename = $this->media_dir . $new_filename;
		@copy( $media_filename, $dest_filename );

		// gets media metadata
		$media_post = get_post( $media_id );

		$title       = '';
		$description = '';
		if ( $media_post ) {
			$title       = $media_post->post_title;
			$description = $media_post->post_content;
		}

		// builds metadata
		$this->media_data[ $media_id ] = array(
			'filename'          => $new_filename,
			'original_filename' => basename( $media_filename ),
			'title'             => $title,
			'description'       => $description,
		);

		return $media_id;

	}

	/**
	 * Saves all the media array data into a json filename
	 *
	 * @return void
	 */
	private function _save_media() {
		$json_filename = $this->work_dir . self::MEDIA_DATA_FILENAME;
		$encoded_data          = json_encode( $this->media_data );

		file_put_contents(
			$json_filename,
			$encoded_data
		);

	}

	/**
	 * Exports the leagues
	 *
	 * @return void
	 */
	private function _export_leagues() {

		$leagues = Leagues_Model::get_all_leagues();
		$data    = array();

		foreach ( $leagues as $league ) {
			$term_id         = $league['data']['term_id'];
			$league_photo_id = Helper::get_var( $league['data'][ Leagues_Model::LEAGUES_FIELD_PHOTO ]['id'] );

			$data[ $term_id ] = array(
				Leagues_Model::LEAGUES_FIELD_NAME    => Helper::get_var( $league['data'][ Leagues_Model::LEAGUES_FIELD_NAME ] ),
				Leagues_Model::LEAGUES_FIELD_COUNTRY => Helper::get_var( $league['data'][ Leagues_Model::LEAGUES_FIELD_COUNTRY ] ),
				Leagues_Model::LEAGUES_FIELD_PHOTO   => $this->_add_media( $league_photo_id ),
			);
		}

		// save the json file
		$json_filename = $this->work_dir . self::LEAGUES_DATA_FILENAME;
		file_put_contents(
			$json_filename,
			json_encode( $data )
		);

	}

	/**
	 * Export the teams
	 *
	 * @return void
	 */
	private function _export_teams() {

		$args = array(
			'post_type'   => Teams_Model::TEAMS_POST_TYPE,
			'post_status' => 'publish',
			'nopaging'    => true,
		);

		$data  = array();
		$query = new WP_Query( $args );

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$post = $query->post;
				$team = Teams_Model::get_team( $post );

				if ( ! $team ) {
					continue;
				}

				$team_id = Helper::get_var( $team['data']['ID'] );
				if ( ! $team_id ) {
					continue;
				}

				$team_photo_id = Helper::get_var( $team['data'][ Teams_Model::TEAMS_FIELD_LOGO ]['ID'] );

				$data[ $team_id ] = array(
					Teams_Model::TEAMS_FIELD_NAME     => Helper::get_var( $team['data'][ Teams_Model::TEAMS_FIELD_NAME ] ),
					Teams_Model::TEAMS_FIELD_NICKNAME => Helper::get_var( $team['data'][ Teams_Model::TEAMS_FIELD_NICKNAME ] ),
					Teams_Model::TEAMS_FIELD_HISTORY  => Helper::get_var( $team['data'][ Teams_Model::TEAMS_FIELD_HISTORY ] ),
					Teams_Model::TEAMS_FIELD_LOGO     => $this->_add_media( $team_photo_id ),
					'league_id'                       => Helper::get_var( $team['data']['league_id'] )
				);

			}
		}

		wp_reset_postdata();

		// save the json file
		$json_filename = $this->work_dir . self::TEAMS_DATA_FILENAME;
		file_put_contents(
			$json_filename,
			json_encode( $data )
		);

	}

	/**
	 * Initialization
	 */
	protected function init() {

	}

	/**
	 * Executes the class
	 */
	public static function load() {
		self::get_instance();
	}


}