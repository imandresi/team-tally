<?php
/**
 * Created by PhpStorm.
 * User: Itanjaka Mandresi
 * Date: 27/02/2023
 * Time: 07:20
 */

namespace TEAMTALLY\Models;

use TEAMTALLY\System\Helper;
use TEAMTALLY\System\Singleton;
use WP_Post;

class Teams_Model extends Singleton {

	const TEAMS_POST_TYPE = 'teamtally_teams';
	const TEAMS_FIELD_NAME = 'team_name';
	const TEAMS_FIELD_NICKNAME = 'team_nickname';
	const TEAMS_FIELD_HISTORY = 'team_history';
	const TEAMS_FIELD_LOGO = 'team_logo';

	/**
	 * Returns info about a team post
	 *
	 * @param int|WP_Post $team
	 *
	 * @return array|false
	 */
	public static function get_team( $team ) {

		$team_data = Generic_Model::get_post( $team, self::TEAMS_POST_TYPE );

		if ( ! $team_data ) {
			return false;
		}

		/** @var WP_Post $team_post */
		$team_post = $team_data['raw'];

		$team_data ['data'] = array(
			'ID'                       => $team_post->ID,
			self::TEAMS_FIELD_NAME     => $team_post->post_title,
			self::TEAMS_FIELD_NICKNAME => $team_data['meta'][ self::TEAMS_FIELD_NICKNAME ],
			self::TEAMS_FIELD_HISTORY  => $team_post->post_content,
			self::TEAMS_FIELD_LOGO     => array(
				'ID'  => get_post_thumbnail_id( $team_post ),
				'URL' => get_the_post_thumbnail_url( $team_post, array( 500, 500 ) ),
			)
		);

		return $team_data;

	}

	/**
	 * Returns the number of teams in a league
	 *
	 * @param $league
	 *
	 * @return int
	 */
	public static function count_teams_in_league($league) {

		$args = array(
			'post_type' => self::TEAMS_POST_TYPE,
			'tax_query' => array(
				array(
					'taxonomy' => Leagues_Model::LEAGUES_TAXONOMY_NAME,
					'field' => 'id',
					'terms' => Helper::get_var($league['data']['term_id'])
				)
			)
		);

		$query = new \WP_Query($args);

		$teams_count = $query->post_count;

		return $teams_count;
	}

	/**
	 * Initialization of the data model
	 *
	 * @return void
	 */
	public function initialize_data_model() {

		register_post_type( self::TEAMS_POST_TYPE, array(
			'supports'           => array(
				'title',
				'editor',
				'thumbnail'
			),
			'public'             => true,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => false,
			'show_in_nav_menus'  => false,
			'show_in_admin_bar'  => false,
			'rewrite'            => false,

			'labels' => array(
				'name'                     => __( 'Teams', TEAMTALLY_TEXT_DOMAIN ),
				'singular_name'            => __( 'Team', TEAMTALLY_TEXT_DOMAIN ),
				'add_new'                  => __( 'Add New Team', TEAMTALLY_TEXT_DOMAIN ),
				'add_new_item'             => __( 'Add New Team', TEAMTALLY_TEXT_DOMAIN ),
				'edit_item'                => __( 'Edit Team', TEAMTALLY_TEXT_DOMAIN ),
				'new_item'                 => __( 'New Team', TEAMTALLY_TEXT_DOMAIN ),
				'view_item'                => __( 'View Team', TEAMTALLY_TEXT_DOMAIN ),
				'view_items'               => __( 'View Teams', TEAMTALLY_TEXT_DOMAIN ),
				'search_items'             => __( 'Search Teams', TEAMTALLY_TEXT_DOMAIN ),
				'not_found'                => __( 'No team found', TEAMTALLY_TEXT_DOMAIN ),
				'not_found_in_trash'       => __( 'No team found in trash', TEAMTALLY_TEXT_DOMAIN ),
				'all_items'                => __( 'All Teams', TEAMTALLY_TEXT_DOMAIN ),
				'item_published'           => __( 'Team published', TEAMTALLY_TEXT_DOMAIN ),
				'item_published_privately' => __( 'Team published with private visibility', TEAMTALLY_TEXT_DOMAIN ),
				'item_updated'             => __( 'Team updated', TEAMTALLY_TEXT_DOMAIN ),
				'featured_image'           => __( 'Logo of the team', TEAMTALLY_TEXT_DOMAIN ),
				'set_featured_image'       => __( 'Choose the logo', TEAMTALLY_TEXT_DOMAIN ),
			)

		) );

		register_post_meta(
			self::TEAMS_POST_TYPE,
			self::TEAMS_FIELD_NICKNAME,
			array(
				'type'              => 'string',
				'single'            => true,
				'sanitize_callback' => function ( $value ) {
					return wp_strip_all_tags( $value );
				}
			)
		);

	}

	/**
	 * Initialization routine
	 */
	protected function init() {
		add_action( 'init', array( $this, 'initialize_data_model' ) );
	}

	/**
	 * Loads and executes the class
	 */
	public static function load() {
		self::get_instance();
	}


} /* End of Class */