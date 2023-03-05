<?php
/**
 * Created by PhpStorm.
 * User: Itanjaka Mandresi
 * Date: 22/02/2023
 * Time: 15:08
 */

namespace TEAMTALLY\Models;

use TEAMTALLY\System\Helper;
use WP_Term;

class Leagues_Model {

	const LEAGUES_TAXONOMY_NAME = 'teamtally_leagues';
	const LEAGUES_FIELD_NAME = 'league_name';
	const LEAGUES_FIELD_COUNTRY = 'league_country';
	const LEAGUES_FIELD_PHOTO = 'league_photo';

	/**
	 * Retrieves league data with photo metadata
	 *
	 * @param int|WP_Term $league
	 *
	 * @return array|false
	 */
	public static function get_league( $league ) {

		$league_data = Generic_Model::get_taxonomy_term_info( $league, self::LEAGUES_TAXONOMY_NAME );

		if ( ! $league_data ) {
			return false;
		}

		$league_photo_id       = Helper::get_var( $league_data['meta'][ self::LEAGUES_FIELD_PHOTO ] );
		$league_photo_metadata = $league_photo_id ? wp_get_attachment_metadata( $league_photo_id ) : false;

		$data = array(
			'term_id'                   => Helper::get_var( $league_data['raw']->term_id ),
			self::LEAGUES_FIELD_NAME    => Helper::get_var( $league_data['raw']->name ),
			self::LEAGUES_FIELD_COUNTRY => Helper::get_var( $league_data['meta'][ self::LEAGUES_FIELD_COUNTRY ] ),
			self::LEAGUES_FIELD_PHOTO   => array(
				'id'       => $league_photo_id,
				'metadata' => $league_photo_metadata
			)
		);

		$league_data['data'] = $data;

		return $league_data;

	}

	/**
	 * Deletes a league
	 *
	 * @param $league_id
	 *
	 * @return boolean
	 */
	public static function delete_league( $league_id ) {
		// Checks if there are teams associated with the league and
		// refuse to delete if true

		$posts_query = Generic_Model::get_posts_linked_to_taxonomy_term(
			Teams_Model::TEAMS_POST_TYPE,
			$league_id,
			self::LEAGUES_TAXONOMY_NAME
		);

		if ( $posts_query->post_count > 0 ) {
			return false;
		}

		// Delete the term
		wp_delete_term( $league_id, self::LEAGUES_TAXONOMY_NAME );

		return true;
	}

	/**
	 * Deletes all the existing leagues
	 *
	 * @return void
	 */
	public static function delete_all_leagues() {
		$taxonomy_name = Leagues_Model::LEAGUES_TAXONOMY_NAME;

		$terms = get_terms( array(
			'taxonomy'   => $taxonomy_name,
			'hide_empty' => false,
		) );

		if ( ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				wp_delete_term( $term->term_id, $taxonomy_name );
			}
		}

		unregister_taxonomy( $taxonomy_name );

	}

	/**
	 * Saves or updates the data of a 'League'
	 *
	 * @param $data
	 * @param $league_id
	 *
	 * @return int|0  // Returns the league_id or 0 if error
	 */
	public static function update_league( $data, $league_id = 0 ) {
		$term_info = array();
		$do_update = (boolean) $league_id;

		// update term
		if ( ! $do_update ) {
			$term_info = wp_insert_term( $data[ self::LEAGUES_FIELD_NAME ], self::LEAGUES_TAXONOMY_NAME );
		} else {
			$term_info = wp_update_term(
				$league_id,
				self::LEAGUES_TAXONOMY_NAME,
				array(
					'name' => $data[ self::LEAGUES_FIELD_NAME ]
				)
			);
		}

		if ( is_wp_error( $term_info ) ) {
			return 0;
		}

		// update meta
		$league_id = $term_info['term_id'];

		if ( $do_update ) {

			update_term_meta( $league_id, self::LEAGUES_FIELD_COUNTRY, $data[ self::LEAGUES_FIELD_COUNTRY ] );
			update_term_meta( $league_id, self::LEAGUES_FIELD_PHOTO, $data[ self::LEAGUES_FIELD_PHOTO ] );
		} else {
			add_term_meta( $league_id, self::LEAGUES_FIELD_COUNTRY, $data[ self::LEAGUES_FIELD_COUNTRY ], true );
			add_term_meta( $league_id, self::LEAGUES_FIELD_PHOTO, $data[ self::LEAGUES_FIELD_PHOTO ], true );
		}

		return $league_id;

	}

	/**
	 * Returns the list of leagues
	 *
	 * @return array|false
	 */
	public static function get_all_leagues() {
		$leagues = array();

		$leagues_list = get_terms( self::LEAGUES_TAXONOMY_NAME, array(
			'fields'     => 'all',
			'hide_empty' => false,
		) );

		if ( is_wp_error( $leagues_list ) ) {
			return false;
		}

		/** @var WP_Term $league */
		foreach ( $leagues_list as $league ) {
			$leagues[] = self::get_league( $league );
		}

		return $leagues;

	}

	/**
	 * Initialization of the data model
	 *
	 * @return void
	 */
	public static function initialize_data_model() {
		register_taxonomy(
			self::LEAGUES_TAXONOMY_NAME,
			Teams_Model::TEAMS_POST_TYPE,
			array(
				'public'            => true,
				'show_ui'           => false,
				'show_in_menu'      => false,
				'show_in_nav_menus' => false,
				'show_in_rest'      => false,
				'hierarchical'      => false,
				'query_var'         => 'league',
				'rewrite'           => array(
					'slug'         => 'league',
					'with_front'   => false,
					'hierarchical' => false,
					'ep_mask'      => EP_NONE,
				),
			)
		);

	}

	/**
	 * Initialization routine
	 */
	public static function init() {
		add_action( 'init', array( self::class, 'initialize_data_model' ) );
	}

}