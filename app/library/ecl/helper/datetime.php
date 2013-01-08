<?php
/**
 * Datetime helper class
 *
 * @package  Ecl
 * @static
 * @version  1.0.0
 */
class Ecl_Helper_Datetime {

	// Constants
	const FORMAT_MYSQL    = 'Y-m-d H:i:s';
	const FORMAT_ISO8601  = 'Y-m-d\TH:i:s';
	const FORMAT_RFC3339  = 'Y-m-d\TH:i:sO';   // Subset of ISO8601 (Used by Atom)
	const FORMAT_RFC822   = 'D, j M Y H:i:s O';   // Used by RSS

	const SECONDS_PER_HOUR  = 3600;   // 60*60
	const SECONDS_PER_DAY   = 86400;   // 60*60*24
	const SECONDS_PER_WEEK  = 604800;   // 60*60*24*7



	/**
	 * Constructor
	 */
	private function __construct() {
	}// /method



/* --------------------------------------------------------------------------------
 * Public Methods
 */



	/**
	 * Get the academic year for a given date
	 *
	 * @param  integer  $date  (optional) datetime to check. (default: current date/time).
	 * @param  integer  $start_month  (optional) month-component of the date an academic year begins (default: 9).
	 * @param  integer  $start_day  (optional) day-component of the date an academic year begins (default: 1).
	 *
	 * @return  integer  Academic year (format: YYYY)
	 */
	public static function getAcademicYear($date = null, $start_month = 9, $start_day = 1) {
		if (is_null($date)) { $date = time(); }
		$year = (int) date('Y',$date);
		$academic_start_date = mktime(0,0,0, $start_month, $start_day, $year );	// the start date for academic year in the given year

		return (int) ( ($date>=$academic_start_date) ? $year : $year-1 );
	}// /method



	/**
	 * Get the difference between two datetimes as hours, minutes and seconds.
	 *
	 * If the time difference is less than 1 hour then only minutes and seconds will be shown.
	 * If the time difference is negative (start date is after end date) then the result will be prefixed with '-'.
	 * Hours are not prefixed with 0, but minutes and seconds are.
	 *
	 * @param  datetime  $start_date  The starting date
	 * @param  datetime  $end_date  The end date
	 *
	 * @return  string  String representation of the time difference (format: h:mm:ss)
	 */
	public static function getTimeDifference($start_date, $end_date) {

		if ($end_date>$start_date) {	// If the start is after the end, the result will be a minus
			$prefix = '';
			$remainder = $end_date - $start_date;
		} else {
			$prefix = '-';
			$remainder = $start_date - $end_date;
		}

		// Calculate hours difference
		$hours = floor($remainder / self::SECONDS_PER_HOUR);
		$remainder = $remainder - ($hours * self::SECONDS_PER_HOUR);

		// Calculate minutes difference
		$minutes = floor($remainder / self::SECONDS_PER_MINUTE);
		$remainder = $remainder - ($minutes * self::SECONDS_PER_MINUTE);

		// Calculate seconds difference
		$seconds = $remainder;

		// Put leading zeros on minutes and seconds (if required)
		if ($minutes<=9) { $minutes = '0' . $minutes; }
		if ($seconds<=9) { $seconds = '0' . $seconds; }

		// Return difference
		if ($hours>0) {
			return "$hours:$minutes:$seconds";
		} else {
			return "$minutes:$seconds";
		}
	}// /method



	/**
	 * Get the date the week started, based on the given date.
	 *
	 * Weeks are assumed to begin on Monday.
	 * e.g. the date was Thurs 10-Jan-2008 13:00
	 *      week start = Mon 7-Jan-2008 00:00:00
	 *
	 * @param  datetime  $date  (optional) The date to find the week for.  (default: finds this week's start)
	 *
	 * @return  datetime  The date on which the week started.
	 */
	public static function getWeekStartDate($date) {
		// Use a time of 12:00 to avoid any BST/GMT offset problems when subtracting SECONDS_PER_DAY and
		// accidentally knocking our date to 'yesterday' @ 23:00 when using something like 2010-09-01 00:00:00
		if (is_null($date)) {
			$date = mktime(12,0,0);
		} else {
			$date = mktime(12,0,0, date('m', $date), date('d', $day), date('Y', $date) );
		}

		// Get the date's day of the week
		$day_of_week = (int) date('w', $date);

		// Find the date of start of that week (Weeks start with Monday)
		if ($day_of_week==0) {
			// Sunday is 0 in PHP
			$date_start = $date - (6 * self::SECONDS_PER_DAY);
		} else {
			$date_start = $date - (($day_of_week-1) * self::SECONDS_PER_DAY);
		}
		return mktime(0, 0, 0, date('m', $date_start), date('d', $date_start), date('Y', $date_start) );
	}// /method



	/**
	 * Is the given year a leap year?
	 *
	 * @param  integer  $year  (optional) The year to check (default: current year).
	 *
	 * @return  boolean  The year is a lear year.
	 */
	public static function isLeapYear($year = null) {
		if (is_null($year)) { $year = (int) date('Y'); }

		if ($year % 400 == 0) { return true; }
		if ($year % 100 == 0) { return false; }
		if ($year % 4 == 0) { return true; }
		return false;
	}// /method



}// /class
?>