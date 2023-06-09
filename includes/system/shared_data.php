<?php
/**
 * Created by PhpStorm.
 * User: Itanjaka Mandresi
 * Date: 13/03/2023
 * Time: 19:08
 */

namespace TEAMTALLY\System;

class Shared_Data extends Singleton {

	private $handles = array();

	/**
	 * Shares PHP data with JS scripts
	 *
	 * @param string $js_handle
	 * @param array $data
	 * @param string $position - 'after' or 'before'
	 *
	 * @return void
	 */
	public static function share_data_to_js( $js_handle, $data, $position = 'after' ) {
		$instance = self::get_instance();

		// already inserted ?
		if ( isset( $instance->handles[ $js_handle ] ) ) {
			return;
		}

		$js_code = self::build_js_from_data( $data );

		wp_add_inline_script( $js_handle, $js_code, $position );

		// this initialization prevent multiple insertion of the script
		$instance->handles[ $js_handle ] = true;

	}

	/**
	 * Creates the javascript code allowing data sharing
	 *
	 * @param $data
	 * @param $add_script_tag
	 *
	 * @return string
	 */
	public static function build_js_from_data( $data, $add_script_tag = false ) {

		$js_code = Template::parse(
			'admin/common/shared_data.php',
			array(
				'shared_data' => $data
			)
		);

		if ( $add_script_tag ) {
			$js_code = "<script>$js_code</script>";
		}

		return $js_code;

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