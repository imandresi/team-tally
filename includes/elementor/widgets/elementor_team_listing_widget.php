<?php
/**
 * Created by PhpStorm.
 * User: Itanjaka Mandresi
 * Date: 05/03/2023
 * Time: 19:48
 */

namespace TEAMTALLY\Elementor\Widgets;

use TEAMTALLY\Elementor\Elementor_Manager;
use TEAMTALLY\Elementor\Models\Team_Listing_Custom_Css_Model;
use TEAMTALLY\Elementor\Models\Team_Listing_Template_Model;
use TEAMTALLY\System\Helper;
use TEAMTALLY\System\Shared_Data;
use TEAMTALLY\System\Template;

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

	}

	/**
	 * NOT CALLED
	 * TODO: remove perhaps ??
	 * @return string[]
	 */
	public function get_script_depends() {
		return [ 'elementor_team_listing' ];
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

        Shared_Data::share_data_to_js('elementor_team_listing_script', $data, 'before');

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
				'default'      => 'off',
				'return_value' => 'on',
			]
		);

		$this->add_control(
			'league_use_http_query',
			[
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label'        => esc_html__( 'Pick value from HTTP query', TEAMTALLY_TEXT_DOMAIN ),
				'show_label'   => true,
				'label_on'     => esc_html__( 'Yes', TEAMTALLY_TEXT_DOMAIN ),
				'label_off'    => esc_html__( 'No', TEAMTALLY_TEXT_DOMAIN ),
				'default'      => 'off',
				'return_value' => 'on',
				'condition'    => array(
					'league_activate_filtering' => 'on',
				)
			]
		);

		$this->add_control(
			'league',
			[
				'type'       => \Elementor\Controls_Manager::SELECT,
				'label'      => esc_html__( 'League', TEAMTALLY_TEXT_DOMAIN ),
				'show_label' => true,
				'condition'  => array(
					'league_activate_filtering' => 'on',
					'league_use_http_query'     => '',
				),
				'options'    => [
					'2' => 'Premier League',
				]
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
				'default'      => 'off',
				'return_value' => 'on',
			]
		);

		$this->add_control(
			'team_id_use_http_query',
			[
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label'        => esc_html__( 'Pick value from HTTP query', TEAMTALLY_TEXT_DOMAIN ),
				'show_label'   => true,
				'label_on'     => esc_html__( 'Yes', TEAMTALLY_TEXT_DOMAIN ),
				'label_off'    => esc_html__( 'No', TEAMTALLY_TEXT_DOMAIN ),
				'default'      => 'off',
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
					'team_id_use_http_query'     => '',
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
				'default'      => 'off',
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
			'_nonce',
			[
				'type'    => \Elementor\Controls_Manager::HIDDEN,
				'default' => wp_create_nonce( self::SECURITY_NONCE ),
			]
		);

		$this->add_control(
			'custom_css',
			[
				'type'        => \Elementor\Controls_Manager::CODE,
				'label'       => esc_html__( 'CSS Content', TEAMTALLY_TEXT_DOMAIN ),
				'show_label'  => true,
				'label_block' => true,
				'default'     => Team_Listing_Custom_Css_Model::get_css(),
				'language'    => 'html',
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

		$this->add_control(
			'pagination_rows_per_page',
			[
				'type'       => \Elementor\Controls_Manager::NUMBER,
				'label'      => esc_html__( 'Rows per page', TEAMTALLY_TEXT_DOMAIN ),
				'show_label' => true,
				'min'        => '1',
				'max'        => '9999999',
				'default'    => '10',
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
		?>

        <p class="hello-world">
            TEST
        </p>

		<?php
	}

}