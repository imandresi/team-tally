<?php
/**
 * Created by PhpStorm.
 * User: Itanjaka Mandresi
 * Date: 27/02/2023
 * Time: 15:39
 */

namespace TEAMTALLY\System;

class Admin_Notices extends Singleton {

	const STORAGE_KEY = 'ADMIN_NOTICE';

	private $LOCAL_MESSAGES = array();

	/**
	 * Admin notices
	 */
	const ADMIN_NOTICE_ERROR = 1;
	const ADMIN_NOTICE_WARNING = 2;
	const ADMIN_NOTICE_SUCCESS = 3;
	const ADMIN_NOTICE_INFO = 4;

	/**
	 * Clearing range of storage variable
	 * Used by self::clear()
	 */
	const STORAGE_RANGE_ALL = 1;
	const STORAGE_RANGE_SESSION = 2;
	const STORAGE_RANGE_LOCAL = 3;

	/**
	 * Returns a reference to the storage variable containing the message
	 *
	 * It may be a reference to a session variable or the local message storage
	 * $key is a semi-colon separated key. (Ex. ADMIN_NOTICE:ERROR)
	 *
	 * @param string $key
	 * @param boolean $use_session
	 *
	 * @return array|mixed|NULL
	 */
	private function &get_storage_reference( $key, $use_session ) {

		$storage_key = $key ? self::STORAGE_KEY . ':' . $key : self::STORAGE_KEY;

		if ( ! $use_session ) {
			$storage_ref = &Helper::array_value_from_string(
				$this->LOCAL_MESSAGES, $storage_key, $found, true
			);
		} else {
			$storage_ref = &Sessions::get( $storage_key );
		}

		return $storage_ref;

	}

	/**
	 * Displays an admin notice
	 *
	 * $notice_type:
	 *    ADMIN_NOTICE_ERROR
	 *    ADMIN_NOTICE_WARNING
	 *    ADMIN_NOTICE_SUCCESS
	 *    ADMIN_NOTICE_INFO
	 *
	 * @param array|string $message
	 * @param $notice_type
	 * @param bool $is_dismissible
	 * @param bool $display
	 *
	 * @return string
	 */
	public static function notice( $message, $notice_type, $is_dismissible = false, $display = true ) {

		if ( empty( $message ) ) {
			return '';
		}

		$classes = "notice";

		switch ( $notice_type ) {
			case self::ADMIN_NOTICE_ERROR:
				$classes .= ' notice-error';
				break;

			case self::ADMIN_NOTICE_INFO:
				$classes .= ' notice-info';
				break;

			case self::ADMIN_NOTICE_SUCCESS:
				$classes .= ' notice-success';
				break;

			case self::ADMIN_NOTICE_WARNING:
				$classes .= ' notice-warning';
				break;
		}

		$classes .= $is_dismissible ? ' is-dismissible' : '';

		$html = "<div class=\"$classes\" style=\"padding: 10px; margin: 10px 0px 10px 2px; font-size: 14px;\">\n";

		if ( is_array( $message ) ) {
			$count       = count( $message );
			$extra_style = $count > 1 ? 'list-style-type: circle; margin-left: 20px;' : '';

			$html .= "<ul style=\"margin: 0;{$extra_style}\">";

			foreach ( $message as $msg ) {
				$html .= "<li>" . $msg . "</li>\n";
			}

			$html .= '</ul>';
		} else {
			$html .= $message;
		}

		$html .= "</div>\n";

		if ( $display ) {
			print $html;
		}

		return $html;

	}

