<?php
/**
 * Created by PhpStorm.
 * User: Itanjaka Mandresi
 * Date: 02/03/2023
 * Time: 11:32
 */

namespace TEAMTALLY\Controllers;

use TEAMTALLY\Core\Admin\Teams_List_Table;
use TEAMTALLY\Models\Leagues_Model;
use TEAMTALLY\Models\Teams_Model;
use TEAMTALLY\System\Helper;
use TEAMTALLY\System\Singleton;

class Teams_Controller extends Singleton {

	/** @var Teams_Controller $instance */
	private $instance;

	public $league;
	public $league_id;
	public $active_page;

	/**
	 * Checks if the current page is in a list of allowed pages
	 * and returns that page identification
	 *
	 * @return false|string
	 */
	public function get_active_page() {

		$allowed_page_request_list = array(

			'quick_edit_post' => array(
				'path'  => 'admin-ajax.php',
				'query' => array(
					'post_type'   => 'teamtally_teams',
					'screen'      => 'edit-teamtally_teams',
					'action'      => 'inline-save',
				)
			),

			'filter_teams' => array(
				'path'  => 'edit.php',
				'query' => array(
					'post_status'   => 'all',
					'post_type'     => 'teamtally_teams',
					'filter_action' => 'Filter'
				)
			),

			'bulk_delete' => array(
				'path'  => 'edit.php',
				'query' => array(
					'post_status' => 'all',
					'post_type'   => 'teamtally_teams',
					'action'      => 'trash'
				)
			),

			'bulk_edit' => array(
				'path'  => 'edit.php',
				'query' => array(
					'screen'    => 'edit-teamtally_teams',
					'bulk_edit' => Helper::VALUE_EMPTY_QUERY_PARAM,
				)
			),

			'delete_all_teams' => array(
				'path'  => 'edit.php',
				'query' => array(
					'post_status' => 'trash',
					'post_type'   => Teams_Model::TEAMS_POST_TYPE,
					'delete_all'  => Helper::VALUE_EMPTY_QUERY_PARAM,
				)
			),

			'teams_in_trash' => array(
				'path'  => 'edit.php',
				'query' => array(
					'post_status' => 'trash',
					'post_type'   => Teams_Model::TEAMS_POST_TYPE
				)
			),

			'list_draft_teams' => array(
				'path'  => 'edit.php',
				'query' => array(
					'post_status' => 'draft',
					'post_type'   => Teams_Model::TEAMS_POST_TYPE
				)
			),

			'delete_post' => array(
				'path'  => 'post.php',
				'query' => array(
					'post'   => Helper::VALUE_EMPTY_QUERY_PARAM,
					'action' => 'delete',
				)
			),

			'list_teams' => array(
				'path'  => 'edit.php',
				'query' => array(
					'post_type' => Teams_Model::TEAMS_POST_TYPE
				)
			),

			'add_new_team' => array(
				'path'  => 'post-new.php',
				'query' => array(
					'post_type' => Teams_Model::TEAMS_POST_TYPE
				)
			),

			'edit_post' => array(
				'path'  => 'post.php',
				'query' => array(
					'post'   => Helper::VALUE_EMPTY_QUERY_PARAM,
					'action' => 'edit',
				)
			),

			'update_post' => array(
				'path'  => 'post.php',
				'query' => array(
					'action' => 'editpost',
				)
			),

		);

		$result = false;

		foreach ( $allowed_page_request_list as $key => $allowed_request ) {
			$allowed_request['path'] = wp_make_link_relative( admin_url( $allowed_request['path'] ) );

			$found = Helper::compare_page_request_to( $allowed_request );
			if ( $found ) {
				$result = $key;
				break;
			}
		}

		return $result;

	}

	/**
	 * Initialization routine
	 */
	protected function init() {

		add_action( 'init', function () {
			$this->active_page = $this->get_active_page();

			if ( ! $this->active_page ) {
				return;
			}

			// sets body classes
			add_filter( 'admin_body_class', function ( $classes ) {
				$classes .= $this->active_page ? " teamtally__teams__{$this->active_page}" : '';

				return $classes;
			}, 10, 1 );

			// get info about the league if indicated
			$this->league_id = Helper::get_var( $_REQUEST['league_id'] );
			if ( $this->league_id ) {
				$this->league = Leagues_Model::get_league( $this->league_id );
			}

			// loading corresponding controller
			switch ( $this->active_page ) {
				case 'list_draft_teams':
				case 'list_teams':
				case 'teams_in_trash':
				case 'filter_teams':
				case 'quick_edit_post':
					Teams_List_Controller::run( $this->instance );
					break;

				case 'add_new_team':
				case 'edit_post':
				case 'update_post':
					Teams_Edit_Controller::run( $this->instance );
					break;

				case 'delete_post':
				case 'delete_all_teams':
				case 'bulk_edit':
				default:
			}

		} );

	}

	/**
	 * Entry point
	 *
	 * @return void
	 */
	public static function load() {
		$instance           = self::get_instance();
		$instance->instance = $instance;
	}
}