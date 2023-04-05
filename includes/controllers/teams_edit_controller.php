<?php
/**
 * Created by PhpStorm.
 * User: Itanjaka Mandresi
 * Date: 02/03/2023
 * Time: 11:18
 */

namespace TEAMTALLY\Controllers;

use TEAMTALLY\Models\Leagues_Model;
use TEAMTALLY\Models\Teams_Model;
use TEAMTALLY\System\Admin_Notices;
use TEAMTALLY\System\Helper;
use TEAMTALLY\System\Singleton;
use TEAMTALLY\System\Template;
use TEAMTALLY\Views\Teams_View;
use WP_Post;

class Teams_Edit_Controller extends Singleton {

	/** @var WP_Post */
	private $current_team;

	/** @var Teams_Controller $teams */
	private $teams;


	/**
	 * Customization of the 'add new / edit team' page
	 *
	 * @return void
	 */
	public function customize_add_edit_team_page() {
		global $post_type_object, $post_new_file;

		// modify the url for the button 'Add New Team'
		if ( $this->teams->league_id ) {
			$post_new_file .= "&league_id={$this->teams->league_id}";
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
	 * Called by 'add_meta_boxes_teamtally_teams'
	 *
	 * @return void
	 */
	public function new_team_page_meta_boxes() {
		add_meta_box(
			'teamtaly_league_id',
			__( 'League of the team', TEAMTALLY_TEXT_DOMAIN ),
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
		Teams_View::new_team_page_meta_box_league_content( $leagues, $this->teams->league_id );
	}

	/**
	 * Called when the form containing the team data is saved
	 *
	 * @return void
	 */
	public function save_posted_team_data( $post_id, $post ) {

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( TEAMTALLY_USER_CAPABILITY, $post_id ) ) {
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
	 * Checks for empty fields before saving
	 *
	 * @param $maybe_empty
	 * @param $postarr
	 *
	 * @return boolean // If true then save is aborted
	 */
	public function check_posted_team_data( $maybe_empty, $postarr ) {

		if ( $postarr['post_status'] === 'auto-draft' ) {
			return $maybe_empty;
		}

		$team_name = Helper::get_var( $postarr['post_title'], '' );
		$team_name = trim( $team_name );

		$team_nickname = Helper::get_var( $postarr['team_nickname'], '' );
		$team_nickname = trim( $team_nickname );

		$team_history = Helper::get_var( $postarr['content'], '' );
		$team_history = trim( wp_strip_all_tags( $team_history, true ) );

		$team_photo   = Helper::get_var( $postarr['_thumbnail_id'], - 1 );
		$teams_league = Helper::get_var( $postarr['teams_league'], '' );

		if ( empty( $team_name ) ||
		     empty( $team_nickname ) ||
		     empty( $team_history ) ||
		     empty( $teams_league ) ||
		     $team_photo == - 1 ) {

			// Avoid displaying success message in order to further replace it with error
			// In fact this hook removes the &message=... from $location
			add_filter( 'redirect_post_location', function ( $location, $post_id ) {
				$location = add_query_arg( array(
					'post'   => $post_id,
					'action' => 'edit'
				), admin_url( 'post.php' ) );

				return $location;
			}, 10, 2 );

			// Sets an admin notice error
			Admin_Notices::set_message(
				__( 'Please fill all fields.' ),
				Admin_Notices::ADMIN_NOTICE_ERROR,
				false,
				'ERROR_EMPTY_FIELDS',
				true
			);

			return true; // is empty
		}

		return $maybe_empty;
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

		if ( isset( $post ) && ( $post->post_type != Teams_Model::TEAMS_POST_TYPE ) ) {
			return false;
		}

		// Checks if a league is associated to the post
		$terms = get_the_terms( $post_id, Leagues_Model::LEAGUES_TAXONOMY_NAME );
		if ( ! is_wp_error( $terms ) && is_array( $terms ) ) {
			$this->teams->league    = Leagues_Model::get_league( $terms[0] );
			$this->teams->league_id = Helper::get_var( $this->teams->league['data']['term_id'], false );
		}

		$this->teams->active_page = 'edit_team';
		$this->current_team       = $post;

		return true;

	}

	/**
	 * Initialization routine
	 */
	protected function init( $args ) {

		$this->teams = $args[0];

		// checks if page is 'edit_team'
		$this->init_if_current_page_is_edit_team();

		if ( ! in_array( $this->teams->active_page, array( 'add_new_team', 'edit_team' ) ) ) {
			return;
		}

		// Displays the nickname field and the history label
		add_action( 'admin_head-post-new.php', array( $this, 'customize_add_edit_team_page' ) );
		add_action( 'admin_head-post.php', array( $this, 'customize_add_edit_team_page' ) );

		// hook for leagues meta box
		add_action( 'add_meta_boxes_' . Teams_Model::TEAMS_POST_TYPE, array(
			$this,
			'new_team_page_meta_boxes'
		) );

		// hook to check if all the fields are filled correctly before saving
		add_filter( 'wp_insert_post_empty_content', array(
			$this,
			'check_posted_team_data'
		), 10, 2 );

		// hook when the post is being saved
		add_action( 'save_post_' . Teams_Model::TEAMS_POST_TYPE, array(
			$this,
			'save_posted_team_data'
		), 10, 2 );

	}

	/**
	 * @param Teams_Controller $teams
	 *
	 * @return void
	 */
	public static function run( $teams ) {
		self::get_instance( $teams ); // this parameter will be sent to self::init()
	}

}