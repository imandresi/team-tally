<?php
/**
 * Created by PhpStorm.
 * User: Itanjaka Mandresi
 * Date: 18/12/2018
 * Time: 06:10
 */

namespace TEAMTALLY\Shortcodes;

use TEAMTALLY\System\Helper;

abstract class Abstract_Shortcode_Base {

	protected $shortcode_default_attributes = null;

	protected $_shortcode_name = '';

	/**
	 * Abstract_Shortcode_Base constructor.
	 *
	 * @param array $default_attributes
	 */
	public function __construct( $default_attributes = null ) {

		// Sets the shortcode name
		$shortcode_name = '';
		$called_class   = get_called_class();
		$regexp         = "/Shortcode_(\w+$)/is";

		if ( preg_match( $regexp, $called_class, $matches ) ) {
			$shortcode_name = $matches[1];
		}

		$this->_shortcode_name = $shortcode_name;

		// Initialization of default attributes
		if ( ( is_null( $this->shortcode_default_attributes ) ) && ( is_null( $default_attributes ) ) ) {
			$error_message = "Please set the property <strong>[$called_class]->shortcode_default_attributes</strong> with an array containing all the supported attributes and their default values.";
			Helper::raise_fatal_error( $error_message );
		}

		if ( ! is_null( $default_attributes ) ) {
			$this->shortcode_default_attributes = $default_attributes;
		}

	}

	/**
	 * Returns the shortcode name
	 *
	 * @return string
	 */
	public function shortcode_name() {
		return $this->_shortcode_name;
	}


	/**
	 * Transforms every key from parameters passed to the shortcode into
	 * an array based on the '-' character. So, a key named 'key1-key11-key111'
	 * will be converted into the following array :
	 * $attributes[key1][key11][key111]
	 *
	 * @param $atts
	 *
	 * @return array
	 */
	protected function parse_shortcode_attributes( $atts ) {

		$attributes = array();

		if ( ! is_array( $atts ) ) {
			return array();
		}

		foreach ( $atts as $key => $value ) {
			$found    = false;
			$variable = &Helper::array_value_from_string(
				$attributes,
				$key,
				$found,
				true,
				'-'
			);

			if ( is_string( $variable ) ) {
				$variable = html_entity_decode( $value, ENT_QUOTES );
			}
			else {
				$variable = $value;
			}

		}

		return $attributes;

	}

	/**
	 * This is the shortcode function called by 'add_shortcode'
	 *
	 * @param $atts
	 * @param string $content
	 *
	 * @return string
	 *
	 */
	public function do_shortcode( $atts, $content = '' ) {

		$atts = $this->parse_shortcode_attributes( $atts );

		$atts = Shortcodes::shortcode_atts( $this->shortcode_default_attributes, $atts, $this->_shortcode_name );

		/**
		 * Filters the associative array of the shortcode attributes
		 *
		 * @param array $atts
		 * @param string $content
		 */
		$atts = apply_filters( "{$this->shortcode_name()}_TEAMTALLY_shortcode_attributes", $atts, $content );

		/**
		 * Filters the enclosed content of the shortcode
		 *
		 * @param array $atts
		 * @param array $content
		 */
		$content = apply_filters( "{$this->shortcode_name()}_TEAMTALLY_shortcode_content", $content, $atts );


		/**
		 * Executes possible hooks to be executed before the shortcode
		 *
		 * @param array $atts
		 * @param array $content
		 */
		do_action( "{$this->shortcode_name()}_TEAMTALLY_pre_shortcode", $atts, $content );

		/**
		 * Executes the real routine of the shortcode
		 */
		$html = $this->shortcode_routine( $atts, $content );

		/**
		 * Executes possible hooks to be executed after the shortcode
		 *
		 * @param array $atts
		 * @param array $content
		 */
		do_action( "{$this->shortcode_name()}_TEAMTALLY_post_shortcode", $atts, $content );

		return $html;

	}

	/**
	 * Implementation of the shortcode
	 *
	 * @param $atts
	 * @param string $content
	 *
	 * @return string
	 *
	 */
	abstract public function shortcode_routine( $atts, $content = '' );

}