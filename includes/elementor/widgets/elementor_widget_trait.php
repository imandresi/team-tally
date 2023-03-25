<?php
/**
 * Created by PhpStorm.
 * User: Itanjaka Mandresi
 * Date: 22/03/2023
 * Time: 19:25
 */

namespace TEAMTALLY\Elementor\Widgets;

use TEAMTALLY\Elementor\Models\Team_Listing_Template_Model;
use TEAMTALLY\System\Template;

trait Elementor_Widget_Trait {

	/**
	 * Adds 'Order By' section
	 *
	 * @return void
	 */
	protected function content_add_ordering_section($options) {
		$this->start_controls_section(
			'ordering_section',
			[
				'label' => esc_html__( 'SET ROWS ORDERING', TEAMTALLY_TEXT_DOMAIN ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'order_by_field',
			[
				'type'       => \Elementor\Controls_Manager::SELECT,
				'label'      => esc_html__( 'Order By', TEAMTALLY_TEXT_DOMAIN ),
				'show_label' => true,
				'options'    => $options,
				'default'    => '1'
			]

		);

		$this->add_control(
			'order_by_direction',
			[
				'type'       => \Elementor\Controls_Manager::SELECT,
				'label'      => esc_html__( 'Direction', TEAMTALLY_TEXT_DOMAIN ),
				'show_label' => true,
				'options'    => [
					'1' => 'ASC',
					'2' => 'DESC',
				],
				'default'    => '1',
			]

		);

		$this->end_controls_section();

	}


	/**
	 * Add template section
	 *
	 * @return void
	 */
	protected function content_add_template_section() {

		$this->start_controls_section(
			'template_section',
			[
				'label' => esc_html__( 'MANAGE OUTPUT TEMPLATE', TEAMTALLY_TEXT_DOMAIN ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'template_admin_notice',
			[
				'type'            => \Elementor\Controls_Manager::RAW_HTML,
				'raw'             => Template::parse( 'admin/common/admin_notice.php' ),
				'content_classes' => 'notice notice-error is-dismissible is-hidden',
			]
		);

		// ---------------------
		// TAB: CHOOSE TEMPLATE
		// ---------------------
		$this->start_controls_tabs(
			'template_tabs'
		);

		$this->start_controls_tab(
			'use_template_tab',
			[
				'label' => esc_html__( 'Choose', 'textdomain' ),
			]
		);

		// loads list of available templates
		$templates      = Team_Listing_Template_Model::get_all_templates();
		$select_options = array();
		foreach ( $templates as $template ) {
			$key                    = $template['name'];
			$select_options[ $key ] = $key;
		}

		$this->add_control(
			'chosen_template',
			[
				'type'       => \Elementor\Controls_Manager::SELECT,
				'label'      => esc_html__( 'Use template', TEAMTALLY_TEXT_DOMAIN ),
				'show_label' => true,
				'options'    => $select_options,
				'default'    => '',
			]
		);

		$this->end_controls_tab();

		// ---------------------
		// TAB: EDIT TEMPLATE
		// ---------------------
		$this->start_controls_tab(
			'edit_template_tab',
			[
				'label' => esc_html__( 'Edit', 'textdomain' ),
			]
		);

		$this->add_control(
			'template_pending',
			[
				'type'            => \Elementor\Controls_Manager::RAW_HTML,
				'raw'             => '',
				'content_classes' => 'template_pending_spinner',
			]
		);

		$this->add_control(
			'template_name',
			[
				'type'        => \Elementor\Controls_Manager::TEXT,
				'label'       => esc_html__( 'Template Name', TEAMTALLY_TEXT_DOMAIN ),
				'description' => esc_html__(
					'If you edit a template and you change this name, a new template will be created',
					TEAMTALLY_TEXT_DOMAIN
				),
				'show_label'  => true,
				'label_block' => true,
				'default'     => '',
			]
		);

		$this->add_control(
			'template_container',
			[
				'type'        => \Elementor\Controls_Manager::CODE,
				'label'       => esc_html__( 'Container template', TEAMTALLY_TEXT_DOMAIN ),
				'show_label'  => true,
				'label_block' => true,
				'default'     => '',
				'language'    => 'html',
			]
		);

		$this->add_control(
			'template_item',
			[
				'type'        => \Elementor\Controls_Manager::CODE,
				'label'       => esc_html__( 'Item template', TEAMTALLY_TEXT_DOMAIN ),
				'show_label'  => true,
				'label_block' => true,
				'default'     => '',
				'language'    => 'html',
			]
		);

		$this->add_control(
			'template_btn_remove',
			[
				'type'        => \Elementor\Controls_Manager::BUTTON,
				'text'        => esc_html__( 'Remove', TEAMTALLY_TEXT_DOMAIN ),
				'show_label'  => true,
				'label_block' => false,
			]
		);

		$this->add_control(
			'template_btn_save',
			[
				'type'        => \Elementor\Controls_Manager::BUTTON,
				'text'        => esc_html__( 'Save', TEAMTALLY_TEXT_DOMAIN ),
				'show_label'  => true,
				'label_block' => false,
				'button_type' => 'success',
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

	}

	/**
	 * Adds custom css section
	 *
	 * @return void
	 */
	protected function content_add_custom_css() {

		$this->start_controls_section(
			'custom_css_section',
			[
				'label' => esc_html__( 'SET CUSTOM CSS', TEAMTALLY_TEXT_DOMAIN ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'custom_css',
			[
				'type'        => \Elementor\Controls_Manager::CODE,
				'label'       => esc_html__( 'CSS Content', TEAMTALLY_TEXT_DOMAIN ),
				'show_label'  => true,
				'label_block' => true,
				'default'     => '',
				'language'    => 'css',
			]
		);

		$this->end_controls_section();

	}

	/**
	 * Adds the pagination section
	 *
	 * @return void
	 */
	protected function content_add_pagination_section() {

		$this->start_controls_section(
			'pagination_section',
			[
				'label' => esc_html__( 'SET PAGINATION', TEAMTALLY_TEXT_DOMAIN ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'pagination_display_all_rows',
			[
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label'        => esc_html__( 'Display all rows', TEAMTALLY_TEXT_DOMAIN ),
				'show_label'   => true,
				'label_on'     => esc_html__( 'Yes', TEAMTALLY_TEXT_DOMAIN ),
				'label_off'    => esc_html__( 'No', TEAMTALLY_TEXT_DOMAIN ),
				'default'      => 'on',
				'return_value' => 'on',
			]
		);


		$this->add_control(
			'pagination_enabled',
			[
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label'        => esc_html__( 'Enable pagination', TEAMTALLY_TEXT_DOMAIN ),
				'show_label'   => true,
				'label_on'     => esc_html__( 'Yes', TEAMTALLY_TEXT_DOMAIN ),
				'label_off'    => esc_html__( 'No', TEAMTALLY_TEXT_DOMAIN ),
				'default'      => '',
				'return_value' => 'on',
				'condition'    => array(
					'pagination_display_all_rows' => 'on',
				)
			]
		);


		$this->add_control(
			'pagination_rows_per_page',
			[
				'type'       => \Elementor\Controls_Manager::NUMBER,
				'label'      => esc_html__( 'Rows per page for pagination', TEAMTALLY_TEXT_DOMAIN ),
				'show_label' => true,
				'min'        => '1',
				'max'        => '9999999',
				'default'    => '10',
				'condition'  => array(
					'pagination_display_all_rows' => 'on',
					'pagination_enabled'          => 'on',
				)
			]

		);


		$this->add_control(
			'pagination_rows_to_display',
			[
				'type'       => \Elementor\Controls_Manager::NUMBER,
				'label'      => esc_html__( 'Number of rows to display', TEAMTALLY_TEXT_DOMAIN ),
				'show_label' => true,
				'min'        => '1',
				'max'        => '9999999',
				'default'    => '1',
				'condition'  => array(
					'pagination_display_all_rows' => '',
				)
			]

		);


		$this->end_controls_section();
	}

}