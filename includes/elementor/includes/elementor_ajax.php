<?php
/**
 * Created by PhpStorm.
 * User: Itanjaka Mandresi
 * Date: 24/03/2023
 * Time: 05:54
 */

namespace TEAMTALLY\Elementor\Includes;

use TEAMTALLY\Elementor\Models\League_Listing_Template_Model;
use TEAMTALLY\Elementor\Models\Team_Listing_Template_Model;
use TEAMTALLY\Elementor\Widgets\Elementor_League_Listing_Widget;
use TEAMTALLY\Elementor\Widgets\Elementor_Team_Listing_Widget;
use TEAMTALLY\System\Helper;
use TEAMTALLY\System\Singleton;

class Elementor_Ajax extends Singleton {

	const HAVE_ACCESS_RIGHTS = TEAMTALLY_USER_CAPABILITY;

	private $widget_class;
	private $template_model_class;

	/**
	 * Executes some initializations before really processing the request
	 *
	 * @return void
	 */
	private function setup_request() {
		$widget_name = Helper::get_var( $_REQUEST['widget_name'] );

		if ( ! in_array( $widget_name, array(
			'league_listing_widget',
			'team_listing_widget',
		) ) ) {
			$response = array(
				'success' => false,
				'message' => __( 'Bad parameters.' ),
			);

			wp_send_json( $response );
		}

		switch ( $widget_name ) {
			case 'league_listing_widget':
				$this->widget_class         = Elementor_League_Listing_Widget::class;
				$this->template_model_class = League_Listing_Template_Model::class;
				break;

			case 'team_listing_widget':
			default:
				$this->widget_class         = Elementor_Team_Listing_Widget::class;
				$this->template_model_class = Team_Listing_Template_Model::class;
				break;
		}

	}

	/**
	 * Checks security access to the ajax functionality
	 *
	 * @return string
	 */
	private function check_security_access( $nonce ) {
		$forbidden = false;

		$message      = '';
		$widget_class = $this->widget_class;
		$new_nonce    = wp_create_nonce( $widget_class::SECURITY_NONCE );

		if ( ! current_user_can( self::HAVE_ACCESS_RIGHTS ) ) {
			$message   = __( 'Action forbidden.' );
			$forbidden = true;
		}

		// security check
		if ( ! wp_verify_nonce( $nonce, $widget_class::SECURITY_NONCE ) ) {
			$message   = __( 'Action forbidden. Please reload the page.' );
			$forbidden = true;
		}

		if ( $forbidden ) {
			$response = array(
				'success' => false,
				'message' => $message,
				'_nonce'  => $new_nonce,
			);

			wp_send_json( $response );
		}

		return $new_nonce;

	}

	/**
	 * Returns the list of all templates
	 *
	 * fired by 'wp_ajax_elementor_league_listing_get_all_templates'
	 *
	 * @return void
	 */
	public function action_get_all_templates() {

		$this->setup_request();
		$template_model = $this->template_model_class;

		$templates = $template_model::get_all_templates();

		$template_names = array();
		foreach ( $templates as $template ) {
			$template_names[] = $template['name'];
		}

		$response = array(
			'success'   => true,
			'templates' => $template_names
		);

		wp_send_json( $response );

	}

	/**
	 * Returns an elementor league listing template
	 *
	 * fired by 'wp_ajax_elementor_league_listing_get_template' hook
	 *
	 * @return void
	 */
	public function action_get_template() {
		$this->setup_request();
		$template_model = $this->template_model_class;

		$template_name = $_REQUEST['template_name'];
		$data          = $template_model::get_template( $template_name );

		if ( ! $data ) {
			$response = array(
				'success' => false,
				'message' => __( 'Template not found.' )
			);

			wp_send_json( $response );
		}

		$data['success'] = true;
		wp_send_json( $data );

	}

	/**
	 * Deletes an elementor league listing template
	 *
	 * fired by 'wp_ajax_elementor_league_listing_delete_template' hook
	 *
	 * @return void
	 */
	public function action_delete_template() {
		$this->setup_request();
		$template_model = $this->template_model_class;

		$template_name = $_REQUEST['template_name'];
		$nonce         = $_REQUEST['_nonce'];

		// Aborts if access not allowed
		$nonce = $this->check_security_access( $nonce );

		// Aborts if default template
		// We should not change default template
		if ( $template_model::is_default_template( $template_name ) ) {
			$data = array(
				'success' => false,
				'message' => __( 'Default template should not be modified.' ),
				'_nonce'  => $nonce,
			);

			wp_send_json( $data );
		}

		// deletes the template
		$template_model::delete_template( $template_name );

		// JSON response
		$templates = array_map( function ( $template ) {
			return $template['name'];
		}, $template_model::get_all_templates() );

		$data = array(
			'success'   => true,
			'_nonce'    => $nonce,
			'templates' => $templates,
			'message'   => __( 'Template removed.' ),
		);

		wp_send_json( $data );

	}

	/**
	 * Updates an elementor league listing template
	 *
	 * fired by 'wp_ajax_elementor_league_listing_update_template' hook
	 *
	 * @return void
	 */
	public function action_update_template() {
		$this->setup_request();
		$template_model = $this->template_model_class;

		$template_name      = $_REQUEST['template_name'];
		$template_container = stripslashes( $_REQUEST['template_container'] );
		$template_item      = stripslashes( $_REQUEST['template_item'] );
		$nonce              = $_REQUEST['_nonce'];

		// Aborts if access not allowed
		$nonce = $this->check_security_access( $nonce );

		// Aborts if default template
		// We should not change default template
		if ( $template_model::is_default_template( $template_name ) ) {
			$data = array(
				'success' => false,
				'message' => __( 'Default template should not be modified. Please rename it.' ),
				'_nonce'  => $nonce,
			);

			wp_send_json( $data );
		}

		// update the template
		$is_new_template = $template_model::update_template( array(
			'name'      => $template_name,
			'container' => $template_container,
			'item'      => $template_item,
		) );

		// JSON response
		$templates = array_map( function ( $template ) {
			return $template['name'];
		}, $template_model::get_all_templates() );

		$message = $is_new_template ? __( 'New template saved.' ) : __( 'Template updated' );
		$data    = array(
			'success'   => true,
			'_nonce'    => $nonce,
			'templates' => $templates,
			'message'   => $message
		);

		wp_send_json( $data );

	}

	/**
	 * Automatically called at initialization
	 */
	protected function init() {
		// hook to query the list of all templates
		add_action(
			'wp_ajax_elementor_get_all_templates',
			array( $this, 'action_get_all_templates' )
		);

		// hook to query a template by template name
		add_action(
			'wp_ajax_elementor_get_template',
			array( $this, 'action_get_template' )
		);

		// hook to save or update a template
		add_action(
			'wp_ajax_elementor_update_template',
			array( $this, 'action_update_template' )
		);

		// hook to delete a template by template name
		add_action(
			'wp_ajax_elementor_delete_template',
			array( $this, 'action_delete_template' )
		);

	}

	/**
	 * Loader
	 */
	public static function load() {
		self::get_instance();
	}

}