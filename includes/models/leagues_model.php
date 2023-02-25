<?php
/**
 * Created by PhpStorm.
 * User: Itanjaka Mandresi
 * Date: 22/02/2023
 * Time: 15:08
 */

namespace TEAMTALLY\Models;

use TEAMTALLY\System\Singleton;
use TEAMTALLY\System\Helper;
use WP_Post;
use WP_Query;

class Leagues_Model extends Singleton {
	const LEAGUES_POST_TYPE = 'teamtally_leagues';
	const LEAGUES_POST_META_COUNTRY = 'league-country';
	const LEAGUES_POST_META_PHOTO = 'league-photo';

	/**
	 * Retrieves post data + post meta
	 *
	 * @param array|false $post
	 *
	 * @return array|false
	 */
	public static function get_league( $post ) {
		$post = Generic_Model::get_post( $post, self::LEAGUES_POST_TYPE );

		if ( ! $post ) {
			return false;
		}

		$league_photo = Helper::get_var( $post['post_meta']['league-photo'] );
		$metadata     = $league_photo ? wp_get_attachment_metadata( $league_photo ) : false;

		$post['data'] = array(
			'league-name'    => $post['post_title'],
			'league-country' => $post['post_meta']['league-country'],
			'league-photo'   => array(
				'id'       => $league_photo,
				'metadata' => $metadata
			)
		);

		return $post;

	}

	/**
	 * Saves or updates the data of a 'League'
	 *
	 * @param $data
	 * @param $post_id
	 *
	 * @return int | 0  // Returns the post_id or 0 if error
	 */
	public static function update_league( $data, $post_id = 0 ) {
		$instance = self::get_instance();

		$update_args = array(
			'ID'          => $post_id,
			'post_title'  => $data['league-name'],
			'post_status' => 'publish',
			'post_type'   => self::LEAGUES_POST_TYPE,
			'meta_input'  => array(
				self::LEAGUES_POST_META_COUNTRY => $data[ self::LEAGUES_POST_META_COUNTRY ],
				self::LEAGUES_POST_META_PHOTO   => $data[ self::LEAGUES_POST_META_PHOTO ],
			),
		);

		/** @var int $post_id */
		$post_id = wp_insert_post( $update_args, false, false );

		return $post_id;

	}

	/**
	 * Returns the list of leagues
	 *
	 * @return array
	 */
	public static function get_all_leagues() {
		$leagues = array();

		$args = array(
			'post_type' => self::LEAGUES_POST_TYPE,
			'order'     => 'ASC',
			'orderby'   => 'title',
		);

		$the_query = new WP_Query( $args );

		if ( $the_query->post_count > 0 ) {
			$leagues = $the_query->posts;
		}

		wp_reset_postdata();

		return $leagues;

	}


	/**
	 * Initialization of the data model
	 *
	 * @return void
	 */
	public function initialize_data_model() {

		register_post_type( self::LEAGUES_POST_TYPE, array(
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => false,
			'show_in_menu'       => false,
			'show_in_nav_menus'  => false,
			'show_in_admin_bar'  => false,
			'rewrite'            => false
		) );

		register_post_meta(
			self::LEAGUES_POST_TYPE,
			self::LEAGUES_POST_META_COUNTRY,
			array(
				'type'              => 'string',
				'single'            => true,
				'sanitize_callback' => function ( $value ) {
					return wp_strip_all_tags( $value );
				}
			)
		);

		register_post_meta(
			self::LEAGUES_POST_TYPE,
			self::LEAGUES_POST_META_PHOTO,
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

}