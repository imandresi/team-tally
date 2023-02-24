<?php
/**
 * Created by PhpStorm.
 * User: Itanjaka Mandresi
 * Date: 16/12/2018
 * Time: 17:30
 */

namespace TEAMTALLY\System;

class Template {

	/**
	 * @param $html
	 * @param $var_list
	 */
	protected static function evaluate_vars( &$html, $var_list ) {

		if ( is_array( $var_list ) ) {
			foreach ( $var_list as $key => $value ) {
				$value = !$value ? '' : $value;
				if ( ( is_string( $value ) ) || ( is_numeric( $value ) ) ) {

					// value will not be escaped
					$search = '{{{' . $key . '}}}';
					$html = str_replace( $search, $value, $html );

					// value will be escaped
					$search = '{{' . $key . '}}';
					$value  = esc_html( $value );
					$html = str_replace( $search, $value, $html );

				}
			}
		}
	}

	/**
	 * @param $filename
	 * @param $attributes
	 *
	 * @return string
	 */
	protected static function evaluate_php_file( $filename, &$attributes ) {
		extract( $attributes, EXTR_OVERWRITE );

		ob_start();
		include( $filename );
		$html = ob_get_clean();

		return $html;

	}

	/**
	 * Parses a template by replacing all placeholders with the corresponding
	 * attributes variables.
	 *
	 * The template may be a TEMPLATE FILE NAME or a TEMPLATE POST. If it is a
	 * template file name, it is a relative path from TEAMTALLY_TEMPLATES_DIR.
	 * If it is a template post, it is the slug of the template POST.
	 *
	 * @param string $template template file name or template post
	 * @param array $attributes
	 *
	 * @return string
	 */
	public static function parse( $template, $attributes = array() ) {

		$html = '';

		// adds constants to $attributes
		$constants  = Helper::get_defined_constants();
		$attributes = array_merge( $constants, $attributes );

		// Is the template a file ?
		// The template path has to be relative to the template directory
		$full_template_filename = TEAMTALLY_TEMPLATES_DIR . $template;
		if ( is_file( $full_template_filename ) ) {
			// evaluates php codes
			$html = self::evaluate_php_file( $full_template_filename, $attributes );
		}

		// evaluates template variables
		self::evaluate_vars( $html, $attributes );

		// evaluate shortcodes
		$html = do_shortcode( $html );

		return $html;

	}

	/**
	 * @param $template
	 * @param array $attributes
	 */
	public static function pparse( $template, $attributes = array() ) {
		print self::parse( $template, $attributes );
	}

}