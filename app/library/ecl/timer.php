<?php
/**
 * Timer class.
 *
 * @package Ecl
 * @version 6.1.0
 */
class Ecl_Timer {

   // Private Properties

   private $_start_time = 0;
   private $_paused_time = 0;



	/**
	 * Constructor
	 */
	public function __construct() {
	}// /->__construct()



/* --------------------------------------------------------------------------------
 * Public Methods
 */



	/**
	 * Fetch the time elapsed since the timer was started.
	 *
	 * If the timer is currently paused, the current paused-time is returned.
	 *
	 * @return  float  The current timer duration (seconds).
	 */
	public function fetch($decimalPlaces = 4) {
		if ($this->_paused_time>0) {
			return round(($this->_gettime() - $this->_paused_time), $decimalPlaces);
		} else {
			return round(($this->_gettime() - $this->_start_time), $decimalPlaces);
		}
	}// /method



	/**
	 * Pause the timer.
	 *
	 * Call ->unpause() to resume the timer from the paused value.
	 *
	 * @return  boolean  The operation was successful.
	 */
	function pause() {
		if ($this->_paused_time==0) {
			$this->_paused_time = $this->_gettime();
		}
		return true;
	}// /method



	/**
	 * Start the timer.
	 *
	 * Resets the timer to zero, begins timing.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function start() {
		$this->_paused_time = 0;
		$this->_start_time = $this->_gettime();
		return true;
	}// /method



	/**
	 * Unpause and resume the timer.
	 *
	 * Resets the timer to the time of the last pause, and resumes timing.
	 * If ->pause() is not called first, this method will do nothing.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function unpause() {
		if ($this->_paused_time>0) {
			$this->_start_time += ($this->_gettime() - $this->_paused_time);
			$this->_paused_time = 0;
		}
	}// /method



/* --------------------------------------------------------------------------------
 * Private Methods
 */



	/**
	 * Get the current time in micro-seconds
	 */
	private function _gettime() {
		list($usec, $sec) = explode(" ", microtime());
		return ((float)$usec + (float)$sec);
	}// /method



}// /class

?>