<?php
/**
 * Created by PhpStorm.
 * User: Itanjaka Mandresi
 * Date: 19/03/2023
 * Time: 14:24
 */

namespace TEAMTALLY\System;

class Hook_Recorder extends Singleton {

	private $hooks = array();
	private $isRecording = false;

	/**
	 * Launch recording
	 *
	 * @return void
	 */
	public static function record() {
		self::load()->isRecording = true;
	}

	/**
	 * Stops recording
	 *
	 * @return void
	 */
	public static function stop() {
		self::load()->isRecording = false;
	}

	/**
	 * Adds a mark to the hook
	 *
	 * @param $mark
	 *
	 * @return void
	 */
	public static function insertMark( $mark ) {
		$instance          = self::load();
		$instance->hooks[] = '***** MARK ***** ' . $mark;
	}

	/**
	 * Saves the recorded hooks into disk
	 *
	 * @return void
	 */
	public static function writeToDisk() {
		$instance = self::load();
		Helper::debug( $instance->hooks, 'HOOKS', true );
	}

	/**
	 * Stores all the occuring hooks in memory
	 *
	 * Fired by 'all' hooks
	 *
	 * @param $tag
	 *
	 * @return void
	 */
	public function watchRecording( $tag ) {
		if ( ! $this->isRecording ) {
			return;
		}

		if ( did_action( $tag ) ) {
			$this->hooks[] = $tag;
		}
		if ( 'shutdown' === $tag ) {
			self::writeToDisk();
		}
	}

	/**
	 * Automatic initialization routine
	 */
	protected function init() {
		$this->hooks       = array();
		$this->isRecording = false;

		add_action( 'all', array( $this, 'watchRecording' ), 10, 1 );

	}

	/**
	 * Loading the class
	 */
	public static function load() {
		$instance = self::get_instance();

		return $instance;

	}


}