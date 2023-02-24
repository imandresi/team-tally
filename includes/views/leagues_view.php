<?php
/**
 * Created by PhpStorm.
 * User: Itanjaka Mandresi
 * Date: 22/02/2023
 * Time: 11:54
 */

namespace TEAMTALLY\Views;

use TEAMTALLY\Models\Leagues_Model;
use TEAMTALLY\System\Helper;
use TEAMTALLY\System\Template;
use WP_Post;

class Leagues_View {

	private static function get_country_list() {

		/** @var array $country_list */
		$country_list = array(
			"Afghanistan",
			"Aland Islands",
			"Albania",
			"Algeria",
			"American Samoa",
			"Andorra",
			"Angola",
			"Anguilla",
			"Antarctica",
			"Antigua and Barbuda",
			"Argentina",
			"Armenia",
			"Aruba",
			"Australia",
			"Austria",
			"Azerbaijan",
			"Bahamas",
			"Bahrain",
			"Bangladesh",
			"Barbados",
			"Belarus",
			"Belgium",
			"Belize",
			"Benin",
			"Bermuda",
			"Bhutan",
			"Bolivia",
			"Bonaire, Sint Eustatius and Saba",
			"Bosnia and Herzegovina",
			"Botswana",
			"Bouvet Island",
			"Brazil",
			"British Indian Ocean Territory",
			"Brunei Darussalam",
			"Bulgaria",
			"Burkina Faso",
			"Burundi",
			"Cambodia",
			"Cameroon",
			"Canada",
			"Cape Verde",
			"Cayman Islands",
			"Central African Republic",
			"Chad",
			"Chile",
			"China",
			"Christmas Island",
			"Cocos (Keeling) Islands",
			"Colombia",
			"Comoros",
			"Congo",
			"Congo, Democratic Republic of the Congo",
			"Cook Islands",
			"Costa Rica",
			"Cote D'Ivoire",
			"Croatia",
			"Cuba",
			"Curacao",
			"Cyprus",
			"Czech Republic",
			"Denmark",
			"Djibouti",
			"Dominica",
			"Dominican Republic",
			"Ecuador",
			"Egypt",
			"El Salvador",
			"Equatorial Guinea",
			"Eritrea",
			"Estonia",
			"Ethiopia",
			"Falkland Islands (Malvinas)",
			"Faroe Islands",
			"Fiji",
			"Finland",
			"France",
			"French Guiana",
			"French Polynesia",
			"French Southern Territories",
			"Gabon",
			"Gambia",
			"Georgia",
			"Germany",
			"Ghana",
			"Gibraltar",
			"Greece",
			"Greenland",
			"Grenada",
			"Guadeloupe",
			"Guam",
			"Guatemala",
			"Guernsey",
			"Guinea",
			"Guinea-Bissau",
			"Guyana",
			"Haiti",
			"Heard Island and Mcdonald Islands",
			"Holy See (Vatican City State)",
			"Honduras",
			"Hong Kong",
			"Hungary",
			"Iceland",
			"India",
			"Indonesia",
			"Iran, Islamic Republic of",
			"Iraq",
			"Ireland",
			"Isle of Man",
			"Israel",
			"Italy",
			"Jamaica",
			"Japan",
			"Jersey",
			"Jordan",
			"Kazakhstan",
			"Kenya",
			"Kiribati",
			"Korea, Democratic People's Republic of",
			"Korea, Republic of",
			"Kosovo",
			"Kuwait",
			"Kyrgyzstan",
			"Lao People's Democratic Republic",
			"Latvia",
			"Lebanon",
			"Lesotho",
			"Liberia",
			"Libyan Arab Jamahiriya",
			"Liechtenstein",
			"Lithuania",
			"Luxembourg",
			"Macao",
			"Macedonia, the Former Yugoslav Republic of",
			"Madagascar",
			"Malawi",
			"Malaysia",
			"Maldives",
			"Mali",
			"Malta",
			"Marshall Islands",
			"Martinique",
			"Mauritania",
			"Mauritius",
			"Mayotte",
			"Mexico",
			"Micronesia, Federated States of",
			"Moldova, Republic of",
			"Monaco",
			"Mongolia",
			"Montenegro",
			"Montserrat",
			"Morocco",
			"Mozambique",
			"Myanmar",
			"Namibia",
			"Nauru",
			"Nepal",
			"Netherlands",
			"Netherlands Antilles",
			"New Caledonia",
			"New Zealand",
			"Nicaragua",
			"Niger",
			"Nigeria",
			"Niue",
			"Norfolk Island",
			"Northern Mariana Islands",
			"Norway",
			"Oman",
			"Pakistan",
			"Palau",
			"Palestinian Territory, Occupied",
			"Panama",
			"Papua New Guinea",
			"Paraguay",
			"Peru",
			"Philippines",
			"Pitcairn",
			"Poland",
			"Portugal",
			"Puerto Rico",
			"Qatar",
			"Reunion",
			"Romania",
			"Russian Federation",
			"Rwanda",
			"Saint Barthelemy",
			"Saint Helena",
			"Saint Kitts and Nevis",
			"Saint Lucia",
			"Saint Martin",
			"Saint Pierre and Miquelon",
			"Saint Vincent and the Grenadines",
			"Samoa",
			"San Marino",
			"Sao Tome and Principe",
			"Saudi Arabia",
			"Senegal",
			"Serbia",
			"Serbia and Montenegro",
			"Seychelles",
			"Sierra Leone",
			"Singapore",
			"Sint Maarten",
			"Slovakia",
			"Slovenia",
			"Solomon Islands",
			"Somalia",
			"South Africa",
			"South Georgia and the South Sandwich Islands",
			"South Sudan",
			"Spain",
			"Sri Lanka",
			"Sudan",
			"Suriname",
			"Svalbard and Jan Mayen",
			"Swaziland",
			"Sweden",
			"Switzerland",
			"Syrian Arab Republic",
			"Taiwan, Province of China",
			"Tajikistan",
			"Tanzania, United Republic of",
			"Thailand",
			"Timor-Leste",
			"Togo",
			"Tokelau",
			"Tonga",
			"Trinidad and Tobago",
			"Tunisia",
			"Turkey",
			"Turkmenistan",
			"Turks and Caicos Islands",
			"Tuvalu",
			"Uganda",
			"Ukraine",
			"United Arab Emirates",
			"United Kingdom",
			"United States",
			"United States Minor Outlying Islands",
			"Uruguay",
			"Uzbekistan",
			"Vanuatu",
			"Venezuela",
			"Viet Nam",
			"Virgin Islands, British",
			"Virgin Islands, U.s.",
			"Wallis and Futuna",
			"Western Sahara",
			"Yemen",
			"Zambia",
			"Zimbabwe",
		);

		return $country_list;

	}

