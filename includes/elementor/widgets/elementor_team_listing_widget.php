<?php
/**
 * Created by PhpStorm.
 * User: Itanjaka Mandresi
 * Date: 05/03/2023
 * Time: 19:48
 */

namespace TEAMTALLY\Elementor\Widgets;

use TEAMTALLY\Elementor\Elementor_Manager;
use TEAMTALLY\Elementor\Models\Team_Listing_Template_Model;
use TEAMTALLY\Models\Leagues_Model;
use TEAMTALLY\Models\Teams_Model;
use TEAMTALLY\System\Dynamic_Css;
use TEAMTALLY\System\Helper;
use TEAMTALLY\System\Shared_Data;
use TEAMTALLY\System\Template;
use WP_Query;

class Elementor_Team_Listing_Widget extends \Elementor\Widget_Base {

	// slug of the widget
	const WIDGET_NAME = 'team_listing_widget';

	const SECURITY_NONCE = 'ajax_security';

	/**
	 * Constructor
	 *
	 * @param $data
	 * @param $args
	 *
	 * @throws \Exception
	 */
	public function __construct( $data = [], $args = null ) {
		parent::__construct( $data, $args );

		// script to load
		add_action( 'elementor/editor/after_enqueue_scripts', array( $this, 'widget_enqueue_editor_scripts' ) );

		// loads frontend styles
		add_action( 'elementor/preview/enqueue_styles', array( $this, 'enqueue_frontend_styles' ) );

	}

	/**
	 * Loads all the frontend stylesheets
	 *
	 * @return void
	 */
	public function enqueue_frontend_styles() {

		// register widget styles
		wp_register_style(
			'elementor-frontend-style',
			TEAMTALLY_ASSETS_CSS_URI . 'elementor-frontend-style.css',
			[],
			Helper::version( true )
		);

	}


	/**
	 * Script dependencies
	 *
	 * @return string[]
	 */
	public function get_script_depends() {
		return [ 'elementor_team_listing' ];
	}

	/**
	 * Styles dependencies
	 *
	 * @return string[]
	 */
	public function get_style_depends() {
		return [ 'elementor-frontend-style' ];
	}

	/**
	 * Enqueue the js script
	 *
	 * @return void
	 */
	public function widget_enqueue_editor_scripts() {

		// handle:the_handle_value|src:the_src_value|deps:the_dep_value1,the_dep_value2|ver:1.0
		Helper::str_enqueue_script(
			'handle:elementor_team_listing_style|src:css/elementor-team-listing-style.css',
			true
		);

		Helper::str_enqueue_script(
			'handle:elementor_team_listing_script|src:js/elementor-team-listing.js',
			true
		);

		// SHARED DATA TO JS
		// TODO: add i18n localization for js labels
		$data = array(
			'nonce' => wp_create_nonce( self::SECURITY_NONCE ),
		);

		Shared_Data::share_data_to_js( 'elementor_team_listing_script', $data, 'before' );

	}

	/**
	 * Returns the slug of the widget
	 *
	 * @return string
	 */
	public function get_name() {
		return self::WIDGET_NAME;
	}

	/**
	 * Title of the widget in the widget panel
	 *
	 * @return string
	 */
	public function get_title() {
		return esc_html__( 'Team Listing', TEAMTALLY_TEXT_DOMAIN );
	}

	/**
	 * Icon associated to the widget
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-posts-grid';
	}

	/**
	 * Associates the widget inside the new created TEAM TALLY category
	 *
	 * @return array
	 */
	public function get_categories() {
		return [ Elementor_Manager::ELEMENTOR_CATEGORY_SLUG ];
	}

	/**
	 * Used to look for the widget in the finder
	 *
	 * @return string[]
	 */
	public function get_keywords() {
		return [ 'team' ];
	}

