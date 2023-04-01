<?php
/**
 * Created by PhpStorm.
 * User: Itanjaka Mandresi
 * Date: 27/02/2023
 * Time: 07:20
 */

namespace TEAMTALLY\Models;

use TEAMTALLY\System\Helper;
use WP_Post;

class Teams_Model {

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

		// get the league associated to the team
		$terms     = get_the_terms( $team_post, Leagues_Model::LEAGUES_TAXONOMY_NAME );
		$term      = Helper::get_var( $terms[0] );
		$league_id = $term ? $term->term_id : 0;

		// builds final data
		$team_data ['data'] = array(
			'ID'                       => $team_post->ID,
			self::TEAMS_FIELD_NAME     => $team_post->post_title,
			self::TEAMS_FIELD_NICKNAME => $team_data['meta'][ self::TEAMS_FIELD_NICKNAME ],
			self::TEAMS_FIELD_HISTORY  => $team_post->post_content,
			self::TEAMS_FIELD_LOGO     => array(
				'ID'  => get_post_thumbnail_id( $team_post ),
				'URL' => get_the_post_thumbnail_url( $team_post, array( 500, 500 ) ),
			),
			'league_id'                => $league_id,
		);

		return $team_data;

	}

	/**
	 * Inserts or updates a team
	 *
	 * @param $team
	 * @param $team_id
	 *
	 * @return int|\WP_Error
	 */
	public static function update_team( $team, $team_id = 0 ) {

		$league_id = Helper::get_var( $team['league_id'], 0 );
		if ( ! $league_id ) {
			return 0;
		}

		$league_data = Leagues_Model::get_league( $league_id );
		$league_slug = Helper::get_var( $league_data['raw']->slug );
		if ( ! $league_slug ) {
			return 0;
		}

		$post_data = array(
			'ID'           => $team_id,
			'post_title'   => $team[ self::TEAMS_FIELD_NAME ],
			'post_content' => $team[ self::TEAMS_FIELD_HISTORY ],
			'post_status'  => 'publish',
			'post_type'    => self::TEAMS_POST_TYPE,
			'meta_input'   => array(
				self::TEAMS_FIELD_NICKNAME => $team[ self::TEAMS_FIELD_NICKNAME ]
			),
			'tax_input'    => array(
				Leagues_Model::LEAGUES_TAXONOMY_NAME => array( $league_slug )
			),
		);

		$post_id = wp_insert_post( $post_data );

		// updates featured image
		$media_id = false;
		if ( isset( $team[ self::TEAMS_FIELD_LOGO ]['ID'] ) ) {
			$media_id = intval( Helper::get_var( $team[ self::TEAMS_FIELD_LOGO ]['ID'], 0 ) );
		}

		if ( ! $media_id ) {
			$media_id = intval( Helper::get_var( $team[ self::TEAMS_FIELD_LOGO ], 0 ) );
		}

		if ( $post_id && $media_id ) {
			set_post_thumbnail( $post_id, $media_id );
		}

		return $post_id;

	}

	/**
	 * Returns the number of teams in a league
	 *
	 * @param $league
	 *
	 * @return int
	 */
	public static function count_teams_in_league( $league ) {

		$args = array(
			'post_type' => self::TEAMS_POST_TYPE,
			'tax_query' => array(
				array(
					'taxonomy' => Leagues_Model::LEAGUES_TAXONOMY_NAME,
					'field'    => 'id',
					'terms'    => Helper::get_var( $league['data']['term_id'] )
				)
			)
		);

		$query = new \WP_Query( $args );

		$teams_count = $query->post_count;

		return $teams_count;
	}

	/**
	 * Initialization of the data model
	 *
	 * @return void
	 */
	public static function initialize_data_model() {

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
	 * Deletes all teamsp
	 *
	 * @return void
	 */
	public static function delete_all_teams() {
		$post_statuses = array(
			'publish',
			'future',
			'draft',
			'pending',
			'private',
			'trash',
			'auto-draft',
			'inherit',
		);

		$args = array(
			'post_type'      => self::TEAMS_POST_TYPE,
			'posts_per_page' => - 1, // Set to -1 to retrieve all posts of the given post type
			'post_status'    => $post_statuses,
		);

		$posts = get_posts( $args );

		foreach ( $posts as $post ) {
			wp_delete_post( $post->ID, true );
		}
	}

	/**
	 * Initialization routine
	 */
	public static function init() {
		add_action( 'init', array( self::class, 'initialize_data_model' ) );
	}

} /* End of Class */