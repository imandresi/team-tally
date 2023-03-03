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
		if ($this->teams->league_id) {
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
		Teams_View::new_team_page_meta_box_league_content( $leagues, $this->teams->league_id );
	}

	/**
	 * Called when the form containing the team data is saved
	 *
	 * @return void
	 */
	public function save_posted_team_data( $post_id, $post ) {

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
			$this->teams->league    = $terms[0];
			$this->teams->league_id = $this->teams->league->term_id;
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