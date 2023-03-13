<?php
/**
 * Created by PhpStorm.
 * User: Itanjaka Mandresi
 * Date: 11/03/2023
 * Time: 08:57
 */

namespace TEAMTALLY\Elementor\Includes;

use TEAMTALLY\Elementor\Models\Team_Listing_Template_Model;
use TEAMTALLY\Elementor\Widgets\Elementor_Team_Listing_Widget;
use TEAMTALLY\System\Helper;

class Elementor_Team_Listing_Ajax {


	/**
	 * Returns an elementor team listing template
	 *
	 * fired by 'wp_ajax_elementor_team_listing_get_template' hook
	 *
	 * @return void
	 */
	public static function action_get_template() {
		$template_name = $_REQUEST['template_name'];
		$data          = Team_Listing_Template_Model::get_template( $template_name );

		if ( ! $data ) {
			$response = array(
				'success' => false,
				'error_msg' => __( 'Template not found.' )
			);

			wp_send_json( $response );
		}

		$data['success'] = true;
		wp_send_json( $data );

	}

	/**
	 * Deletes an elementor team listing template
	 *
	 * fired by 'wp_ajax_elementor_team_listing_delete_template' hook
	 *
	 * @return void
	 */
	public static function action_delete_template() {

		$template_name  = $_REQUEST['template_name'];
		$template_nonce = $_REQUEST['template_nonce'];

		$nonce = wp_create_nonce( Elementor_Team_Listing_Widget::SECURITY_NONCE );

		// security check
		if ( ! wp_verify_nonce( $template_nonce, Elementor_Team_Listing_Widget::SECURITY_NONCE ) ) {
			$response = array(
				'success' => false,
				'error_msg' => __( 'Action forbidden. Please reload the page.' ),
			);

			wp_send_json( $response );
		}

		// deletes the template
		Team_Listing_Template_Model::delete_template( $template_name );

		// JSON response
		$templates = array_map( function ( $template ) {
			return $template['name'];
		}, Team_Listing_Template_Model::get_all_templates() );

		$data = array(
			'success'        => true,
			'template_nonce' => $nonce,
			'templates'      => $templates,
			'notice_msg'     => __( 'Template removed.' ),
		);

		wp_send_json( $data );

	}

	/**
	 * Updates an elementor team listing template
	 *
	 * fired by 'wp_ajax_elementor_team_listing_update_template' hook
	 *
	 * @return void
	 */
	public static function action_update_template() {

		$template_name      = $_REQUEST['template_name'];
		$template_container = $_REQUEST['template_container'];
		$template_item      = $_REQUEST['template_item'];
		$template_nonce     = $_REQUEST['template_nonce'];

		// security check
		if ( ! wp_verify_nonce( $template_nonce, Elementor_Team_Listing_Widget::SECURITY_NONCE ) ) {
			$response = array(
				'success' => false,
				'error_msg' => __( 'Action forbidden. Please reload the page.' )
			);

			wp_send_json( $response );
		}

		// update the template
		$is_new_template = Team_Listing_Template_Model::update_template( array(
			'name'      => $template_name,
			'container' => $template_container,
			'item'      => $template_item,
		) );

		// JSON response
		$templates = array_map( function ( $template ) {
			return $template['name'];
		}, Team_Listing_Template_Model::get_all_templates() );

		$notice_msg = $is_new_template ? __('New template saved.') : __('Template updated');
		$data = array(
			'success'        => true,
			'template_nonce' => wp_create_nonce( Elementor_Team_Listing_Widget::SECURITY_NONCE ),
			'templates'      => $templates,
			'notice_msg'     => $notice_msg
		);

		wp_send_json( $data );

	}

	/**
	 * Initialization
	 */
	public static function init() {

		// hook to query a template by template name
		add_action(
			'wp_ajax_elementor_team_listing_get_template',
			array( self::class, 'action_get_template' )
		);

		// hook to save or update a template
		add_action(
			'wp_ajax_elementor_team_listing_update_template',
			array( self::class, 'action_update_template' )
		);

		// hook to delete a template by template name
		add_action(
			'wp_ajax_elementor_team_listing_delete_template',
			array( self::class, 'action_delete_template' )
		);

	}

}