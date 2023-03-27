<?php
/**
 * Created by PhpStorm.
 * User: Itanjaka Mandresi
 * Date: 05/03/2023
 * Time: 19:48
 */

namespace TEAMTALLY\Elementor\Widgets;

use TEAMTALLY\Elementor\Elementor_Manager;
use TEAMTALLY\Elementor\Includes\Elementor_League_Listing_Pagination;
use TEAMTALLY\Elementor\Models\Team_Listing_Template_Model;
use TEAMTALLY\Models\Leagues_Model;
use TEAMTALLY\Models\Teams_Model;
use TEAMTALLY\System\Helper;
use TEAMTALLY\System\Shared_Data;
use TEAMTALLY\System\Template;
use WP_Query;

class Elementor_Team_Listing_Widget extends \Elementor\Widget_Base {

	// use other external methods
	use Elementor_Widget_Trait;

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
		return [ 'teamtally-elementor-frontend-style' ];
	}

	/**
	 * Enqueue the js script
	 *
	 * @return void
	 */
	public function widget_enqueue_editor_scripts() {

		// handle:the_handle_value|src:the_src_value|deps:the_dep_value1,the_dep_value2|ver:1.0
		Helper::str_enqueue_script(
			'handle:elementor_team_listing_style|src:css/elementor-widget-style.css',
			true
		);

		Helper::str_enqueue_script(
			'handle:elementor_team_listing_script|src:js/elementor-widget.js',
			true
		);

		// SHARED DATA TO JS
		// TODO: add i18n localization for js labels
		$data = array(
			'widget_name' => self::WIDGET_NAME,
			'nonce'       => wp_create_nonce( self::SECURITY_NONCE ),
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
				'description'  => esc_html__( 'Use HTTP query value from "league_id" if available otherwise use below value', TEAMTALLY_TEXT_DOMAIN ),
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
		$this->content_add_ordering_section( array(
			'1' => 'Name',
			'2' => 'Nickname',
		) );
		$this->content_add_pagination_section();
		$this->content_add_template_section();
		$this->content_add_custom_css();

		// Style Tab
		$this->style_add_odd_row_section();
		$this->style_add_even_row_section();

	}

	/**
	 * Generates the main html result
	 *
	 * This html is used either by PHP or JS
	 *
	 * @param $widget_id
	 * @param $settings
	 * @param $paged
	 *
	 * @return array
	 */
	public static function pre_render( $widget_id, $settings, $paged = 1 ) {

		// get the templates
		$template_name = $settings['chosen_template'];
		$template      = Team_Listing_Template_Model::get_template( $template_name );

		if ( ! $template ) {
			$data = array(
				'success' => false,
				'html'    => __( 'Please choose a template for displaying the list of teams.', TEAMTALLY_TEXT_DOMAIN ),
			);

			return $data;
		}

		$template['item']      = Helper::remove_html_comments( Helper::get_var( $template['item'], '' ) );
		$template['container'] = Helper::remove_html_comments( Helper::get_var( $template['container'], '' ) );

		/*
		 *  initialize query arguments
		 */
		$args = array(
			'post_type'   => Teams_Model::TEAMS_POST_TYPE,
			'post_status' => 'publish',
		);

		// League filtering
		$title = __( 'List of teams' );

		if ( $settings['league_activate_filtering'] ) {
			$http_query_league = $settings['league_use_http_query'] ? Helper::get_var( $_REQUEST['league_id'], false ) : false;
			$league            = $http_query_league ?: $settings['league'];

			$args['tax_query'] = array(
				array(
					'taxonomy' => Leagues_Model::LEAGUES_TAXONOMY_NAME,
					'field'    => 'term_id',
					'terms'    => $league
				)
			);

			if ( $league ) {
				$owner_league = Leagues_Model::get_league( $league );
				$title = $owner_league['data'][ Leagues_Model::LEAGUES_FIELD_NAME ];
				$title        = __(
					sprintf( 'Teams from: %s',  $title),
					TEAMTALLY_TEXT_DOMAIN
				);
			}

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

		if ( $settings['pagination_display_all_rows'] ) {
			$show_paginator = $settings['pagination_enabled'];
			if ( $show_paginator ) {
				$args['nopaging']       = false;
				$args['posts_per_page'] = intval( $settings['pagination_rows_per_page'] );
				$args['paged']          = $paged;
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
			$data = array(
				'success' => false,
				'html'    => __( 'No teams found', TEAMTALLY_TEXT_DOMAIN ),
			);

			return $data;
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
					$pagination   .= Elementor_League_Listing_Pagination::paginate_links( array(
						'base'      => '%_%',
						'format'    => '%#%',
						'current'   => $current_page,
						'total'     => $total_pages,
						'prev_text' => __( '&laquo; Previous' ),
						'next_text' => __( 'Next &raquo;' ),
						'end_size'  => 5,
						'widget_id' => $widget_id,
					) );
				}
			}

			// html from elementor
			$html = Template::parse(
				$template['container'],
				array(
					'title'      => $title,
					'content'    => $html,
					'pagination' => $pagination,
				)
			);
		}

		$data = array(
			'success' => true,
			'html'    => $html,
		);

		return $data;

	}

	/**
	 * Generates the final output
	 *
	 * @return void
	 */
	protected function render() {

		$widget_id = $this->get_id();
		$settings  = $this->get_settings_for_display();

		$data = $this->pre_render( $widget_id, $settings );
		$html = $data['html'];

		// apply template container
		if ( $data['success'] ) {

			// prepares js code used for javascript interaction
			// such as pagination
			$data = array(
				$widget_id => $settings,
				'ajaxurl'  => admin_url( 'admin-ajax.php' ),
			);

			$js_code      = Shared_Data::build_js_from_data( $data );
			$container_id = self::WIDGET_NAME . '_' . $widget_id;

			// builds the final html
			$html = Template::parse(
				'front/teams_listing.php',
				array(
					'elementor_content'    => $html,
					'elementor_custom_css' => $settings['custom_css'],
					'js_code'              => $js_code,
					'container_id'         => $container_id,
				)
			);

		}

		// final display
		print $html;

	}

}