	/**
	 * Saves the notice message
	 *
	 * The message may be stored inside a local variable or the session
	 *
	 * $msg_key    allows you to store the message inside a special key instead of
	 *             using the following array index
	 *
	 * $store_in_session    allows you to choose where to store the message.
	 *                      store_in_session is useful if there is a redirection to the page and
	 *                      the message has to be displayed in that next page. If the message has
	 *                      to be displayed immediately, set $store_in_session to false and
	 *                      a local variable will be used to store the message. Take note that
	 *                      this function has then to be called before the 'admin_notices' hook.
	 *
	 * @param string $message
	 * @param $notice_type
	 * @param bool $is_dismissible
	 * @param null $msg_key
	 * @param bool $store_in_session
	 *
	 * @return void
	 */
	public static function set_message(
		$message,
		$notice_type,
		$is_dismissible = false,
		$msg_key = null,
		$store_in_session = false
	) {

		$instance = self::get_instance();

		switch ( $notice_type ) {
			// Allowed $notice_type
			case self::ADMIN_NOTICE_ERROR :
			case self::ADMIN_NOTICE_WARNING :
			case self::ADMIN_NOTICE_SUCCESS :
			case self::ADMIN_NOTICE_INFO :
				break;

			// unknown $notice_type - forbidden
			default:
				return;
		}

		$message_data = array(
			'is_dismissible' => $is_dismissible,
			'message'        => $message,
		);

		$message_storage_var = &$instance->get_storage_reference( $notice_type, $store_in_session );

		if ( ! $message_storage_var ) {
			$message_storage_var = array();
		}

		if ( is_null( $msg_key ) ) {
			$message_storage_var[] = $message_data;
		} else {
			$message_storage_var[ $msg_key ] = $message_data;
		}

	}

	/**
	 * Clear messages
	 *
	 * @return void
	 */
	public static function clear( $range = self::STORAGE_RANGE_ALL ) {

		$instance = self::get_instance();

		$clear_session = ( $range == self::STORAGE_RANGE_ALL ) ||
		                 ( $range == self::STORAGE_RANGE_SESSION );

		$clear_local = ( $range == self::STORAGE_RANGE_ALL ) ||
		               ( $range == self::STORAGE_RANGE_LOCAL );

		if ( $clear_session ) {
			$admin_notices = &Sessions::get( self::STORAGE_KEY );
			$admin_notices = array();
		}

		if ( $clear_local ) {
			$instance->LOCAL_MESSAGES = array();
		}

	}

	/**
	 * Displays all the pending admin notices in the session
	 *
	 * @param $display
	 *
	 * @return string
	 */
	public static function all_pending_notices( $display = true ) {
		$html = '';

		$instance = self::get_instance();

		// sets the notice display for the local storage and the session storage
		$notice_storage_list = array( self::STORAGE_RANGE_LOCAL, self::STORAGE_RANGE_SESSION );

		// displays selected notice storages
		foreach ( $notice_storage_list as $notice_storage ) {

			$admin_notices = &$instance->get_storage_reference(
				'',
				$notice_storage == self::STORAGE_RANGE_SESSION
			);

			$notice_statuses = array(
				self::ADMIN_NOTICE_WARNING,
				self::ADMIN_NOTICE_ERROR,
				self::ADMIN_NOTICE_INFO,
				self::ADMIN_NOTICE_SUCCESS,
			);

			foreach ( $notice_statuses as $notice_type ) {
				if ( ! isset( $admin_notices[ $notice_type ] ) ) {
					continue;
				}

				if ( ! is_array( $admin_notices[ $notice_type ] ) ) {
					continue;
				}

				// Grouping the same status notices by dismissibility
				$grouped_notice = array(
					'dismissible'     => array(),
					'not_dismissible' => array()
				);

				foreach ( $admin_notices[ $notice_type ] as $notice_data ) {
					$message        = Helper::get_var( $notice_data['message'], '' );
					$is_dismissible = Helper::get_var( $notice_data['is_dismissible'], false );

					$notice_key                      = $is_dismissible ? 'dismissible' : 'not_dismissible';
					$grouped_notice[ $notice_key ][] = $message;
				}

				// Displaying the grouped notices
				$html .= self::notice( $grouped_notice['dismissible'], $notice_type, true, false );
				$html .= self::notice( $grouped_notice['not_dismissible'], $notice_type, false, false );

			}

			// Clear session containing the admin notices
			// for the specified storage
			self::clear( $notice_storage );

		}

		if ( $display ) {
			print $html;
		}

		return $html;

	}

	/**
	 * Initialization
	 *
	 * @return void
	 */
	protected function init() {
		add_action( 'all_admin_notices', function () {
			Admin_Notices::all_pending_notices();
		} );
	}

	/**
	 * Loads the class
	 */
	public static function load() {
		self::get_instance();
	}

}