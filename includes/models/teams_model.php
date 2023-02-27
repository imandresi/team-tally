<?php
/**
 * Created by PhpStorm.
 * User: Itanjaka Mandresi
 * Date: 27/02/2023
 * Time: 07:20
 */

namespace TEAMTALLY\Models;

use TEAMTALLY\System\Singleton;

class Teams_Model extends Singleton {

	const TEAMS_POST_TYPE = 'teamtally_teams';
	const TEAMS_POST_META_LEAGUE = 'team_league';

	/**
	 * Initialization of the data model
	 *
	 * @return void
	 */
	public function initialize_data_model() {
/*
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
*/

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