	/**
	 * Displays the League add or edit page
	 *
	 * @param integer $post_id
	 * @param boolean $display
	 *
	 * @return string
	 */
	public static function add_or_edit_page( $post_id = 0, $display = false ) {
		$league_name             = '';
		$league_country          = '';
		$former_league_photo     = '';
		$former_league_photo_url = '';

		// Gets the value referred by the $post_id and extracts its data for editing
		if ( $post_id && is_numeric( $post_id ) ) {
			$league_data = Leagues_Model::get_league( $post_id );

			if ( $league_data ) {
				$post_id             = $league_data['ID'];
				$league_name         = Helper::get_var( $league_data['data']['league-name'] );
				$league_country      = Helper::get_var( $league_data['data']['league-country'] );
				$former_league_photo = Helper::get_var( $league_data['data']['league-photo']['id'] );

				if ( $former_league_photo ) {
					$former_league_photo_url = wp_get_attachment_image_url(
						$former_league_photo,
						array( 300, 300 ),
						false
					);

					if (!$former_league_photo_url) {
						$former_league_photo = '';
					}
				}
			}
			else {
				$post_id = 0;
			}
		}

		$page_title = $post_id ? 'Edit League' : 'Add New League';

		$html = Template::parse( 'admin/leagues/league_add.php', array(
			'id'                      => $post_id,
			'league_name'             => $league_name,
			'league_country'          => $league_country,
			'country_list'            => self::get_country_list(),
			'former_league_photo'     => $former_league_photo,
			'former_league_photo_url' => $former_league_photo_url,
			'page_title'              => $page_title,
		) );

		if ($display) {
			print $html;
		}

		return $html;
	}

	public static function admin_management_page() {
		$data = array(
			'add_form' => self::add_or_edit_page(  )

		);
		Template::pparse( 'admin/leagues/league_page_management.php', $data );
	}


}