<?php
/**
 * Created by PhpStorm.
 * User: Itanjaka Mandresi
 * Date: 25/03/2023
 * Time: 19:08
 */

namespace TEAMTALLY\Elementor\Includes;

use TEAMTALLY\System\Singleton;

class Elementor_League_Listing_Pagination extends Singleton {

	private $activated = false;

	/**
	 * Our custom paginate_links() function
	 *
	 * @param $args
	 *
	 * @return string|string[]|null
	 */
	public static function paginate_links( $args ) {
		$instance = self::get_instance();
		$result   = null;

		// this flag is used inside filters
		// and prevents unwanted modification of other
		// paginate_links() functions
		$instance->activated = true;

		$result = paginate_links( $args );

		$instance->activated = false;

		return $result;

	}

	/**
	 * Clears the pagenum link so that only page number is left
	 *
	 * Fired by the 'get_pagenum_link' filter
	 *
	 * @param $result
	 * @param $pagenum
	 *
	 * @return string
	 */
	public function filter_get_pagenum_link( $result, $pagenum ) {
		$result = $this->activated ? '' : $result;

		return $result;
	}

	/**
	 * Changes the final output from the paginate_links() function
	 *
	 * Fired by 'paginate_links_output' filter
	 *
	 * @param $r
	 * @param $args
	 *
	 * @return mixed
	 */
	public function filter_paginate_links_output( $r, $args ) {
		$regexp = "/ href=\"http:\/\/(\d+)?\"/is";
		$replacement = " href=\"javascript:void(0);\" onclick=\"window.TEAMTALLY.widgetNavigate('{$args['widget_id']}', $1);\"";
		$r = preg_replace($regexp, $replacement, $r);

		return $r;
	}

	/**
	 * Initialization
	 *
	 * @return void
	 */
	protected function init() {
		$this->activated = false;

		add_filter( 'get_pagenum_link', array( $this, 'filter_get_pagenum_link' ), 10, 2 );
		add_filter( 'paginate_links_output', array( $this, 'filter_paginate_links_output' ), 10, 2 );

	}

	/**
	 * Loads the class
	 */
	public static function load() {
		self::get_instance();
	}

}