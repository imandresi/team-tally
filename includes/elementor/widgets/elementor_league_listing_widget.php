<?php
/**
 * Created by PhpStorm.
 * User: Itanjaka Mandresi
 * Date: 05/03/2023
 * Time: 19:48
 */

namespace TEAMTALLY\Elementor\Widgets;

use TEAMTALLY\Elementor\Elementor_Manager;

use TEAMTALLY\Elementor\Models\League_Listing_Template_Model;
use TEAMTALLY\Models\Leagues_Model;
use TEAMTALLY\System\Helper;
use TEAMTALLY\System\Shared_Data;
use TEAMTALLY\System\Template;
use WP_Query;

class Elementor_League_Listing_Widget extends \Elementor\Widget_Base {

	// use other external methods
	use Elementor_Widget_Trait;

	// slug of the widget
	const WIDGET_NAME = 'league_listing_widget';

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
		return [ 'elementor_league_listing' ];
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
			'handle:elementor_league_listing_style|src:css/elementor-widget-style.css',
			true
		);

		Helper::str_enqueue_script(
			'handle:elementor_league_listing_script|src:js/elementor-widget.js',
			true
		);

		// SHARED DATA TO JS
		// TODO: add i18n localization for js labels
		$data = array(
			'widget_name' => self::WIDGET_NAME,
			'nonce'       => wp_create_nonce( self::SECURITY_NONCE ),
		);

		Shared_Data::share_data_to_js( 'elementor_league_listing_script', $data, 'before' );

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
		return esc_html__( 'League Listing', TEAMTALLY_TEXT_DOMAIN );
	}

	/**
	 * Icon associated to the widget
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-apps';
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
		return [ 'league' ];
	}

	/**
	 * Adds the FILTER section for the league
	 *
	 * @return void
	 */
	protected function content_add_league_id_filter_section() {

		// Content Tab Start
		$this->start_controls_section(
			'league_filter_section',
			[
				'label' => esc_html__( 'FILTER BY LEAGUE #ID', TEAMTALLY_TEXT_DOMAIN ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		// Activate league id filtering
		$this->add_control(
			'league_id_activate_filtering',
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

		// LEAGUE control
		$this->add_control(
			'league_id',
			[
				'type'       => \Elementor\Controls_Manager::NUMBER,
				'label'      => esc_html__( 'League #ID', TEAMTALLY_TEXT_DOMAIN ),
				'show_label' => true,
				'min'        => '1',
				'max'        => '9999999',
				'default'    => '',
				'condition'  => array(
					'league_id_activate_filtering' => 'on',
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
	protected function content_add_country_filter_section() {

		$this->start_controls_section(
			'country_filter_section',
			[
				'label' => esc_html__( 'ADD COUNTRY FILTERING', TEAMTALLY_TEXT_DOMAIN ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		// Activate country filtering
		$this->add_control(
			'country_activate_filtering',
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

		// Country
		$league_countries = Leagues_Model::get_all_countries();
		$country_options  = array();
		foreach ( $league_countries as $league_country ) {
			$country_options[ $league_country ] = $league_country;
		}

		$this->add_control(
			'country',
			[
				'type'       => \Elementor\Controls_Manager::SELECT,
				'label'      => esc_html__( 'Country', TEAMTALLY_TEXT_DOMAIN ),
				'show_label' => true,
				'options'    => $country_options,
				'default'    => '',
				'condition'  => array(
					'country_activate_filtering' => 'on',
				)

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
		$this->content_add_league_id_filter_section();
		$this->content_add_country_filter_section();
		$this->content_add_ordering_section( array(
			'1' => 'League Name',
			'2' => 'League Country',
		) );
		$this->content_add_template_section();
		$this->content_add_custom_css();

	}

	/**
	 * Generates the final output
	 *
	 * @return void
	 */
	protected function render() {

		$settings = $this->get_settings_for_display();

		// get the templates
		$template_name = $settings['chosen_template'];
		$template      = League_Listing_Template_Model::get_template( $template_name );

		if ( ! $template ) {
			_e( 'Please choose a template for displaying the list of leagues.', TEAMTALLY_TEXT_DOMAIN );

			return;
		}

		$template['item']      = Helper::remove_html_comments( Helper::get_var( $template['item'], '' ) );
		$template['container'] = Helper::remove_html_comments( Helper::get_var( $template['container'], '' ) );

		/*
		 *  initialize query arguments
		 */
		$args = array(
			'taxonomy'   => Leagues_Model::LEAGUES_TAXONOMY_NAME,
			'hide_empty' => false,
		);

		// League #ID filtering
		$league_id = '';
		if ( $settings['league_id_activate_filtering'] ) {
			$league_id = $settings['league_id'];
			if ( $league_id ) {
				$args['include'] = array( $league_id );
			}
		}

		// Country filtering
		if ( $settings['country_activate_filtering'] ) {
			$country = $settings['country'];
			if ( $country ) {
				$args['meta_key']   = Leagues_Model::LEAGUES_FIELD_COUNTRY;
				$args['meta_value'] = $country;
			}
		}

		// Rows ordering
		switch ( $settings['order_by_field'] ) {

			// order by 'league name'
			case '1':
				$args['orderby'] = 'name';
				break;

			// order by 'league country'
			case '2':
			default:
				$args['orderby'] = Leagues_Model::LEAGUES_FIELD_COUNTRY;

				break;
		}

		$args['order'] = $settings['order_by_direction'] == 1 ? 'ASC' : 'DESC';

		/*
		 * Build items content
		 */
		$html  = '';
		$terms = get_terms( $args );

		if ( ! is_wp_error( $terms ) ) {

			// Builds each league items html
			$counter = 0;

			foreach ( $terms as $term ) {
				$term_data = Leagues_Model::get_league( $term );
				if ( ! $term_data ) {
					continue;
				}

				$counter ++;
				$term_id        = Helper::get_var( $term_data['data']['term_id'] );
				$league_name    = Helper::get_var( $term_data['data'][ Leagues_Model::LEAGUES_FIELD_NAME ], '' );
				$league_country = Helper::get_var( $term_data['data'][ Leagues_Model::LEAGUES_FIELD_COUNTRY ], '' );
				$league_logo_id = Helper::get_var( $term_data['data'][ Leagues_Model::LEAGUES_FIELD_PHOTO ]['id'], '' );

				$league_logo_url = '';
				if ( $league_logo_id ) {
					$league_logo_url = wp_get_attachment_image_url(
						$league_logo_id,
						array( 500, 500 ),
						false
					);
				}

				$class = count( $terms ) == 1 ? 'only-item' :
					( $counter >= count( $terms ) ? 'last-item' :
						( $counter == 1 ? 'first-item' : '' ) );

				$class .= $counter % 2 == 0 ? ' odd' : ' even';

				$params = array(
					'league_id'       => $term_id,
					'league_name'     => $league_name,
					'league_country'  => $league_country,
					'league_logo_url' => $league_logo_url,
					'class'           => $class,
				);

				// builds item html
				$html .= Template::parse(
					$template['item'], $params
				);

			}

			// Builds the html container
			if ( $html ) {

				$html = Template::parse(
					$template['container'],
					array(
						'content' => $html,
					)
				);

				// final html - adds the 'custom css' style
				$html = Template::parse(
					'front/leagues_listing.php',
					array(
						'elementor_content'    => $html,
						'elementor_custom_css' => $settings['custom_css'],
					)
				);

			}

		}

		if ( ! $html ) {
			$html = __( 'No leagues found', TEAMTALLY_TEXT_DOMAIN );
		}

		// final display
		print $html;

	}

}