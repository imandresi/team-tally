<?php
/**
 * Created by PhpStorm.
 * User: Itanjaka Mandresi
 * Date: 23/03/2023
 * Time: 19:24
 */

namespace TEAMTALLY\Elementor\Includes;

use TEAMTALLY\Elementor\Models\League_Listing_Template_Model;
use TEAMTALLY\Elementor\Widgets\Elementor_League_Listing_Widget;
use TEAMTALLY\System\Helper;

class Elementor_League_Listing_Ajax {

	const HAVE_ACCESS_RIGHTS = TEAMTALLY_USER_CAPABILITY;

	/**
	 * Checks security access to the ajax functionality
	 *
	 * @return string
	 */
	private static function check_security_access( $nonce ) {
		$forbidden = false;

		$message   = '';
		$new_nonce = wp_create_nonce( Elementor_League_Listing_Widget::SECURITY_NONCE );

		if ( ! current_user_can( self::HAVE_ACCESS_RIGHTS ) ) {
			$message   = __( 'Action forbidden.' );
			$forbidden = true;
		}

		// security check
		if ( ! wp_verify_nonce( $nonce, Elementor_League_Listing_Widget::SECURITY_NONCE ) ) {
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
	public static function action_get_all_templates() {
		$templates = League_Listing_Template_Model::get_all_templates();

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
	public static function action_get_template() {
		$template_name = $_REQUEST['template_name'];
		$data          = League_Listing_Template_Model::get_template( $template_name );

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
	public static function action_delete_template() {

		$template_name = $_REQUEST['template_name'];
		$nonce         = $_REQUEST['_nonce'];

		// Aborts if access not allowed
		$nonce = self::check_security_access( $nonce );

		// Aborts if default template
		// We should not change default template
		if ( League_Listing_Template_Model::is_default_template( $template_name ) ) {
			$data = array(
				'success' => false,
				'message' => __( 'Default template should not be modified.' ),
				'_nonce'  => $nonce,
			);

			wp_send_json( $data );
		}

		// deletes the template
		League_Listing_Template_Model::delete_template( $template_name );

		// JSON response
		$templates = array_map( function ( $template ) {
			return $template['name'];
		}, League_Listing_Template_Model::get_all_templates() );

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
	public static function action_update_template() {

		$template_name      = $_REQUEST['template_name'];
		$template_container = stripslashes( $_REQUEST['template_container'] );
		$template_item      = stripslashes( $_REQUEST['template_item'] );
		$nonce              = $_REQUEST['_nonce'];

		// Aborts if access not allowed
		$nonce = self::check_security_access( $nonce );

		// Aborts if default template
		// We should not change default template
		if ( League_Listing_Template_Model::is_default_template( $template_name ) ) {
			$data = array(
				'success' => false,
				'message' => __( 'Default template should not be modified. Please rename it.' ),
				'_nonce'  => $nonce,
			);

			wp_send_json( $data );
		}

		Helper::debug( $template_container, '$template_container', true );

		// update the template
		$is_new_template = League_Listing_Template_Model::update_template( array(
			'name'      => $template_name,
			'container' => $template_container,
			'item'      => $template_item,
		) );

		// JSON response
		$templates = array_map( function ( $template ) {
			return $template['name'];
		}, League_Listing_Template_Model::get_all_templates() );

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
	 * Initialization
	 */
	public static function init() {

		// hook to query the list of all templates
		add_action(
			'wp_ajax_elementor_league_listing_get_all_templates',
			array( self::class, 'action_get_all_templates' )
		);

		// hook to query a template by template name
		add_action(
			'wp_ajax_elementor_league_listing_get_template',
			array( self::class, 'action_get_template' )
		);

		// hook to save or update a template
		add_action(
			'wp_ajax_elementor_league_listing_update_template',
			array( self::class, 'action_update_template' )
		);

		// hook to delete a template by template name
		add_action(
			'wp_ajax_elementor_league_listing_delete_template',
			array( self::class, 'action_delete_template' )
		);

	}

}