<?php
/**
 * Created by PhpStorm.
 * User: Itanjaka Mandresi
 * Date: 28/02/2023
 * Time: 06:21
 */

namespace TEAMTALLY\Controllers;

use TEAMTALLY\Core\Admin\Teams_List_Table;
use TEAMTALLY\Models\Generic_Model;
use TEAMTALLY\Models\Leagues_Model;
use TEAMTALLY\Models\Teams_Model;
use TEAMTALLY\System\Helper;
use TEAMTALLY\System\Singleton;
use TEAMTALLY\System\Template;
use TEAMTALLY\Views\Teams_View;
use WP_Post;
use WP_Term;

class Teams_Controller extends Singleton {

	private $league;
	private $league_id;
	private $active_page;

	private $last_post;  // used as a cache

	/** @var WP_Post */
	private $current_team;

	/**
	 * Checks if the current page is in a list of allowed pages
	 * and returns that page identification
	 *
	 * @return false|string
	 */
	public function get_active_page() {

		$allowed_page_request_list = array(

			'draft_post' => array(
				'path'  => '/wp-admin/edit.php',
				'query' => array(
					'post_status' => 'draft',
					'post_type'   => Teams_Model::TEAMS_POST_TYPE
				)
			),

			'delete_post' => array(
				'path'  => '/wp-admin/edit.php',
				'query' => array(
					'post_status' => 'trash',
					'post_type'   => Teams_Model::TEAMS_POST_TYPE
				)
			),

			'list_teams' => array(
				'path'  => '/wp-admin/edit.php',
				'query' => array(
					'post_type' => Teams_Model::TEAMS_POST_TYPE
				)
			),

			'add_new_team' => array(
				'path'  => '/wp-admin/post-new.php',
				'query' => array(
					'post_type' => Teams_Model::TEAMS_POST_TYPE
				)
			),

			'edit_post' => array(
				'path'  => '/wp-admin/post.php',
				'query' => array(
					'post'   => Helper::VALUE_EMPTY_QUERY_PARAM,
					'action' => 'edit',
				)
			),

		);

		$result = false;

		foreach ( $allowed_page_request_list as $key => $allowed_request ) {
			$found = Helper::compare_page_request_to( $allowed_request );
			if ( $found ) {
				$result = $key;
				break;
			}
		}

		return $result;

	}

	/**
	 * Proceed to various initialization if the active page is about edit team
	 *
	 * @return boolean
	 */
	public function init_if_current_page_is_edit_team() {
		// Checks if the post is a Team Post
		$post_id = Helper::get_var( $_GET['post'] );

		/** @var WP_Post $post */
		$post = get_post( $post_id );

		if ( $post->post_type != Teams_Model::TEAMS_POST_TYPE ) {
			return false;
		}

		// Checks if a league is associated to the post
		$terms = get_the_terms( $post_id, Leagues_Model::LEAGUES_TAXONOMY_NAME );
		if ( ! is_wp_error( $terms ) && is_array( $terms ) ) {
			$this->league    = $terms[0];
			$this->league_id = $this->league->term_id;
		}

		$this->active_page  = 'edit_team';
		$this->current_team = $post;

		return true;

	}

	/**
	 * @return void
	 */
	public function init_if_current_page_is_list_teams() {

		if ( $this->active_page != 'list_teams' ) {
			return;
		}

		// filter listed teams by league
		add_filter( 'wp_list_table_class_name', function ( $class_name, $args ) {
			// uses another list table instead of WP_Posts_List_Table
			// in order to implement filtering by league
			$class_name = Teams_List_Table::class;

			return $class_name;
		}, 10, 2 );

		// customize the teams listing
		$this->customize_teams_list_table();

	}

	/**
	 * Proceed to various initialization according to active page
	 *
	 * @return void
	 */
	public function init_on_active_page() {

		// initialization is done only if in the list of allowed pages
		$this->active_page = $this->get_active_page();

		// sets body classes
		add_filter( 'admin_body_class', function ( $classes ) {
			$classes .= $this->active_page ? " teamtally__teams__{$this->active_page}" : '';

			return $classes;
		}, 10, 1 );

		// customization
		switch ( $this->active_page ) {

			case 'list_teams':
			case 'add_new_team':
				// get info about the league if indicated
				$this->league_id = Helper::get_var( $_GET['league_id'] );

				if ( $this->league_id ) {
					$this->league = Leagues_Model::get_league( $this->league_id );
				}

				$this->init_if_current_page_is_list_teams();

				break;

			case 'edit_post':
				$post_is_edit_team = $this->init_if_current_page_is_edit_team();

				if ( ! $post_is_edit_team ) {
					return;
				}

				break;

			case 'draft_post':
			case 'delete_post':
			default:
				return;
		}

		// league_id has to be provided if we want to view the list of teams about a league
		// otherwise restrict access
		if ( ( $this->active_page == 'list_teams' ) && ( ! $this->league_id ) ) {
			$url = add_query_arg( array(
				'page' => 'teamtally_leagues_view'
			), admin_url( 'admin.php' ) );

			wp_redirect( $url );
			exit;
		}

	}

	/**
	 * Customize the teams page
	 *
	 * @return void
	 */
	public function customize_teams_list_page() {
		global $post_type_object, $post_new_file;

		if ( $this->active_page != 'list_teams' ) {
			return;
		}

		// title initialization
		$league_name                    = $this->league['data']['league_name'];
		$new_title                      = sprintf(
			__( 'TEAMS MANAGEMENT (League : %s)', TEAMTALLY_TEXT_DOMAIN ),
			$league_name
		);
		$post_type_object->labels->name = $new_title;

		// button add new
		$post_new_file = add_query_arg( array(
			'post_type' => 'teamtally_teams',
			'league_id' => $this->league['data']['term_id'],
		), 'post-new.php' );

	}

