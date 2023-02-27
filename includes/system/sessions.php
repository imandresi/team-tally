<?php
/**
 * Created by PhpStorm.
 * User: Itanjaka Mandresi
 * Date: 27/02/2023
 * Time: 14:57
 */

namespace TEAMTALLY\System;

class Sessions {

	const SESSION_PREFIX = PROJECT_NAME;

	/**
	 * Adds an item to the session
	 *
	 * @param $key
	 * @param $value
	 *
	 * @return void
	 */
	public static function set( $key, $value ) {
		$session_var = &self::get_key_ref( $key );
		$session_var = $value;
	}

	/**
	 * Returns the value of the session pointed by $key
	 *
	 * @param $key
	 *
	 * @return array|mixed|NULL
	 */
	public static function &get( $key ) {
		$session_var = &self::get_key_ref( $key );

		return $session_var;
	}

	/**
	 * Returns a reference to the session variable pointed by $key
	 *
	 * @param $key
	 *
	 * @return array|mixed|NULL
	 */
	private static function &get_key_ref( $key ) {
		$key_str     = $key ? self::SESSION_PREFIX . ':' . $key : self::SESSION_PREFIX;
		$session_var = &Helper::array_value_from_string( $_SESSION, $key_str, $found, true );

		return $session_var;
	}

	/**
	 * Clears all the sessions associated to the project (only)
	 *
	 * @return void
	 */
	public static function clear() {
		$session_var = &self::get( '' );
		$session_var = array();
	}

	/**
	 * Called at initialization
	 *
	 * @return void
	 */
	private static function start_session() {
		if ( ! session_id() ) {
			session_start();
		}
	}

	/**
	 * Called at initialization
	 *
	 * @return void
	 */
	private static function regenerate_session_id() {
		session_regenerate_id( true );
	}

	/**
	 * Initialization
	 *
	 * @return void
	 */
	public static function init() {
		self::start_session();
		add_action( 'wp_login', array( self::class, 'regenerate_session_id' ) );
		add_action( 'wp_logout', array( self::class, 'regenerate_session_id' ) );
	}

}