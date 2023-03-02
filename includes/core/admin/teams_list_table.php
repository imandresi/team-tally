<?php
/**
 * Created by PhpStorm.
 * User: Itanjaka Mandresi
 * Date: 01/03/2023
 * Time: 13:41
 */

namespace TEAMTALLY\Core\Admin;

use TEAMTALLY\Models\Leagues_Model;
use TEAMTALLY\System\Helper;
use \WP_Posts_List_Table;

class Teams_List_Table extends WP_Posts_List_Table {

	private $league_id;

	/**
	 * Constructor.
	 *
	 * @param $args
	 */
	public function __construct( $args = array() ) {
		parent::__construct( $args );

		$this->league_id = Helper::get_var( $_GET['league_id'] );

	}

	/**
	 * Override of WP_Posts_List_Table::prepare_items()
	 *
	 * @return void
	 */
	public function prepare_items() {
		global $wp_query;

		parent::prepare_items();

		$wp_query->set( 'tax_query',
			array(
				array(
					'taxonomy' => Leagues_Model::LEAGUES_TAXONOMY_NAME,
					'field'    => 'term_id',
					'terms'    => $this->league_id,
				)
			)
		);

		$wp_query->get_posts();

	}

}