	/**
	 * Adds the FILTER section for the league
	 *
	 * @return void
	 */
	protected function content_add_league_filter_section() {

		// Content Tab Start
		$this->start_controls_section(
			'league_filter_section',
			[
				'label' => esc_html__( 'ADD LEAGUE FILTERING', TEAMTALLY_TEXT_DOMAIN ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		// LEAGUE control
		$this->add_control(
			'league_activate_filtering',
			[
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label'        => esc_html__( 'Activate', TEAMTALLY_TEXT_DOMAIN ),
				'show_label'   => true,
				'label_on'     => esc_html__( 'Yes', TEAMTALLY_TEXT_DOMAIN ),
				'label_off'    => esc_html__( 'No', TEAMTALLY_TEXT_DOMAIN ),
				'default'      => '',
				'return_value' => 'on',
			]
		);

		$this->add_control(
			'league_use_http_query',
			[
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label'        => esc_html__( 'Pick value from HTTP query first', TEAMTALLY_TEXT_DOMAIN ),
				'description'  => esc_html__( 'Use HTTP query value if available otherwise use below value', TEAMTALLY_TEXT_DOMAIN ),
				'show_label'   => true,
				'label_on'     => esc_html__( 'Yes', TEAMTALLY_TEXT_DOMAIN ),
				'label_off'    => esc_html__( 'No', TEAMTALLY_TEXT_DOMAIN ),
				'default'      => '',
				'return_value' => 'on',
				'condition'    => array(
					'league_activate_filtering' => 'on',
				)
			]
		);

		// prepare league control options
		$leagues        = Leagues_Model::get_all_leagues();
		$league_options = array();

		if ( $leagues ) {
			foreach ( $leagues as $league ) {
				$data = $league['data'];
				if ( ! $data ) {
					continue;
				}
				$league_options[ $data['term_id'] ] = $data[ Leagues_Model::LEAGUES_FIELD_NAME ];
			}
		}

		$this->add_control(
			'league',
			[
				'type'       => \Elementor\Controls_Manager::SELECT,
				'label'      => esc_html__( 'League', TEAMTALLY_TEXT_DOMAIN ),
				'show_label' => true,
				'condition'  => array(
					'league_activate_filtering' => 'on',
				),
				'options'    => $league_options,
			]

		);

		$this->end_controls_section();

	}

	/**
	 * Adds the filter section for the team #id
	 * @return void
	 */
	protected function content_add_team_id_filter_section() {
		// Content Tab Start

		$this->start_controls_section(
			'team_id_filter_section',
			[
				'label' => esc_html__( 'ADD TEAM #ID FILTERING', TEAMTALLY_TEXT_DOMAIN ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'team_id_activate_filtering',
			[
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label'        => esc_html__( 'Activate', TEAMTALLY_TEXT_DOMAIN ),
				'show_label'   => true,
				'label_on'     => esc_html__( 'Yes', TEAMTALLY_TEXT_DOMAIN ),
				'label_off'    => esc_html__( 'No', TEAMTALLY_TEXT_DOMAIN ),
				'default'      => '',
				'return_value' => 'on',
			]
		);

		$this->add_control(
			'team_id_use_http_query',
			[
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label'        => esc_html__( 'Pick value from HTTP query first', TEAMTALLY_TEXT_DOMAIN ),
				'description'  => esc_html__( 'Use HTTP query value if available otherwise use below value', TEAMTALLY_TEXT_DOMAIN ),
				'show_label'   => true,
				'label_on'     => esc_html__( 'Yes', TEAMTALLY_TEXT_DOMAIN ),
				'label_off'    => esc_html__( 'No', TEAMTALLY_TEXT_DOMAIN ),
				'default'      => '',
				'return_value' => 'on',
				'condition'    => array(
					'team_id_activate_filtering' => 'on',
				)
			]
		);

		$this->add_control(
			'team_id',
			[
				'type'       => \Elementor\Controls_Manager::NUMBER,
				'label'      => esc_html__( 'Team #ID', TEAMTALLY_TEXT_DOMAIN ),
				'show_label' => true,
				'min'        => '1',
				'max'        => '9999999',
				'condition'  => array(
					'team_id_activate_filtering' => 'on',
				)
			]

		);

		$this->end_controls_section();

	}

	/**
	 * Adds the keyword section
	 *
	 * @return void
	 */
	protected function content_add_keyword_filter_section() {

		$this->start_controls_section(
			'keyword_filter_section',
			[
				'label' => esc_html__( 'ADD KEYWORD FILTERING', TEAMTALLY_TEXT_DOMAIN ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'keyword_activate_filtering',
			[
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label'        => esc_html__( 'Activate', TEAMTALLY_TEXT_DOMAIN ),
				'show_label'   => true,
				'label_on'     => esc_html__( 'Yes', TEAMTALLY_TEXT_DOMAIN ),
				'label_off'    => esc_html__( 'No', TEAMTALLY_TEXT_DOMAIN ),
				'default'      => '',
				'return_value' => 'on',
			]
		);

		$this->add_control(
			'keyword',
			[
				'type'       => \Elementor\Controls_Manager::TEXT,
				'label'      => esc_html__( 'Keyword', TEAMTALLY_TEXT_DOMAIN ),
				'show_label' => true,
				'default'    => '',
				'condition'  => array(
					'keyword_activate_filtering' => 'on',
				)
			]
		);

		$this->end_controls_section();

	}

	/**
	 * Adds 'Order By' section
	 *
	 * @return void
	 */
	protected function content_add_ordering_section() {
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
				'options'    => [
					'1' => 'Name',
					'2' => 'Nickname',
				],
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

	/**
	 * Adds the odd row section for style
	 *
	 * @return void
	 */
	protected function style_add_odd_row_section() {
		$this->start_controls_section(
			'style_odd_row_section',
			[
				'label' => esc_html__( 'ODD ROWS', TEAMTALLY_TEXT_DOMAIN ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'odd_row_text_color',
			[
				'label'     => esc_html__( 'Text Color', TEAMTALLY_TEXT_DOMAIN ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .hello-world' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'odd_row_background_color',
			[
				'label'     => esc_html__( 'Background Color', TEAMTALLY_TEXT_DOMAIN ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .hello-world' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'odd_row_typography',
				'selector' => '{{WRAPPER}} .your-class',
			]
		);

		$this->end_controls_section();


	}

	/**
	 * Adds the odd row section for style
	 *
	 * @return void
	 */
	protected function style_add_even_row_section() {

		$this->start_controls_section(
			'style_even_row_section',
			[
				'label' => esc_html__( 'EVEN ROWS', TEAMTALLY_TEXT_DOMAIN ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'even_row_text_color',
			[
				'label'     => esc_html__( 'Text Color', TEAMTALLY_TEXT_DOMAIN ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .hello-world' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'even_row_background_color',
			[
				'label'     => esc_html__( 'Background Color', TEAMTALLY_TEXT_DOMAIN ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .hello-world' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'even_row_typography',
				'selector' => '{{WRAPPER}} .your-class',
			]
		);

		$this->end_controls_section();


	}

	/**
	 * Registering all controls
	 *
	 * -- Fired internally by Elementor --
	 *
	 * @return void
	 */
	protected function register_controls() {

		// Content Tab
		$this->content_add_league_filter_section();
		$this->content_add_team_id_filter_section();
		$this->content_add_keyword_filter_section();
		$this->content_add_ordering_section();
		$this->content_add_pagination_section();
		$this->content_add_template_section();
		$this->content_add_custom_css();

		// Style Tab
		$this->style_add_odd_row_section();
		$this->style_add_even_row_section();

	}

	protected function render() {

		$settings = $this->get_settings_for_display();

		// get the templates
		$template_name = $settings['chosen_template'];
		$template      = Team_Listing_Template_Model::get_template( $template_name );

		if ( ! $template ) {
			_e( 'Please choose a template for displaying the list of teams.', TEAMTALLY_TEXT_DOMAIN );

			return;
		}

		/*
		 *  initialize query arguments
		 */
		$args = array(
			'post_type'   => Teams_Model::TEAMS_POST_TYPE,
			'post_status' => 'publish',
		);

		// League filtering
		if ( $settings['league_activate_filtering'] ) {
			$http_query_league = $settings['league_use_http_query'] ? Helper::get_var( $_REQUEST['league'], false ) : false;
			$league            = $http_query_league ?: $settings['league'];

			$args['tax_query'] = array(
				array(
					'taxonomy' => Leagues_Model::LEAGUES_TAXONOMY_NAME,
					'field'    => 'term_id',
					'terms'    => $league
				)
			);
		}

		// Team #ID filtering
		if ( $settings['team_id_activate_filtering'] ) {
			$http_query_team_id = $settings['team_id_use_http_query'] ? Helper::get_var( $_REQUEST['team_id'], false ) : false;
			$team_id            = $http_query_team_id ?: $settings['team_id'];

			$args['p'] = $team_id;
		}

		// Keyword filtering
		if ( $settings['keyword_activate_filtering'] ) {
			$args['s'] = $settings['keyword'];
		}

		// Rows ordering
		$args['orderby'] = $settings['order_by_field'] == 1 ? 'team_name' : 'team_nickname';
		$args['order']   = $settings['order_by_direction'] == 1 ? 'ASC' : 'DESC';

		// Pagination
		$rows_to_display  = - 1;
		$show_paginator   = false;
		$args['nopaging'] = true;
		$paged            = 1;

		if ( $settings['pagination_display_all_rows'] ) {
			$show_paginator = $settings['pagination_enabled'];
			if ( $show_paginator ) {
				$args['nopaging']       = false;
				$args['posts_per_page'] = intval( $settings['pagination_rows_per_page'] );

				$paged         = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
				$args['paged'] = $paged;
			}
		} else {
			$rows_to_display = intval( $settings['pagination_rows_to_display'] );
		}

		/*
		 * Build items content
		 */
		$html        = '';
		$posts_count = 0;
		$query       = new WP_Query( $args );

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$posts_count ++;
				$post       = $query->post;
				$item_class = 'team-item ';

				// extract data
				$team          = Teams_Model::get_team( $post );
				$team_id       = Helper::get_var( $team['data']['ID'], '' );
				$team_name     = Helper::get_var( $team['data'][ Teams_Model::TEAMS_FIELD_NAME ], '' );
				$team_nickname = Helper::get_var( $team['data'][ Teams_Model::TEAMS_FIELD_NICKNAME ], '' );
				$team_history  = Helper::get_var( $team['data'][ Teams_Model::TEAMS_FIELD_HISTORY ], '' );
				$team_logo_url = Helper::get_var( $team['data'][ Teams_Model::TEAMS_FIELD_LOGO ]['URL'], '' );

				// builds css class
				$item_status = $posts_count == 1 ? 'first-item' : '';

				if ( ( ( $rows_to_display > 0 ) && ( $posts_count >= $rows_to_display ) ) ||
				     ( $posts_count >= $query->post_count ) ) {
					$item_status = 'last-item';
				}

				if ( $rows_to_display == 1 ) {
					$item_status = 'only-item';
				}

				$item_class .= $item_status . ' ';
				$item_class .= $posts_count % 2 ? 'even' : 'odd';

				$league_id       = '';
				$league_name     = '';
				$league_country  = '';
				$league_logo_url = '';

				$terms = get_the_terms( $post, Leagues_Model::LEAGUES_TAXONOMY_NAME );
				if ( $terms ) {
					$league         = Leagues_Model::get_league( $terms[0] );
					$league_id      = Helper::get_var( $league['data']['term_id'], '' );
					$league_name    = Helper::get_var( $league['data'][ Leagues_Model::LEAGUES_FIELD_NAME ], '' );
					$league_country = Helper::get_var( $league['data'][ Leagues_Model::LEAGUES_FIELD_COUNTRY ], '' );

					$league_logo_id = Helper::get_var( $league['data'][ Leagues_Model::LEAGUES_FIELD_PHOTO ]['id'], null );

					if ( $league_logo_id ) {
						$league_logo_url = wp_get_attachment_image_url(
							$league_logo_id,
							array( 500, 500 ),
							false
						);
					}
				}

				$params = array(
					'league_id'       => $league_id,
					'team_id'         => $team_id,
					'team_name'       => $team_name,
					'team_nickname'   => $team_nickname,
					'team_history'    => $team_history,
					'team_logo_url'   => $team_logo_url,
					'league_name'     => $league_name,
					'league_country'  => $league_country,
					'league_logo_url' => $league_logo_url,
					'class'           => $item_class,
				);

				// builds item html
				$html .= Template::parse(
					$template['item'], $params
				);

				if ( ( $rows_to_display > 0 ) && ( $posts_count >= $rows_to_display ) ) {
					break;
				}

			}
		} else {
			$html = __( 'No posts found', TEAMTALLY_TEXT_DOMAIN );
		}

		wp_reset_postdata();

		// apply template container
		if ( $posts_count ) {

			// build paginator
			$pagination = '';

			if ( $show_paginator ) {
				$total_pages = $query->max_num_pages;
				if ( $total_pages > 1 ) {
					$current_page = $paged;
					$pagination   .= paginate_links( array(
						'base'      => get_pagenum_link( 1 ) . '%_%',
						'format'    => '/page/%#%',
						'current'   => $current_page,
						'total'     => $total_pages,
						'prev_text' => __( '&laquo; Previous' ),
						'next_text' => __( 'Next &raquo;' )
					) );
				}
			}

			// html from elementor
			$html = Template::parse(
				$template['container'],
				array(
					'content'    => $html,
					'pagination' => $pagination,
				)
			);

			// final html - adds the 'custom css' style
			$html = Template::parse(
				'front/teams/teams_listing.php',
				array(
					'elementor_content' => $html,
					'elementor_custom_css' => $settings['custom_css'],
				)
			);

		}

		// final display
		print $html;

	}

}