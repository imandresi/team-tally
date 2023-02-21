<?php
/**
 * Created by PhpStorm.
 * User: Itanjaka Mandresi
 * Date: 18/12/2018
 * Time: 04:44
 */

namespace TEAMTALLY\Shortcodes;


use TEAMTALLY\System\Singleton;
use TEAMTALLY\System\Helper;

class Shortcodes extends Singleton {

	const SHORTCODE_SESSION_NAME = 'SHORTCODE_ATTRIBUTES';


	/**
	 * @param array $atts
	 * @param string $prefix
	 *
	 * @return string
	 */
	private static function build_shortcode_params( $atts, $prefix = '' ) {
		$atts_code = '';

		if ( is_array( $atts ) ) {
			foreach ( $atts as $key => $value ) {
				$key = $prefix ? $prefix . '-' . $key : $key;

				if ( is_array( $value ) ) {
					$atts_code .= self::build_shortcode_params( $value, $key );
				} else {
					$value     = addcslashes( $value, '"' );
					$atts_code .= " " . $key . '="' . $value . '"';
				}
			}
		}

		return $atts_code;

	}


	/**
	 * @param string $shortcode_name
	 * @param array $atts
	 * @param string|null $content
	 *
	 * @return string
	 */
	public static function build_shortcode( $shortcode_name, $atts, $content = null ) {

		$shortcode_params = self::build_shortcode_params( $atts );

		$shortcode = "[$shortcode_name" . $shortcode_params . "]";

		if ( $content ) {
			$shortcode .= $content . "[/" . $shortcode_name . "]";
		}

		return $shortcode;

	}


	/**
	 * @param $pairs
	 * @param $atts
	 * @param string $shortcode
	 *
	 * @return array|mixed
	 */
	public static function shortcode_atts( $pairs, $atts, $shortcode = '' ) {

		$out = Helper::array_recursive_merge_if_key_exists( $pairs, $atts );

		/**
		 * Filters a shortcode's default attributes.
		 *
		 * If the third parameter of the shortcode_atts() function is present then this filter is available.
		 * The third parameter, $shortcode, is the name of the shortcode.
		 *
		 * @since 3.6.0
		 * @since 4.4.0 Added the `$shortcode` parameter.
		 *
		 * @param array $out The output array of shortcode attributes.
		 * @param array $pairs The supported attributes and their defaults.
		 * @param array $atts The user defined shortcode attributes.
		 * @param string $shortcode The shortcode name.
		 */
		if ( $shortcode ) {
			$out = apply_filters( "shortcode_atts_{$shortcode}", $out, $pairs, $atts, $shortcode );
		}

		return $out;
	}

	/**
	 * Loads the shortcode registered inside the $shortcode_dir directory
	 *
	 * @param $shortcode_name
	 */
	private function load_shortcode( $shortcode_name ) {
		$shortcode_name      = strtolower( $shortcode_name );
		$shortcode_classname = 'Shortcode_' . $shortcode_name;

		$shortcode_filename      = strtolower( $shortcode_classname ) . '.php';
		$shortcode_full_filename = TEAMTALLY_SHORTCODES_DIR . $shortcode_name . '/' . $shortcode_filename;

		require( $shortcode_full_filename );

		// registers the shortcode
		$shortcode_classname = __NAMESPACE__ . '\\' . $shortcode_classname;
		$shortcode_obj       = new $shortcode_classname();
		add_shortcode( $shortcode_name, array( $shortcode_obj, 'do_shortcode' ) );

	}

	/**
	 * Loads all shortcodes defined inside the shortcodes directory
	 */
	private function load_all_shortcodes() {

		$d = dir( TEAMTALLY_SHORTCODES_DIR );

		while ( false !== ( $entry = $d->read() ) ) {
			if ( ( $entry == '.' ) || ( $entry == '..' ) ) {
				continue;
			}

			$full_filename = $d->path . '/' . $entry;

			if ( is_dir( $full_filename ) ) {
				$this->load_shortcode( $entry );
			}

		}

		$d->close();

	}

	/**
	 * Automatic initialization routine
	 */
	protected function init() {
		$this->load_all_shortcodes();
	}

	/**
	 * Loading the class
	 */
	public static function load() {
		self::get_instance();
	}

}