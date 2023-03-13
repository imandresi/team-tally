<?php
/**
 * Created by PhpStorm.
 * User: Itanjaka Mandresi
 * Date: 13/03/2023
 * Time: 13:12
 */

namespace TEAMTALLY\Elementor\Models;

class Team_Listing_Custom_Css_Model {

	const CSS_OPTION_FIELD = 'TEAMTALLY_TEAM_LISTING_CUSTOM_CSS';

	/**
	 * Extracts the custom css data
	 *
	 * @return string
	 */
	public static function get_css() {
		return get_option( self::CSS_OPTION_FIELD, '' );
	}

	/**
	 * Saves the css data
	 *
	 * @param string $css
	 *
	 * @return void
	 */
	public static function save_css( $css ) {
		add_option( self::CSS_OPTION_FIELD, $css );
	}

	/**
	 * Used when uninstalling the plugin
	 *
	 * Removes the option from the database
	 *
	 * @return void
	 */
	public static function remove_field() {
		delete_option( self::CSS_OPTION_FIELD );
	}

}