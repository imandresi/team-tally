<?php
/**
 * Created by PhpStorm.
 * User: Itanjaka Mandresi
 * Date: 17/03/2023
 * Time: 16:34
 */

namespace TEAMTALLY\System;

class Dynamic_Css extends Singleton {

	private $handles = array();

	/**
	 * Add inline style after a css handle already loaded
	 *
	 * This script prevents multiple insertions
	 *
	 * @param $css_handle
	 * @param $css_data
	 *
	 * @return bool
	 */
	public static function inline_style( $css_handle, $css_data) {
		$instance = self::get_instance();

		// already inserted ?
		if ( isset( $instance->handles[ $css_handle ] ) ) {
			return false;
		}

		$status = wp_add_inline_style(  $css_handle, $css_data );

		// this initialization prevent multiple insertion of the script
		$instance->handles[ $css_handle ] = true;

		return $status;

	}

	/**
	 * Automatic initialization routine
	 */
	protected function init() {
		$this->handles = array();

	}

	/**
	 * Loading the class
	 */
	public static function load() {
		self::get_instance();
	}

}