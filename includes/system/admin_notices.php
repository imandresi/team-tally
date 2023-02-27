<?php
/**
 * Created by PhpStorm.
 * User: Itanjaka Mandresi
 * Date: 27/02/2023
 * Time: 15:39
 */

namespace TEAMTALLY\System;

class Admin_Notices {

	const SESSION_KEY = 'ADMIN_NOTICE';

	/**
	 * Admin notices
	 */
	const ADMIN_NOTICE_ERROR = 1;
	const ADMIN_NOTICE_WARNING = 2;
	const ADMIN_NOTICE_SUCCESS = 3;
	const ADMIN_NOTICE_INFO = 4;


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

		$html = "<div class=\"$classes\" style=\"padding: 10px; margin: 10px 20px 10px 2px; font-size: 14px;\">\n";

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
	 * Saves the notice message into a session
	 *
	 * @param $message
	 * @param $notice_type
	 * @param $is_dismissible
	 *
	 * @return void
	 */
	public static function set_message( $message, $notice_type, $is_dismissible = false ) {

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

		$session_key = self::SESSION_KEY . ":{$notice_type}";
		$session_var = &Sessions::get( $session_key );

		if ( ! $session_var ) {
			$session_var = array();
		}

		$session_var[] = $message_data;

	}

	/**
	 * Displays all the pending admin notices in the session
	 *
	 * @param $display
	 *
	 * @return string
	 */
	public static function all_pending_notices( $display = true ) {

		$admin_notices   = &Sessions::get( "ADMIN_NOTICE" );
		$notice_statuses = array(
			self::ADMIN_NOTICE_WARNING,
			self::ADMIN_NOTICE_ERROR,
			self::ADMIN_NOTICE_INFO,
			self::ADMIN_NOTICE_SUCCESS,
		);

		$html = '';

		foreach ( $notice_statuses as $notice_type ) {
			if ( ! isset( $admin_notices[ $notice_type ] ) ) {
				continue;
			}

			if ( ! is_array( $admin_notices[ $notice_type ] ) ) {
				continue;
			}

			// Grouping the same status notices by dismissibility
			$grouped_notice = array(
			    'dismissible' => array(),
				'not_dismissible' => array()
			);

			foreach ( $admin_notices[ $notice_type ] as $notice_data ) {
				$message = Helper::get_var($notice_data['message'], '');
				$is_dismissible = Helper::get_var($notice_data['is_dismissible'], false);

				$notice_key = $is_dismissible ? 'dismissible' : 'not_dismissible';
				$grouped_notice[$notice_key][] = $message;
			}

			// Displaying the grouped notices
			$html .= self::notice($grouped_notice['dismissible'], $notice_type, true, false );
			$html .= self::notice($grouped_notice['not_dismissible'], $notice_type, false, false );

		}

		// Clear session containing the admin notices
		$admin_notices = array();

		if ($display) {
			print $html;
		}

		return $html;

	}

}