	/**
	 * Customization of the 'add new / edit team' page
	 *
	 * @return void
	 */
	public function customize_add_edit_team_page() {
		global $post_type_object;

		if ( ! in_array( $this->active_page, array( 'add_new_team', 'edit_team' ) ) ) {
			return;
		}

		// sets the label for the 'team name' input
		add_filter( 'enter_title_here', function () {
			return __( 'Enter the team name', TEAMTALLY_TEXT_DOMAIN );
		}, 10, 2 );

		// init the nickname field and the history label
		add_action( 'edit_form_after_title', function () {
			$team_nickname_field_name = Teams_Model::TEAMS_FIELD_NICKNAME;
			$team_nickname            = '';

			if ( $this->current_team ) {
				$team_nickname = $this->current_team->$team_nickname_field_name;
			}

			Template::pparse( 'admin/teams/edit_form.php', array(
				'team_nickname' => $team_nickname,
			) );
		} );

	}

	/**
	 * Adds additional columns to teams list table
	 *
	 * @return void
	 */
	public function customize_teams_list_table() {

		if ( $this->active_page != 'list_teams' ) {
			return;
		}

		// Customize the columns header
		add_filter( 'manage_' . Teams_Model::TEAMS_POST_TYPE . '_posts_columns', function ( $columns ) {
			$columns = array(
				'cb'       => $columns['cb'],
				'title'    => __( 'Name', TEAMTALLY_TEXT_DOMAIN ),
				'nickname' => __( 'Nickname', TEAMTALLY_TEXT_DOMAIN ),
				'history'  => __( 'History', TEAMTALLY_TEXT_DOMAIN ),
				'logo'     => __( 'Logo', TEAMTALLY_TEXT_DOMAIN ),
				'date'     => $columns['date'],
			);

			return $columns;
		} );

		// Customize the columns content
		// manage_teamtally_teams_posts_custom_column
		$action_name = 'manage_' . Teams_Model::TEAMS_POST_TYPE . '_posts_custom_column';
		add_action( $action_name, function ( $column, $post_id ) {

			$last_post_id = Helper::get_var( $this->last_post['data']['ID'] );
			$do_load_post = ( $last_post_id != $post_id );

			if ( $do_load_post ) {
				$this->last_post = Teams_Model::get_team( $post_id );
			}

			switch ( $column ) {
				case 'nickname':
					print $this->last_post['data'][ Teams_Model::TEAMS_FIELD_NICKNAME ];
					break;

				case 'logo':
					$url = Helper::get_var( $this->last_post['data'][ Teams_Model::TEAMS_FIELD_LOGO ]['URL'], '' );
					print <<<EOT
<div class="teams-logo" style="background-image: url({$url})"></div>
EOT;
					break;

				case 'history':
					$excerpt = get_the_excerpt( $this->last_post['raw'] );
					print $excerpt;
					break;
			}

		}, 10, 2 );

	}

	/**
	 * Called by 'add_meta_boxes_teamtally_teams'
	 *
	 * @return void
	 */
	public function new_team_page_meta_boxes() {
		add_meta_box(
			'teamtaly_league_id',
			'League of the team',
			array( $this, 'new_team_page_meta_box_league_content' ),
			Teams_Model::TEAMS_POST_TYPE,
			'side',
			'low',
			null
		);
	}

	/**
	 * Content of the meta box league of new team page
	 * @return void
	 */
	public function new_team_page_meta_box_league_content( $post ) {

		// gets the list of all leagues to be displayed in a combobox
		$leagues = Leagues_Model::get_all_leagues();
		Teams_View::new_team_page_meta_box_league_content( $leagues, $this->league_id );
	}

	/**
	 * Called when the form containing the team data is saved
	 *
	 * @return void
	 */
	public function save_posted_team_data( $post_id, $post ) {

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// save nickname as post_meta
		$team_nickname = Helper::get_var( $_POST['team_nickname'], '' );
		update_post_meta( $post_id, Teams_Model::TEAMS_FIELD_NICKNAME, $team_nickname );

		// save league as taxonomy term
		$teams_league = Helper::get_var( $_POST['teams_league'], '' );
		wp_set_post_terms(
			$post_id,
			$teams_league,
			Leagues_Model::LEAGUES_TAXONOMY_NAME,
			false
		);

	}

	/**
	 * Initialization for admin interface
	 *
	 * @return void
	 */
	private function admin_init() {
		add_action( 'init', array( $this, 'init_on_active_page' ) );
		add_action( 'admin_head-edit.php', array( $this, 'customize_teams_list_page' ) );
		add_action( 'admin_head-post-new.php', array( $this, 'customize_add_edit_team_page' ) );
		add_action( 'admin_head-post.php', array( $this, 'customize_add_edit_team_page' ) );

		// hook for leagues meta box
		add_action( 'add_meta_boxes_' . Teams_Model::TEAMS_POST_TYPE, array(
			$this,
			'new_team_page_meta_boxes'
		) );

		// hook when the post is being saved
		add_action( 'save_post_' . Teams_Model::TEAMS_POST_TYPE, array(
			$this,
			'save_posted_team_data'
		), 10, 2 );

	}

	/**
	 * Initialization routine
	 */
	protected function init() {
		if ( is_admin() ) {
			$this->admin_init();
		}
	}

	/**
	 * Loads and executes the class
	 */
	public static function load() {
		self::get_instance();
	}

}